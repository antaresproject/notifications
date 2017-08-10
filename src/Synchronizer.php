<?php

namespace Antares\Notifications;

use Antares\Notifications\Model\NotificationContents;
use Antares\Notifications\Model\NotificationSeverity;
use Antares\Notifications\Model\NotificationCategory;
use Antares\Notifications\Model\NotificationTypes;
use Antares\Notifications\Model\Notifications;
use Antares\Notifications\Contracts\Message;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use ReflectionClass;
use Exception;

class Synchronizer
{

    /**
     * Synchronize notification with entry in database
     * 
     * @param String $classname
     * @param Message $message
     * @return boolean
     */
    public function syncDatabase($classname, Message $message)
    {

        DB::beginTransaction();
        try {
            if (is_array($message->type)) {
                foreach ($message->type as $type) {
                    $this->save($classname, $type, $message);
                }
            } else {
                $this->save($classname, $message->type, $message);
            }
        } catch (Exception $ex) {
            DB::rollback();
            Log::error($ex);
            return false;
        }

        DB::commit();
        return true;
    }

    /**
     * Saves notification in database
     * 
     * @param String $classname
     * @param String $type
     * @param Message $message
     * @return void
     */
    protected function save($classname, $type, Message $message)
    {
        $model      = Notifications::query()->firstOrCreate([
            'classname' => $classname,
            'type_id'   => $this->type($type)->id,
        ]);
        $reflection = new ReflectionClass($classname);
        $checksum   = md5_file($reflection->getFileName());

        if (!is_null($model->checksum)) {

            if ($model->checksum === $checksum) {
                return;
            }
        }
        $model->fill([
            'severity_id' => $this->severity($message->severity)->id,
            'category_id' => $this->category($message->category)->id,
            'checksum'    => $checksum
        ]);
        $model->save();
        $langs = langs();
        foreach ($langs as $lang) {
            $content = ($message->type === 'sms') ? $message->content : file_get_contents(view($message->view)->getPath());
            $this->saveNotificationContent($model->id, $lang->code, $message->rawSubject, $content);
        }
    }

    /**
     * Saves notification message
     * 
     * @param mixed $id
     * @param String $locale
     * @param String $subject
     * @param String $view
     * @return boolean
     */
    private function saveNotificationContent($id, $locale, $subject, $view)
    {
        $lang = lang($locale);

        if (is_null($lang)) {
            return false;
        }
        $content          = NotificationContents::query()->firstOrCreate([
            'notification_id' => $id,
            'lang_id'         => $lang->id
        ]);
        $content->title   = $subject;
        $content->content = $view;
        return $content->save();
    }

    /**
     * Resolves notification category
     * 
     * @param String $category
     * @return NotificationCategory
     */
    private function category($category = null)
    {
        $value = is_null($category) ? 'default' : $category;
        return NotificationCategory::where('name', $value)->firstOrFail();
    }

    /**
     * Resolves notification type identifier
     * 
     * @param String $type
     * @return NotificationTypes
     */
    private function type($type = null)
    {
        $value = is_null($type) ? 'admin' : $type;
        return NotificationTypes::where('name', $value)->firstOrFail();
    }

    /**
     * Resolves notification severity
     * 
     * @param String $severity
     * @return NotificationSeverity
     */
    private function severity($severity = null)
    {
        $value = is_null($severity) ? 'medium' : $severity;
        return NotificationSeverity::where('name', $value)->firstOrFail();
    }

}
