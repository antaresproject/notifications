<?php

namespace Antares\Notifications\Model;

class Template {

    /**
     * @var string
     */
    protected $severity = 'medium';

    /**
     * @var string[]
     */
    protected $types = [];

    /**
     * @var string
     */
    protected $subject;

    /**
     * @var string
     */
    protected $view;

    /**
     * @var string[]
     */
    protected $recipients = [];

    /**
     * Template constructor.
     * @param array $types
     * @param string $subject
     * @param string $view
     */
    public function __construct(array $types, string $subject, string $view) {
        $this->subject  = $subject;
        $this->view     = $view;
        $this->types    = array_unique($types);
    }

    /**
     * @param array $recipients
     * @return $this
     */
    public function setRecipients(array $recipients) : self {
        $this->recipients = $recipients;

        return $this;
    }

    /**
     * @return string[]
     */
    public function getRecipients() : array {
        return $this->recipients;
    }

    /**
     * @param string $severity
     * @return $this
     */
    public function setSeverity(string $severity) : self {
        $this->severity = $severity;

        return $this;
    }

    /**
     * @return string
     */
    public function getSeverity() : string {
        return $this->severity;
    }

    /**
     * @return string[]
     */
    public function getTypes() : array {
        return $this->types;
    }

    /**
     * @return string
     */
    public function getSubject() : string {
        return $this->subject;
    }

    /**
     * @return string
     */
    public function getView() : string {
        return $this->view;
    }

    /**
     * @param array $data
     * @return string
     */
    public function renderView(array $data = []) : string {
        return view()->make($this->view, $data)->render();
    }

    /**
     * @return string
     */
    public function getViewContent() : string {
        return file_get_contents(view($this->view)->getPath());
    }

}