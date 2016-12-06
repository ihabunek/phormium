<?php

namespace Phormium\Tests\Query;

use Phormium\Query\ColumnOrder;


class ColumnOrderTest extends \PHPUnit_Framework_TestCase
{
    public function testConstruct()
    {
        $order = new ColumnOrder("foo", "asc");

        $this->assertSame("foo", $order->column());
        $this->assertSame("asc", $order->direction());

        $order = new ColumnOrder("bar", "desc");

        $this->assertSame("bar", $order->column());
        $this->assertSame("desc", $order->direction());
    }

    public function testFactories()
    {
        $order = ColumnOrder::asc("foo");

        $this->assertSame("foo", $order->column());
        $this->assertSame("asc", $order->direction());

        $order = ColumnOrder::desc("bar");

        $this->assertSame("bar", $order->column());
        $this->assertSame("desc", $order->direction());
    }

    /**
     * @expectedException Phormium\Exception\OrmException
     * @expectedExceptionMessage Invalid $direction [bar]. Expected one of [asc, desc]
     */
    public function testInvalidDirection()
    {
        new ColumnOrder("foo", "bar");
    }

    /**
     * @expectedException Phormium\Exception\OrmException
     * @expectedExceptionMessage Invalid $column type [array], expected string.
     */
    public function testInvalidColumn()
    {
        new ColumnOrder([], "asc");
    }
}
