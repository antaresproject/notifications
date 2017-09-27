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
 * @version    0.9.0
 * @author     Antares Team
 * @license    BSD License (3-clause)
 * @copyright  (c) 2017, Antares
 * @link       http://antaresproject.io
 */

namespace Antares\Notifications\Adapter;

use Antares\Notifications\Parsers\ContentParser;

class VariablesAdapter
{

    /**
     * @var ContentParser
     */
    protected $contentParser;

    /**
     * @var array
     */
    protected $variables = [];

    /**
     * VariablesAdapter constructor.
     * @param ContentParser $contentParser
     */
    public function __construct(ContentParser $contentParser) {
        $this->contentParser = $contentParser;
    }

    /**
     * For TRUE value the evaluated variables will use default or fake values to simulate fully filled content.
     *
     * @param bool $state
     */
    public function setPreviewMode(bool $state) : void {
        $this->contentParser->setPreviewMode($state);
    }

    /**
     * @param array $variables
     * @return VariablesAdapter
     */
    public function setVariables($variables = []) : self {
        $this->variables = $variables;

        return $this;
    }

    /**
     * Gets variables filled notification content
     * 
     * @param String $content
     * @param array $variables
     * @return String
     */
    public function get(string $content, array $variables = []) : string {
        return $this->contentParser->parse($content, array_merge($this->variables, $variables));
    }

}
