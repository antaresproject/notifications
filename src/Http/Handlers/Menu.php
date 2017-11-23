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

namespace Antares\Notifications\Http\Handlers;

use Antares\Contracts\Auth\Guard;
use Antares\Foundation\Support\MenuHandler;
use function app;

class Menu extends MenuHandler
{

    /**
     * Menu configuration.
     *
     * @var array
     */
    protected $menu = [
        'id'    => 'notifications',
        'title' => 'Notifications',
        'link'  => 'antares::notifications',
        'icon'  => 'zmdi-email',
    ];

    /**
     * Get the title.
     * 
     * @return string
     */
    public function getTitleAttribute()
    {
        return trans('antares/notifications::messages.notification_templates');
    }

    /**
     * Get position.
     *
     * @return string
     */
    public function getPositionAttribute()
    {

        return $this->handler->has('settings.brands') ? '>:settings.brands' : '>:settings.general-config';
    }

    /**
     * Check whether the menu should be displayed.
     *
     * @return bool
     */
    public function authorize()
    {
        return app('antares.acl')->make('antares/notifications')->can('notifications-list');
    }

}
