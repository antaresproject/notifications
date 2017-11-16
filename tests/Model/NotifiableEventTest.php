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

namespace Antares\Templates\Model\TestCase;

use Antares\Notifications\Model\NotifiableEvent;
use Antares\Notifications\Model\Recipient;
use Antares\Testing\TestCase;
use InvalidArgumentException;
use Mockery as m;

class NotifiableEventTest extends TestCase
{

   public function testBasic() {
       $className = MockEventStub::class;

       $notifiableEvent = new NotifiableEvent($className, 'tests');

       $this->assertEquals($className, $notifiableEvent->getEventClass());
       $this->assertEquals('Mock Event Stub', $notifiableEvent->getLabel());
       $this->assertEquals('tests', $notifiableEvent->getCategoryName());
   }

    public function testWithLabel() {
        $className = MockEventStub::class;

        $notifiableEvent = new NotifiableEvent($className, 'tests', 'Custom label');

        $this->assertEquals($className, $notifiableEvent->getEventClass());
        $this->assertEquals('Custom label', $notifiableEvent->getLabel());
        $this->assertEquals('tests', $notifiableEvent->getCategoryName());
    }

    public function testInvalidEventClassName() {
        $this->expectException(InvalidArgumentException::class);

        new NotifiableEvent('invalid', 'tests');
    }

    public function testNullHandler() {
        $className = MockEventStub::class;

        $notifiableEvent = new NotifiableEvent($className, 'tests');

        $this->assertNull($notifiableEvent->getHandler());
    }

    public function testClosureHandler() {
        $className = MockEventStub::class;

        $handler = m::mock('stdClass');

        $notifiableEvent = new NotifiableEvent($className, 'tests');
        $notifiableEvent->setHandler(function() use($handler) {
            return $handler;
        });

        $this->assertInstanceOf(\Closure::class, $notifiableEvent->getHandler());
        $this->assertEquals($handler, value($notifiableEvent->getHandler()));
    }

    public function testStringHandler() {
        $className = MockEventStub::class;

        $handler = m::mock('stdClass');
        $handlerName = get_class($handler);

        $notifiableEvent = new NotifiableEvent($className, 'tests');
        $notifiableEvent->setHandler($handlerName);

        $this->assertEquals($handlerName, $notifiableEvent->getHandler());
        $this->assertEquals($handlerName, value($notifiableEvent->getHandler()));
    }

    public function testRecipients() {
        $className = MockEventStub::class;
        $notifiableEvent = new NotifiableEvent($className, 'tests', 'Custom label');

        $this->assertNull($notifiableEvent->getRecipientByArea('aaa'));

        $client = new Recipient('client', function() {});
        $admin = new Recipient('admin', function() {});

        $notifiableEvent->addRecipient($client);
        $notifiableEvent->addRecipient($admin);

        $this->assertEquals($client, $notifiableEvent->getRecipientByArea('client'));
        $this->assertEquals($admin, $notifiableEvent->getRecipientByArea('admin'));

        $this->assertTrue( is_array($notifiableEvent->toArray()));
    }

}

class MockArgumentStub {

}

class MockEventStub {

    protected $stub;

    public function __construct(MockArgumentStub $stub) {
        $this->stub = $stub;
    }
}