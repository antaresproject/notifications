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

namespace Antares\Notifications\Http\Middlewares;

use Antares\Notifications\Listener\NotificationsListener;
use Illuminate\Http\Request;
use Closure;

class ListenerMiddleware
{

    /**
     * @var NotificationsListener
     */
    protected $listener;

    /**
     * ListenerMiddleware constructor.
     * @param NotificationsListener $listener
     */
    public function __construct(NotificationsListener $listener) {
        $this->listener = $listener;
    }

    /**
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next) {
        $this->listener->listen();

        return $next($request);
    }

}