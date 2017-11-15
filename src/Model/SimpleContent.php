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

class SimpleContent {

    /**
     * Lang ISO Code.
     *
     * @var string
     */
    public $langCode;

    /**
     * Title of notification.
     *
     * @var string
     */
    public $title;

    /**
     * Content of notification.
     *
     * @var string
     */
    public $content;

    /**
     * SimpleContent constructor.
     * @param string $langCode
     * @param string $title
     * @param string $content
     */
    public function __construct(string $langCode, string $title, string $content) {
        $this->langCode = $langCode;
        $this->title    = $title;
        $this->content  = $content;
    }

}