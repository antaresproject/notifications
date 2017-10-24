<?php

namespace Antares\Notifications\Services;

use Antares\Notifications\Collections\TemplatesCollection;
use Antares\Notifications\Contracts\MessageContract;
use Antares\Notifications\Contracts\NotificationEditable;
use Antares\Notifications\Contracts\TemplateMessageContract;
use Antares\Notifications\Model\Template;
use Antares\Notifications\Parsers\ContentParser;
use Antares\Notifications\Parsers\StringParser;
use Illuminate\Notifications\Notification;

class TemplateBuilderService {

    /**
     * @var TemplatesCollection
     */
    protected $templates;

    /**
     * @var ContentParser
     */
    protected $contentParser;

    /**
     * TemplateBuilderService constructor.
     * @param Notification $notification
     */
    public function __construct(Notification $notification) {
        $this->contentParser = app()->make(ContentParser::class);

        if($notification instanceof NotificationEditable) {
            $this->templates = $notification::templates();
        }
        else {
            $this->templates = new TemplatesCollection();
        }
    }

    /**
     * @param MessageContract $message
     */
    public function build(MessageContract $message) {
        if($message instanceof TemplateMessageContract && $templateName = $message->getTemplate()) {
            $template = $this->templates->getByName($templateName);

            if($template) {
                $this->passDataFromTemplate($message, $template);
            }
        }
    }

    /**
     * @param TemplateMessageContract $message
     * @param Template $template
     */
    protected function passDataFromTemplate(TemplateMessageContract $message, Template $template) {
        $message->category  = $template->getCategory();
        $message->severity  = $template->getSeverity();
        $message->types     = $template->getTypes();

        if( property_exists($message, 'subject') ) {
            $subject = $this->contentParser->parse($template->getSubject());
            $message->subject = StringParser::parse($subject, $message->getSubjectData());
            //$message->subject = StringParser::parse($template->getSubject(), $message->getSubjectData());
        }

        if( property_exists($message, 'content') ) {
            $message->content = $template->renderView($message->getViewData());
        }

        if( property_exists($message, 'view') ) {
            $message->view = $template->getView();
        }

        if( property_exists($message, 'viewData') ) {
            $message->viewData = array_merge($message->viewData, $message->getViewData());
        }
    }

}