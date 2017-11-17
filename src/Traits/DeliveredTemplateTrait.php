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

namespace Antares\Notifications\Traits;

trait DeliveredTemplateTrait
{

    /**
     * Subject data.
     *
     * @var array
     */
    public $subjectData = [];

    /**
     * View data.
     *
     * @var array
     */
    public $viewData = [];

    /**
     * Sets subject data.
     *
     * @param array $data
     * @return $this
     */
    public function subjectData(array $data)
    {
        $this->subjectData = $data;

        return $this;
    }

    /**
     * Sets view data.
     *
     * @param array $data
     * @return $this
     */
    public function viewData(array $data)
    {
        $this->viewData = $data;

        return $this;
    }

    /**
     * Returns an array of subject data.
     *
     * @return array
     */
    public function getSubjectData(): array
    {
        return $this->subjectData;
    }

    /**
     * Returns an array of view data.
     *
     * @return array
     */
    public function getViewData(): array
    {
        return $this->viewData;
    }

}
