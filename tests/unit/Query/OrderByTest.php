<?php

use Phormium\Query\ColumnOrder;
use Phormium\Query\OrderBy;

/**
 * @group query
 * @group unit
 */
class OrderByTest extends \PHPUnit_Framework_TestCase
{
    public function testConstruct()
    {
        $co1 = new ColumnOrder("foo", ColumnOrder::ASCENDING);
        $co2 = new ColumnOrder("foo", ColumnOrder::ASCENDING);
        $orderBy = new OrderBy([$co1, $co2]);

        $this->assertCount(2, $orderBy->orders());
        $this->assertSame($co1, $orderBy->orders()[0]);
        $this->assertSame($co2, $orderBy->orders()[1]);
    }

    public function testAdding()
    {
        $co1 = new ColumnOrder("foo", ColumnOrder::ASCENDING);
        $co2 = new ColumnOrder("foo", ColumnOrder::ASCENDING);

        $ob1 = new OrderBy([$co1]);
        $ob2 = $ob1->withAdded($co2);

        $this->assertNotSame($ob1, $ob2);

        $this->assertCount(1, $ob1->orders());
        $this->assertSame($co1, $ob1->orders()[0]);

        $this->assertCount(2, $ob2->orders());
        $this->assertSame($co1, $ob2->orders()[0]);
        $this->assertSame($co2, $ob2->orders()[1]);
    }

    /**
     * @expectedException Phormium\Exception\OrmException
     * @expectedExceptionMessage OrderBy needs at least one ColumnOrder element, empty array given.
     */
    public function testEmptyOrder()
    {
        $orderBy = new OrderBy([]);
    }

    /**
     * @expectedException Phormium\Exception\OrmException
     * @expectedExceptionMessage Expected $orders to be instances of Phormium\Query\ColumnOrder. Given [string].
     */
    public function testInvalidOrder()
    {
        $orderBy = new OrderBy(["foo"]);
    }

    // /**
    //  * @expectedException Phormium\Exception\OrmException
    //  * @expectedExceptionMessage $limit must be a positive integer or null.
    //  */
    // public function testInvalidLimit2()
    // {
    //     new LimitOffset('foo');
    // }

    // /**
    //  * @expectedException Phormium\Exception\OrmException
    //  * @expectedExceptionMessage $offset must be a positive integer or null.
    //  */
    // public function testInvalidOffset()
    // {
    //     new LimitOffset(1, -1);
    // }

    // /**
    //  * @expectedException Phormium\Exception\OrmException
    //  * @expectedExceptionMessage $offset cannot be given without a $limit
    //  */
    // public function testOffsetWithoutLimit()
    // {
    //     new LimitOffset(null, 1);
    // }
}
