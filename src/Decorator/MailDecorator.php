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

namespace Antares\Notifications\Decorator;

use Antares\Brands\Model\BrandOptions;

class MailDecorator {

    /**
     * Decorated content with brand email header and footer.
     *
     * @param string $content
     * @return string
     */
    public static function decorate(string $content) : string {
        $brandTemplate = BrandOptions::query()->where('brand_id', brand_id())->first();
        $header        = str_replace('</head>', '<style>' . $brandTemplate->styles . '</style></head>', $brandTemplate->header);

        return (string) sprintf('%s %s %s', $header, $content, $brandTemplate->footer);
    }

}
