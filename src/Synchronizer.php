<?php

namespace Antares\Notifications;

use Antares\Notifications\Collections\TemplatesCollection;
use Antares\Notifications\Model\NotificationContents;
use Antares\Notifications\Model\NotificationSeverity;
use Antares\Notifications\Model\NotificationTypes;
use Antares\Notifications\Model\Notifications;
use Antares\Notifications\Model\Template;
use Antares\Translations\Models\Languages;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use ReflectionClass;
use Exception;

class Synchronizer
{

    /**
     * @var \Illuminate\Database\Eloquent\Collection|Languages[]
     */
    protected $languages;

    /**
     * @var bool
     */
    protected $forceMode = false;

    /**
     * Synchronizer constructor.
     */
    public function __construct() {
        $this->languages = Languages::all();
    }

    /**
     * @param bool $state
     */
    public function setForceMode(bool $state) {
        $this->forceMode = $state;
    }

    /**
     * @param string $notificationClassName
     * @param TemplatesCollection $templates
     */
    public function syncTemplates(string $notificationClassName, TemplatesCollection $templates) {
        $notifiableEvent    = $templates->getNotifiableEvent();
        $category           = $templates->getEventsCategory();
        $title              = $templates->getTitle();

        foreach($templates->all() as $template) {
            $this->syncTemplate($title, $notificationClassName, $template, $notifiableEvent, $category);
        }
    }

    /**
     * @param string $title
     * @param string $notificationClassName
     * @param Template $template
     * @param string $notifiableEvent
     * @param string $category
     * @throws Exception
     */
    public function syncTemplate(string $title, string $notificationClassName, Template $template, string $notifiableEvent, string $category) {
        DB::beginTransaction();

        try {
            foreach($template->getTypes() as $type) {
                $this->save($title, $notificationClassName, $type, $template, $notifiableEvent, $category);
            }

            DB::commit();
        }
        catch(Exception $e) {
            DB::rollBack();
            Log::error($e);

            throw $e;
        }
    }

    /**
     * @param string $title
     * @param string $className
     * @param string $type
     * @param Template $template
     * @param string $notifiableEvent
     * @param string $category
     */
    protected function save(string $title, string $className, string $type, Template $template, string $notifiableEvent, string $category)
    {
        /* @var $model Notifications */
        $model      = Notifications::query()->firstOrCreate([
            'source'        => $className,
            'event'         => $notifiableEvent,
            'category'      => $category,
            'type_id'       => $this->type($type)->id,
            'severity_id'   => $this->severity($template->getSeverity())->id,
        ]);

        $reflection = new ReflectionClass($className);
        $checksum   = md5_file($reflection->getFileName());

        if( ! ($this->forceMode || $model->checksum !== $checksum) ) {
            return;
        }

        $model->fill([
            'name'          => $title,
            'recipients'    => $template->getRecipients(),
            'checksum'      => $checksum
        ]);

        $model->save();

        $title          = $template->getSubject();
        $viewContent    = $template->getViewContent();

        foreach ($this->languages as $lang) {
            $content = NotificationContents::query()->firstOrCreate([
                'notification_id' => $model->id,
                'lang_id'         => $lang->id,
            ]);

            $content->title   = $title;
            $content->content = $viewContent;
            $content->save();
        }
    }

    /**
     * Resolves notification type identifier
     * 
     * @param String $type
     * @return NotificationTypes
     */
    private function type(string $type = null)
    {
        /* @var $model NotificationTypes */
        $model = NotificationTypes::query()->where('name', $type ?: 'notification')->firstOrFail();

        return $model;
    }

    /**
     * Resolves notification severity
     * 
     * @param String $severity
     * @return NotificationSeverity
     */
    private function severity($severity = null)
    {
        /* @var $model NotificationSeverity */
        $model = NotificationSeverity::query()->where('name', $severity ?: 'medium')->firstOrFail();

        return $model;
    }

}
