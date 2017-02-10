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
 * @version    0.9.0
 * @author     Antares Team
 * @license    BSD License (3-clause)
 * @copyright  (c) 2017, Antares Project
 * @link       http://antaresproject.io
 */


namespace Antares\Notifications\Server;

use Antares\Notifications\Listener\SocketEventListener;
use BrainSocket\LaravelEventPublisher;
use BrainSocket\BrainSocketResponse;
use BrainSocket\BrainSocketServer;
use Ratchet\WebSocket\WsServer;
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;

class SocketServer extends BrainSocketServer
{

    public function start($port)
    {
        $this->server = IoServer::factory(new HttpServer(new WsServer(new SocketEventListener(new BrainSocketResponse(new LaravelEventPublisher())))), $port);
    }

}
