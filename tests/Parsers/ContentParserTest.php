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

namespace Antares\Templates\Model\TestCase;

use Antares\Notifications\Parsers\ContentParser;
use Antares\Notifications\Renderers\TwigRenderer;
use Antares\Notifications\Services\VariablesService;
use Antares\Notifications\Variable;
use Antares\Testing\TestCase;

class ContentParserTest extends TestCase
{

    /**
     * @var VariablesService
     */
    protected $variablesService;

    /**
     * @var ContentParser
     */
    protected $contentParser;

    public function setUp() {
        parent::setUp();

        $this->variablesService = $this->app->make(VariablesService::class);

        $this->variablesService->register('unit_test')
            ->set('dump', 'Dump Value', 'dump_value')
            ->modelDefinition('model', TestModelStub::class, function() {
                return new TestModelStub('default name');
            })
            ->setAttributes([
                'name' => 'Model Name',
            ]);


        $renderer = $this->app->make(TwigRenderer::class);

        $this->contentParser = new ContentParser($this->variablesService, $renderer);
    }

    public function testGetEmptyVariables() {
        $content = 'Simple content without variables';

        $this->assertEmpty($this->contentParser->getVariables($content));
    }

    public function testGetEmptyRequiredVariables() {
        $content = 'Simple content without variables';

        $this->assertEmpty($this->contentParser->getRequiredVariables($content));
    }

    public function testGetVariables() {
        $content = 'Simple content with [[ unit_test::dump ]] variable';

        $this->assertEquals(['unit_test::dump'], $this->contentParser->getVariables($content));
    }

    public function testGetRequiredVariables() {
        $content    = 'Simple content with [[ unit_test::dump ]] variable';
        $variables  = $this->contentParser->getRequiredVariables($content);

        $this->assertEquals(['unit_test::dump'], array_keys($variables));
        $this->assertInstanceOf(Variable::class, reset($variables));
    }

    public function testSimpleCompilation() {
        $content    = 'Simple content with [[ unit_test::dump ]] variable';
        $expected   = 'Simple content with dump_value variable';

        $this->assertEquals($expected, $this->contentParser->getCompiled($content));
    }

    public function testNotExistModelCompilation() {
        $content    = 'Simple content with [[ unit_test::model.dump ]] variable';

        $this->assertEquals($content, $this->contentParser->getCompiled($content));
    }

    public function testModelCompilation() {
        $content    = 'Simple content with [[ unit_test::model.name ]] variable';
        $expected   = 'Simple content with {{ model.name }} variable';

        $this->assertEquals($expected, $this->contentParser->getCompiled($content));
    }

    public function testParseAsPreview() {
        $content    = 'Simple content with [[ unit_test::model.name ]] variable';
        $expected   = 'Simple content with default name variable';

        $this->contentParser->setPreviewMode(true);

        $this->assertEquals($expected, $this->contentParser->parse($content));
    }

    public function testParseWithException() {
        $this->expectException(\Exception::class);

        $content = 'Simple content with [[ unit_test::model.name ]] variable';

        $this->contentParser->setPreviewMode(false);

        $this->contentParser->parse($content);
    }

}

class TestModelStub {

    /**
     * @var string
     */
    public $name;

    /**
     * TestModelStub constructor.
     * @param string $name
     */
    public function __construct(string $name) {
        $this->name = $name;
    }

}