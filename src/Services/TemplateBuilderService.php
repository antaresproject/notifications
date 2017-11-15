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

namespace Antares\Notifications\Services;

use Antares\Notifications\Collections\TemplatesCollection;
use Antares\Notifications\Contracts\MessageContract;
use Antares\Notifications\Contracts\NotificationEditable;
use Antares\Notifications\Contracts\TemplateMessageContract;
use Antares\Notifications\Model\NotificationContents;
use Antares\Notifications\Model\Notifications;
use Antares\Notifications\Model\Template;
use Antares\Notifications\Parsers\ContentParser;
use Antares\Notifications\Synchronizer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Notifications\Notification;

class TemplateBuilderService
{

    /**
     * Templates collection instance.
     *
     * @var TemplatesCollection
     */
    protected $templates;

    /**
     * Content parser instance.
     *
     * @var ContentParser
     */
    protected $contentParser;

    /**
     * Synchronizer instance.
     *
     * @var Synchronizer
     */
    protected $synchronizer;

    /**
     * Editable notification if passed.
     *
     * @var NotificationEditable|null
     */
    protected $notification;

    /**
     * Notification source.
     *
     * @var string|null
     */
    protected $source;

    /**
     * Category.
     *
     * @var string|null
     */
    protected $category;

    /**
     * TemplateBuilderService constructor.
     * @param ContentParser $contentParser
     * @param Synchronizer $synchronizer
     */
    public function __construct(ContentParser $contentParser, Synchronizer $synchronizer)
    {
        $this->contentParser = $contentParser;
        $this->synchronizer  = $synchronizer;
        $this->templates     = new TemplatesCollection('', '');
    }

    /**
     * Sets given notification to builder if it has valid interface.
     *
     * @param Notification $notification
     * @return TemplateBuilderService
     */
    public function setNotification(Notification $notification): self
    {
        if ($notification instanceof NotificationEditable) {
            $this->notification = $notification;
            $this->templates    = $notification::templates();
            $this->source       = get_class($notification);
        }

        return $this;
    }

    /**
     * Determines if notification is testable.
     *
     * @return bool
     */
    protected function isTestable(): bool
    {
        if (isset($this->notification)) {
            return (isset($this->notification->testable) && $this->notification->testable);
        }

        return false;
    }

    /**
     * Returns template object of notification if exists.
     *
     * @return Notifications|null
     */
    protected function getTemplateObject()
    {
        if (isset($this->notification)) {
            return isset($this->notification->template) ? $this->notification->template : null;
        }

        return null;
    }

    /**
     * Build notification template by the given message.
     *
     * @param MessageContract $message
     */
    public function build(MessageContract $message)
    {
        if ($message instanceof TemplateMessageContract && $templateName = $message->getTemplate()) {
            $template = $this->templates->getByName($templateName);

            if ($template) {
                if ($templateObject = $this->getTemplateObject()) {
                    $notification = $templateObject->lang(lang());
                } else {
                    $notification = $this->syncWithDatabase($template);
                }

                $this->passDataFromTemplate($message, $template, $notification);
            }
        }
    }

    /**
     * Makes synchronization of template to database.
     *
     * @param Template $template
     * @return NotificationContents|null
     */
    protected function syncWithDatabase(Template $template)
    {
        $storedNotification = $this->findNotification($template);

        if (!$storedNotification && $this->notification) {
            $this->synchronizer->syncTemplate($this->source, $template);

            $storedNotification = $this->findNotification($template);
        }

        return $storedNotification;
    }

    /**
     * Given message will be populated by values from template.
     *
     * @param TemplateMessageContract $message
     * @param Template $template
     * @param NotificationContents|null $notificationContent
     */
    protected function passDataFromTemplate(TemplateMessageContract $message, Template $template, NotificationContents $notificationContent = null)
    {
        $message->category = $this->templates->getEventsCategory();
        $message->severity = $template->getSeverity();

        $message->subject = $this->contentParser->parse($notificationContent ? $notificationContent->title : $template->getSubject(), $message->getSubjectData());
        $message->content = $this->contentParser->parse($notificationContent ? $notificationContent->content : $template->getViewContent(), $message->getViewData());
    }

    /**
     * Finds notification template based on the given template data.
     *
     * @param Template $template
     * @return NotificationContents|null
     */
    protected function findNotification(Template $template)
    {
        /* @var $notificationContent NotificationContents */
        $notificationContent = NotificationContents::query()
            ->where('lang_id', lang_id())
            ->whereHas('notification', function(Builder $query) use($template) {
                $query->where('source', $this->source);
                $query->where('active', 1);

                if($category = $this->templates->getEventsCategory()) {
                    $query->where('category', $category);
                }

                $query->whereHas('type', function(Builder $query) use($template) {
                    $query->whereIn('name', $template->getTypes());
                })->whereHas('severity', function(Builder $query) use($template) {
                    $query->where('name', $template->getSeverity());
                });
            })->first();

        return $notificationContent;
    }

}
