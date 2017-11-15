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

namespace Antares\Notifications\Renderers;

use Antares\Notifications\Contracts\RendererContract;
use Illuminate\Contracts\Container\Container;
use Twig_Environment;

class TwigRenderer implements RendererContract {

    /**
     * Twig instance.
     *
     * @var Twig_Environment
     */
    protected $twig;

    /**
     * TwigRender constructor.
     * @param Container $container
     */
    public function __construct(Container $container) {
        $this->twig = $container->make('twig');
    }

    /**
     * Returns rendered content with the given data.
     *
     * @param string $content
     * @param array $data
     * @return string
     */
    public function render(string $content, array $data = []): string {
        return $this->twig->createTemplate($content)->render($data);
    }

}