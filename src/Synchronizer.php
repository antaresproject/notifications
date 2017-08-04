<?php

namespace Antares\Notifications;

use Illuminate\Notifications\Events\NotificationSending as LaravelNotificationSending;
use Antares\Notifications\Model\NotificationContents;
use Antares\Notifications\Model\NotificationSeverity;
use Antares\Notifications\Model\NotificationCategory;
use Antares\Notifications\Model\NotificationTypes;
use Antares\Notifications\Model\Notifications;
use Antares\Notifications\Contracts\Message;
use Illuminate\Support\Facades\DB;
use Exception;

class Synchronizer
{

    public function syncDatabase($classname, $subject, $body)
    {
        $notification = Notifications::query()->firstOrCreate([
            'classname' => $classname,
        ]);
    }

    /**
     * Saves notification message
     * 
     * @param Message $message
     */
    public function insert(Message $message)
    {
        DB::beginTransaction();
        try {
            $type = $message->getType();
            if ($type) {
                $this->saveNotification($message, $type);
            } else {

                $areas = array_merge(array_keys(config('areas.areas')), [config('antares/foundation::handles')]);
                foreach ($areas as $area) {
                    $this->saveNotification($message, $area);
                }
            }
        } catch (Exception $ex) {
            DB::rollback();
        }
        DB::commit();
    }

    /**
     * Saves notification content
     * 
     * @param Message $message
     * @param String $type
     */
    protected function saveNotification(Message $message, $type)
    {
        $notification = Notifications::query()->firstOrCreate([
            'severity_id' => $this->severity($message->getSeverity())->id,
            'category_id' => $this->category($message->getCategory())->id,
            'type_id'     => $this->type($type)->id,
            'event'       => $message->getName(),
            'classname'   => get_class($message),
            'active'      => 1,
        ]);
        $templates    = $message->getTemplates();
        foreach ($templates as $lang => $template) {
            $this->insertNotificationContent($notification->id, $lang, key($template), view(current($template))->render());
        }
    }

    /**
     * Inserts message content
     * 
     * @param mixed $id
     * @param String $locale
     * @param String $title
     * @param String $content
     * @return boolean
     */
    private function insertNotificationContent($id, $locale, $title, $content)
    {
        $lang = lang($locale);

        if (is_null($lang)) {
            return false;
        }

        return NotificationContents::query()->firstOrCreate([
                    'notification_id' => $id,
                    'lang_id'         => $lang->id,
                    'title'           => $title,
                    'content'         => $content,
        ]);
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
