<?php

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

        return (string) preg_replace("/<body[^>]*>(.*?)<\/body>/is", '<body>' . $content . '</body>', $header . $brandTemplate->footer);
    }

}
