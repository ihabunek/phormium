<?php

use Phormium\Query\Aggregate;

/**
 * @group query
 * @group unit
 */
class AggregateTest extends \PHPUnit_Framework_TestCase
{
    public function testConstruct()
    {
        $agg = new Aggregate(Aggregate::AVERAGE, "foo");
        $this->assertSame("avg", $agg->type());
        $this->assertSame("foo", $agg->column());

        $agg = new Aggregate(Aggregate::COUNT);
        $this->assertSame("count", $agg->type());
        $this->assertSame("*", $agg->column());
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Invalid aggregate type [xxx].
     */
    public function testInvalidType()
    {
        $agg = new Aggregate('xxx', 'yyy');
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Aggregate type [avg] requires a column to be given.
     */
    public function testRequiresColumnError()
    {
        $agg = new Aggregate(Aggregate::AVERAGE);
    }
}
