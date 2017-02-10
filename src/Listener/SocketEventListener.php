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


namespace Antares\Notifications\Listener;

use Ratchet\ConnectionInterface;
use BrainSocket\BrainSocketEventListener;

class SocketEventListener extends BrainSocketEventListener
{

    /**
     * get messages from repository
     * 
     * @param array $ids
     * @return array
     */
    protected function getMessages($ids = [])
    {
        $messages = app('message')->findAllNew($ids);
        $data     = [];
        foreach ($messages as $message) {
            $value = is_array($message->value) ? print_r($message->value, true) : $message->value;
            array_push($data, ['name' => $message->name, 'value' => $value, 'date' => $message->created_at->__toString(), 'id' => $message->id]);
        }
        return $data;
    }

    /**
     * on message from client
     * 
     * @param ConnectionInterface $from
     * @param mixed $msg
     */
    public function onMessage(ConnectionInterface $from, $msg)
    {
        $numRecv  = count($this->clients) - 1;
        echo sprintf('Connection %d sending message "%s" to %d other connection%s' . "\n", $from->resourceId, $msg, $numRecv, $numRecv == 1 ? '' : 's');
        $message  = json_decode($msg, true);
        $response = $this->getMessages(array_get($message, 'client.data.message', []));
        array_set($message, 'client.data.response', $response);
        foreach ($this->clients as $client) {
            $client->send($this->response->make(json_encode($message)));
        }
    }

}
