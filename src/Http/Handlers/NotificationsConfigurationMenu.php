<?php

/**
 * Part of the Antares Project package.
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
 * @copyright  (c) 2017, Antares Project
 * @link       http://antaresproject.io
 */

namespace Antares\Notifications\Http\Handlers;

use Antares\Foundation\Support\MenuHandler;
use Antares\Contracts\Authorization\Authorization;

class NotificationsConfigurationMenu extends MenuHandler
{

    /**
     * Menu configuration.
     *
     * @var array
     */
    protected $menu = [
        'id'       => 'notifications',
        'position' => '*',
        'link'     => 'antares::notifications/logs/config',
        'icon'     => 'zmdi-notifications',
    ];

    /**
     * Gets title attribute
     * 
     * @return String
     */
    public function getTitleAttribute(): String
    {
        return trans('antares/notifications::logs.notifications_config');
    }

    /**
     * Check authorization to display the menu.
     *
     * @param  \Antares\Contracts\Authorization\Authorization  $acl
     *
     * @return boolean
     */
    public function authorize(Authorization $acl): bool
    {
        return can('antares/notifications.notifications-edit');
    }

}
