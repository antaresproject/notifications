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

namespace Antares\Notifications\Facade;

use Illuminate\Support\Facades\Facade;
use Antares\Notifications\ChannelManager;

class Notification extends Facade
{

    /**
     * Get the registered name of the component.
     *
     * @return String
     */
    protected static function getFacadeAccessor()
    {
        return ChannelManager::class;
    }

}
