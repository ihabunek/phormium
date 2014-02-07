<?php

namespace Phormium\Tests;

use Phormium\Event;

/**
 * @group event
 */
class EventTest extends \PHPUnit_Framework_TestCase
{
    private $triggered;

    public function testCallback1()
    {
        $id = uniqid("", true);
        $triggered = false;

        // Inline callback
        Event::on($id, function() use (&$triggered) {
            $triggered = true;
        });

        $this->assertFalse($triggered);
        Event::emit($id);
        $this->assertTrue($triggered);
    }

    private static $callback2Triggered = false;
    public static function callback2()
    {
        self::$callback2Triggered = true;
    }

    public function testCallback2()
    {
        $id = uniqid("", true);
        $triggered = false;

        // Class::method callback
        Event::on($id, "Phormium\\Tests\\EventTest::callback2");

        $this->assertFalse(self::$callback2Triggered);
        Event::emit($id);
        $this->assertTrue(self::$callback2Triggered);
    }

    private $callback3Triggered = false;
    public function callback3()
    {
        $this->callback3Triggered = true;
    }

    public function testCallback3()
    {
        $id = uniqid("", true);
        $triggered = false;

        // [$obj, $method] callback
        Event::on($id, array($this, 'callback3'));

        $this->assertFalse($this->callback3Triggered);
        Event::emit($id);
        $this->assertTrue($this->callback3Triggered);
    }

    public function testMultiple()
    {
        $id = uniqid("", true);
        $count = 0;

        Event::on($id, function() use (&$count) {
            $count += 1;
        });

        Event::emit($id);
        Event::emit($id);
        Event::emit($id);
        Event::emit($id);

        $this->assertSame(4, $count);
    }

    public function testOnce()
    {
        $id = uniqid("", true);
        $count = 0;

        Event::once($id, function() use (&$count) {
            $count += 1;
        });

        Event::emit($id);
        Event::emit($id);
        Event::emit($id);
        Event::emit($id);

        $this->assertSame(1, $count);
    }

    public function testAddRemove()
    {
        Event::removeListeners();

        $id1 = uniqid("", true);
        $id2 = uniqid("", true);

        $listener = function() {};

        Event::on($id1, $listener);
        Event::on($id2, $listener);

        $listeners1 = Event::listeners($id1);
        $listeners2 = Event::listeners($id2);

        $this->assertSame($listeners1[0], $listeners2[0]);
        $this->assertCount(1, $listeners1);
        $this->assertCount(1, $listeners2);

        Event::removeListener($id1, $listener);

        $listeners1 = Event::listeners($id1);
        $listeners2 = Event::listeners($id2);

        $this->assertCount(0, $listeners1);
        $this->assertCount(1, $listeners2);

        Event::removeListener($id2, $listener);

        $listeners1 = Event::listeners($id1);
        $listeners2 = Event::listeners($id2);

        $this->assertCount(0, $listeners1);
        $this->assertCount(0, $listeners2);

        Event::on($id1, $listener);
        Event::on($id2, $listener);

        $listeners1 = Event::listeners($id1);
        $listeners2 = Event::listeners($id2);

        $this->assertSame($listeners1[0], $listeners2[0]);
        $this->assertCount(1, $listeners1);
        $this->assertCount(1, $listeners2);

        Event::removeListeners();

        $listeners1 = Event::listeners($id1);
        $listeners2 = Event::listeners($id2);

        $this->assertCount(0, $listeners1);
        $this->assertCount(0, $listeners2);
    }
}
