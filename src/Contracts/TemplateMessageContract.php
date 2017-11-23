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
     * Returns type of notification.
     *
     * @return string
     */
    public function getType() : string;

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
