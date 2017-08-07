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
 * @package    Antares Notifications
 * @version    0.9.2
 * @author     Antares Team
 * @license    BSD License (3-clause)
 * @copyright  (c) 2017, Antares
 * @link       http://antaresproject.io
 */

namespace Antares\Notifications\Messages;

use Illuminate\Notifications\Messages\MailMessage as LaravelMailMessage;

class MailMessage extends LaravelMailMessage
{

    /**
     * Message category
     *
     * @var String
     */
    public $category = 'default';

    /**
     * Message severity
     *
     * @var String 
     */
    public $severity = 'medium';

    /**
     * Message type
     *
     * @var String 
     */
    public $type = 'mail';

    /**
     * Raw subject without translation
     *
     * @var String 
     */
    public $rawSubject;

    /**
     * Subject params
     *
     * @var array
     */
    public $subjectParams = [];

    /**
     * {@inheritdoc}
     */
    public function subject($subject, array $params = [])
    {
        $this->rawSubject    = trans($subject);
        $this->subjectParams = $params;
        return parent::subject(trans($subject, $params));
    }

    /**
     * {@inheritdoc}
     */
    public function __call($name, $arguments)
    {
        if (isset($this->$name)) {
            $this->$name = $arguments[0];
            return $this;
        }
    }

}
