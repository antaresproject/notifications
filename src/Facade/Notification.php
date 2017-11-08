<?php

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
