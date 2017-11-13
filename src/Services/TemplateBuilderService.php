<?php

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
     * @var TemplatesCollection
     */
    protected $templates;

    /**
     * @var ContentParser
     */
    protected $contentParser;

    /**
     * @var Synchronizer
     */
    protected $synchronizer;

    /**
     * @var NotificationEditable|null
     */
    protected $notification;

    /**
     * @var string|null
     */
    protected $source;

    /**
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
        $this->templates     = new TemplatesCollection('');
    }

    /**
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
