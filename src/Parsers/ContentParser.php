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

namespace Antares\Notifications\Parsers;

use Antares\Notifications\Contracts\RendererContract;
use Antares\Notifications\ModelVariableDefinitions;
use Antares\Notifications\Services\VariablesService;
use Antares\Notifications\Variable;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class ContentParser {

    /**
     * Variables Service instance.
     *
     * @var VariablesService
     */
    protected $variablesService;

    /**
     * Renderer instance.
     *
     * @var RendererContract
     */
    protected $renderer;

    /**
     * Determines preview mode.
     *
     * @var bool
     */
    protected $previewMode = false;

    /**
     * Pattern for variables.
     */
    const VARIABLE_PATTERN = '/\[\[(.*?)\]\]/';

    /**
     * Pattern for blocks.
     */
    const BLOCK_PATTERN = '/\{%(.*?)\%\}/';

    /**
     * Array of block instructions.
     *
     * @var array
     */
    protected static $instructions = [
        '[[if]]',
        '[[/if]]',
        '[[foreach]]',
        '[[/foreach]]',
    ];

    /**
     * ContentParser constructor.
     * @param VariablesService $variablesService
     * @param RendererContract $renderer
     */
    public function __construct(VariablesService $variablesService, RendererContract $renderer) {
        $this->variablesService = $variablesService;
        $this->renderer = $renderer;
    }

    /**
     * For TRUE value the evaluated variables will use default or fake values to simulate fully filled content.
     *
     * @param bool $state
     */
    public function setPreviewMode(bool $state) : void {
        $this->previewMode = $state;
    }

    /**
     * Sets own render function.
     *
     * @param RendererContract $renderer
     */
    public function setRender(RendererContract $renderer) : void {
        $this->renderer = $renderer;
    }

    /**
     * Returns required variables by the given content.
     *
     * @param string $content
     * @return Variable[]|array
     */
    public function getRequiredVariables(string $content) : array {
        $variables          = $this->variablesService->getNamedVariables();
        $contentVariables   = $this->getVariables($content);
        $required           = [];

        foreach($variables as $code => $variable) {
            foreach($contentVariables as $contentVariable) {
                if( starts_with($contentVariable, $code) ) {
                    $after = str_after($contentVariable, $code);

                    if($after === '' || substr($after, 0, 1) === '.') {
                        $required[$code] = $variable;
                    }
                }
            }
        }

        return $required;
    }

    /**
     * Returns variables within the given content.
     *
     * @param string $content
     * @return string[]|array
     */
    public function getVariables(string $content) : array {
        $matches = [];

        preg_match_all(self::VARIABLE_PATTERN, $content, $matches);

        return array_values(array_unique(array_map('trim', $matches[1])));
    }

    /**
     * Returns parsed content with given variables. If preview mode is set as TRUE then the parser will try to fetch fake version of them.
     * 
     * @param string $content
     * @param array $data
     * @return string
     * @throws \Exception
     */
    public function parse(string $content, array $data = []) : string {
        foreach($this->variablesService->all() as $moduleVariables) {
            foreach($moduleVariables->getModelDefinitions() as $modelDefinition) {
                $this->isSatisfiedDataRequirements($modelDefinition, $data);
            }
        }

        $content = $this->renderer->render($this->getCompiled($content), $data);

        return StringParser::parse($content, $data);
    }

    /**
     * Compiles the given content to the format which is supported by the render engine (Twig in default).
     *
     * @param string $content
     * @return string
     */
    public function getCompiled(string $content) : string {
        $variables  = $this->getRequiredVariables($content);
        $search     = [];
        $replace    = [];

        $content    = str_replace(self::$instructions, '', $content);
        $blocks     = [];
        $inline     = [];

        preg_match_all(self::BLOCK_PATTERN, $content, $blocks);
        preg_match_all(self::VARIABLE_PATTERN, $content, $inline);

        $mergedBlocks = implode(' | ', $blocks[1]);

        foreach($variables as $code => $variable) {
            $value      = $variable->getValue();
            $search[]   = '[[ ' . $code . ' ]]';

            if( Str::contains($mergedBlocks, $code) ) {
                $replace[] = ' ' . $value . ' ';
            }
            elseif($variable->isCompiled()) {
                $replace[] = $value;
            }
            elseif($value) {
                $replace[] = '{{ ' . $value . ' }}';
            }
            else {
                $replace[] = '';
            }
        }

        $content = str_replace(["&#39;"], ['"'], $content);

        return str_replace($search, $replace, $content);
    }

    /**
     * For preview mode not-filled variables will be set by fake versions.
     *
     * @param ModelVariableDefinitions $modelVariableDefinitions
     * @param array $data
     * @throws \Exception
     */
    protected function isSatisfiedDataRequirements(ModelVariableDefinitions $modelVariableDefinitions, array & $data) {
        if( ! $modelVariableDefinitions->isSatisfiedRequirements($data)) {
            $variableName = $modelVariableDefinitions->getBindParameter()->getVariableName();

            if($this->previewMode) {
                $data[$variableName] = $modelVariableDefinitions->getDefault();
            }
        }
    }

}
