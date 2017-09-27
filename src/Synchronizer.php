<?php

namespace Antares\Notifications;

use Antares\Notifications\Collections\TemplatesCollection;
use Antares\Notifications\Model\NotificationContents;
use Antares\Notifications\Model\NotificationSeverity;
use Antares\Notifications\Model\NotificationCategory;
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
        foreach($templates->all() as $template) {
            $this->syncTemplate($notificationClassName, $template);
        }
    }

    /**
     * @param string $notificationClassName
     * @param Template $template
     * @throws Exception
     */
    public function syncTemplate(string $notificationClassName, Template $template) {
        DB::beginTransaction();

        try {
            foreach($template->getTypes() as $type) {
                $this->save($notificationClassName, $type, $template);
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
     * @param string $className
     * @param string $type
     * @param Template $template
     */
    protected function save(string $className, string $type, Template $template)
    {
        /* @var $model Notifications */
        $model      = Notifications::query()->firstOrCreate([
            'classname' => $className,
            'type_id'   => $this->type($type)->id,
        ]);

        $reflection = new ReflectionClass($className);
        $checksum   = md5_file($reflection->getFileName());

        if( ! ($this->forceMode || $model->checksum !== $checksum) ) {
            return;
        }

        $model->fill([
            'severity_id' => $this->severity($template->getSeverity())->id,
            'category_id' => $this->category($template->getCategory())->id,
            'checksum'    => $checksum
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
     * Resolves notification category
     * 
     * @param String $category
     * @return NotificationCategory
     */
    private function category(string $category = null)
    {
        return NotificationCategory::query()->where('name', $category ?: 'default')->firstOrFail();
    }

    /**
     * Resolves notification type identifier
     * 
     * @param String $type
     * @return NotificationTypes
     */
    private function type(string $type = null)
    {
        return NotificationTypes::query()->where('name', $type ?: 'admin')->firstOrFail();
    }

    /**
     * Resolves notification severity
     * 
     * @param String $severity
     * @return NotificationSeverity
     */
    private function severity($severity = null)
    {
        return NotificationSeverity::query()->where('name', $severity ?: 'medium')->firstOrFail();
    }

}
