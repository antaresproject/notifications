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

namespace Antares\Notifications\Contracts;

interface TemplateMessageContract
{

    /**
     * Defines which template should be used by the given name.
     *
     * @param string $name
     * @return $this
     */
    public function template(string $name);

    /**
     * Defines data for notification subject.
     *
     * @param array $data
     * @return $this
     */
    public function subjectData(array $data);

    /**
     * Defines data for notification view.
     *
     * @param array $data
     * @return $this
     */
    public function viewData(array $data);

    /**
     * Returns template name if exists.
     *
     * @return null|string
     */
    public function getTemplate();

    /**
     * Returns subject data.
     *
     * @return array
     */
    public function getSubjectData(): array;

    /**
     * Returns view data.
     *
     * @return array
     */
    public function getViewData(): array;

}
