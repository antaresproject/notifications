<?php

namespace Antares\Notifications\Renderers;

use Antares\Notifications\Contracts\RendererContract;
use Illuminate\Contracts\Container\Container;
use Twig_Environment;

class TwigRenderer implements RendererContract {

    /**
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