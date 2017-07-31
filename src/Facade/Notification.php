<?php

namespace Antares\Notifications\Facade;

use Illuminate\Support\Facades\Facade as LaravelFacade;
use Antares\Notifications\Messenger;

class Notification extends LaravelFacade
{

    /**
     * Get the registered name of the component.
     *
     * @return String
     */
    protected static function getFacadeAccessor()
    {
        return Messenger::class;
    }

}
