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

namespace Antares\Templates\Model\TestCase;

use Antares\Notifications\Model\Recipient;
use Antares\Testing\TestCase;
use Mockery as m;

class RecipientTest extends TestCase
{

    public function testAreaData() {
        $resolved = function() {};
        $recipient = new Recipient('client', $resolved);

        $this->assertEquals('client', $recipient->getArea());
        $this->assertEquals('Client', $recipient->getLabel());

        $this->assertEquals([
            'area'  => 'client',
            'label' => 'Client',
        ], $recipient->toArray());

        $recipient = new Recipient('client area', $resolved);

        $this->assertEquals('client area', $recipient->getArea());
        $this->assertEquals('Client area', $recipient->getLabel());

        $this->assertEquals([
            'area'  => 'client area',
            'label' => 'Client area',
        ], $recipient->toArray());
    }

    public function testResolver() {
        $event = m::mock('stdClass')
            ->shouldReceive('test')
            ->once()
            ->andReturn('test_value')
            ->getMock();

        $recipient = new Recipient('client', function($event) {
            return $event->test();
        });

        $this->assertEquals('test_value', $recipient->resolve($event));
    }

}