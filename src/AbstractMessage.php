<?php

namespace Antares\Notifications;

abstract class AbstractMessage
{

    /**
     * Name of notification
     *
     * @var String
     */
    protected $name = '';

    /**
     * Category name
     *
     * @var String
     */
    protected $category = 'default';

    /**
     * Notification severity
     *
     * @var type 
     */
    protected $severity = 'high';

    /**
     * Notification type
     *
     * @var String 
     */
    protected $type;

    /**
     * Notification recipients container
     *
     * @var mixed 
     */
    protected $recipients;

    /**
     * Notification templates configuration
     *
     * @var array 
     */
    protected $templates = [];

    /**
     * Custom data messages
     *
     * @var array 
     */
    protected $data = [];

    /**
     * Category getter
     * 
     * @return String
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * Severity getter
     * 
     * @return String
     */
    public function getSeverity()
    {
        return $this->severity;
    }

    /**
     * Type getter
     * 
     * @return String
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Notification name getter
     * 
     * @return String
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Recipients setter
     * 
     * @param mixed $recipients
     * @return $this
     */
    public function setRecipients($recipients)
    {
        $this->recipients = $recipients;
        return $this;
    }

    /**
     * Templates getter
     * 
     * @return array
     */
    public function getTemplates()
    {
        return $this->templates;
    }

    /**
     * Data setter
     * 
     * @param mixed $data
     * @return $this
     */
    public function setData($data)
    {
        $this->data = $data;
        return $this;
    }

    /**
     * Data getter
     * 
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

}
