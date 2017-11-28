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

use Illuminate\Contracts\Support\Htmlable;

class HtmlWrapper implements Htmlable {

    /**
     * String content as HTML.
     *
     * @var string
     */
    protected $html;

    /**
     * HtmlWrapper constructor.
     * @param string $html
     */
    public function __construct(string $html) {
        $this->html = $html;
    }

    /**
     * Get content as a string of HTML.
     *
     * @return string
     */
    public function toHtml() {
        return $this->html;
    }

}