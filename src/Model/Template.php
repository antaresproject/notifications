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

namespace Antares\Notifications\Model;

class Template {

    /**
     * Severity name.
     *
     * @var string
     */
    protected $severity = 'medium';

    /**
     * Used types.
     *
     * @var string[]
     */
    protected $types = [];

    /**
     * Subject.
     *
     * @var string
     */
    protected $subject;

    /**
     * Path to view.
     *
     * @var string
     */
    protected $view;

    /**
     * Names of recipients.
     *
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
     * Sets recipients by names.
     *
     * @param array $recipients
     * @return $this
     */
    public function setRecipients(array $recipients) : self {
        $this->recipients = $recipients;

        return $this;
    }

    /**
     * Returns recipients names.
     *
     * @return string[]
     */
    public function getRecipients() : array {
        return $this->recipients;
    }

    /**
     * Sets severity.
     *
     * @param string $severity
     * @return $this
     */
    public function setSeverity(string $severity) : self {
        $this->severity = $severity;

        return $this;
    }

    /**
     * Returns severity.
     *
     * @return string
     */
    public function getSeverity() : string {
        return $this->severity;
    }

    /**
     * Returns types.
     *
     * @return string[]
     */
    public function getTypes() : array {
        return $this->types;
    }

    /**
     * Return subject.
     *
     * @return string
     */
    public function getSubject() : string {
        return $this->subject;
    }

    /**
     * Returns view path.
     *
     * @return string
     */
    public function getView() : string {
        return $this->view;
    }

    /**
     * Returns rendered view with given data.
     *
     * @param array $data
     * @return string
     */
    public function renderView(array $data = []) : string {
        return view()->make($this->view, $data)->render();
    }

    /**
     * Returns raw content of view.
     *
     * @return string
     */
    public function getViewContent() : string {
        return file_get_contents(view($this->view)->getPath());
    }

}