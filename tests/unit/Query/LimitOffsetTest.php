<?php

use Phormium\Query\LimitOffset;

/**
 * @group query
 * @group unit
 */
class LimitOffsetTest extends \PHPUnit_Framework_TestCase
{
    public function testConstruct()
    {
        $lo = new LimitOffset(10, 20);
        $this->assertSame(10, $lo->limit());
        $this->assertSame(20, $lo->offset());

        $lo = new LimitOffset(10);
        $this->assertSame(10, $lo->limit());
        $this->assertNull($lo->offset());
    }

    /**
     * @expectedException Phormium\Exception\OrmException
     * @expectedExceptionMessage $limit must be a positive integer or null.
     */
    public function testInvalidLimit1()
    {
        new LimitOffset(-1);
    }

    /**
     * @expectedException Phormium\Exception\OrmException
     * @expectedExceptionMessage $limit must be a positive integer or null.
     */
    public function testInvalidLimit2()
    {
        new LimitOffset('foo');
    }

    /**
     * @expectedException Phormium\Exception\OrmException
     * @expectedExceptionMessage $offset must be a positive integer or null.
     */
    public function testInvalidOffset()
    {
        new LimitOffset(1, -1);
    }

    /**
     * @expectedException Phormium\Exception\OrmException
     * @expectedExceptionMessage $offset cannot be given without a $limit
     */
    public function testOffsetWithoutLimit()
    {
        new LimitOffset(null, 1);
    }
}
