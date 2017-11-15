<?php

/**
 * Part of the Antares package.
 *
 * NOTICE OF LICENSE
 *
 * Licensed under the 3-clause BSD License.
 *
 * This source file is subject to the 3-clause BSD License that is
 * bundled with this package in the LICENSE file.
 *
 * @package    Notifications
 * @version    0.9.2
 * @author     Antares Team
 * @license    BSD License (3-clause)
 * @copyright  (c) 2017, Antares
 * @link       http://antaresproject.io
 */

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
     * Collection of all system languages.
     *
     * @var \Illuminate\Database\Eloquent\Collection|Languages[]
     */
    protected $languages;

    /**
     * Determines if force mode is on.
     *
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
     * Sets force mode.
     *
     * @param bool $state
     */
    public function setForceMode(bool $state) {
        $this->forceMode = $state;
    }

    /**
     * Makes synchronization of given templates.
     *
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
     * Makes synchronization for given template data.
     *
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
     * Saves template in database.
     *
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
