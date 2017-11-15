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

namespace Antares\Notifications\Http\Form;

use App;
use Antares\Contracts\Html\Form\Factory;
use Antares\Html\Form\Grid as FormGrid;
use Antares\Html\Form\FormBuilder;
use Closure;
use Illuminate\Support\Str;
use InvalidArgumentException;

class VueFormBuilder {

    /**
     * Form action URL.
     *
     * @var string
     */
    protected $action;

    /**
     * Form method.
     *
     * @var string
     */
    protected $method;

    /**
     * Defined data for form.
     *
     * @var array
     */
    protected $dataProviders = [];

    /**
     * Built form.
     *
     * @var Factory|null
     */
    protected $form;

    /**
     * VueFormBuilder constructor.
     * @param string $action
     * @param string $method
     */
    public function __construct(string $action, string $method = 'POST') {
        $this->action   = $action;
        $this->method   = $method;
    }

    /**
     * Adds single value to data providers.
     *
     * @param string $variable
     * @param string $value
     * @throws InvalidArgumentException
     */
    public function addDataProvider(string $variable, string $value) {
        if($variable === '') {
            throw new InvalidArgumentException('The given variable is empty.');
        }

        $this->dataProviders[$variable] = $value;
    }

    /**
     * Sets data providers by given array.
     *
     * @param array $dataProviders
     * @throws InvalidArgumentException
     */
    public function setDataProviders(array $dataProviders) {
        foreach($dataProviders as $variable => $value) {
            $this->addDataProvider($variable, $value);
        }
    }

    /**
     * Returns built form.
     *
     * @param string $name
     * @param Closure $callback
     * @return FormBuilder
     */
    public function build(string $name, Closure $callback) : FormBuilder {
        publish('foundation', ['js/select2.js', 'js/form_errors.js', 'js/form_mixin.js']);

        $form = $this->form()->of($name, function(FormGrid $formGrid) use($name, $callback) {
            $formAttributes = [
                'id'        => $this->getIdOfName($name),
                'method'    => $this->method,
                '@keydown'  => 'errors.clear($event.target.name)',
            ];

            foreach($this->dataProviders as $variable => $value) {
                $formAttributes['data-provider-' . $variable] = $value;
            }

            $formGrid->simple($this->action, $formAttributes);
            $formGrid->name($name);

            $callback($formGrid);
        });

        if($form instanceof FormBuilder) {
            return $form;
        }
    }

    /**
     * Returns already built form.
     *
     * @return Factory
     */
    public function form() : Factory {
        if($this->form === null) {
            $this->form = App::make(Factory::class);
        }

        return $this->form;
    }

    /**
     * Returns prepared ID for given name.
     *
     * @param string $name
     * @return string
     */
    protected function getIdOfName(string $name) : string {
        $search     = ['.', ','];
        $replace    = ['-', '-'];

        return Str::slug('form-' . str_replace($search, $replace, $name));
    }

}
