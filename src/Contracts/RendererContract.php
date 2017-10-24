<?php

namespace Antares\Notifications\Contracts;

interface RendererContract {

    /**
     * Returns rendered content with the given data.
     *
     * @param string $content
     * @param array $data
     * @return string
     */
    public function render(string $content, array $data = []) : string;

}