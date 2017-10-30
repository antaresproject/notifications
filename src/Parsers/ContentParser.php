<?php

namespace Antares\Notifications\Parsers;

use Antares\Notifications\Contracts\RendererContract;
use Antares\Notifications\ModelVariableDefinitions;
use Antares\Notifications\Services\VariablesService;
use Antares\Notifications\Variable;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class ContentParser {

    /**
     * @var VariablesService
     */
    protected $variablesService;

    /**
     * @var RendererContract
     */
    protected $renderer;

    /**
     * @var bool
     */
    protected $previewMode = false;

    const VARIABLE_PATTERN = '/\[\[(.*?)\]\]/';

    const BLOCK_PATTERN = '/\{%(.*?)\%\}/';

    /**
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
        $variables = $this->variablesService->getNamedVariables();

        return (array) Arr::only($variables, $this->getVariables($content));
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

//        foreach ($inline[0] as $index => $variable) {
//            $content = str_replace($variable, '{{ ' . trim($inline[1][$index]) . ' }}', $content);
//        }

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

        $content = str_replace(["&nbsp;", "&#39;"], ['', '"'], $content);
        //$content = str_replace(['<p>', '</p>', '<br />', "&nbsp;"], '', $content);

        return str_replace($search, $replace, $content);
    }

    /**
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
//            else {
//                throw new \Exception('Model variable bind [' . $variableName . '] was not found or has invalid value.');
//            }
        }
    }

}