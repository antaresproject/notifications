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

namespace Antares\Notifications\Console;

use Symfony\Component\Console\Input\InputOption;
use Antares\Notifications\Server\SocketServer;
use Illuminate\Console\Command;

class SocketCommand extends Command
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'notifications:start';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Starts Notifications and the Ratchet WebSocket server to start running event-driven apps with Antares.';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function fire()
    {

        $port   = $this->option('port');
        $server = new SocketServer();
        $server->start($port);
        $this->info('WebSocket server started on port:' . $port);
        $server->run();
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return array(
            array('port', null, InputOption::VALUE_OPTIONAL, 'The port you want the websocket server to run on (default: 8080)', '8080'),
        );
    }

}
