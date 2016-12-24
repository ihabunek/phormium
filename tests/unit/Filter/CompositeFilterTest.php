<?php

namespace Phormium\Tests\Unit\Filter;

use Mockery as m;
use Phormium\Filter\ColumnFilter;
use Phormium\Filter\CompositeFilter;
use Phormium\Filter\Filter;

/**
 * @group unit
 * @group filter
 */
class CompositeFilterTest extends \PHPUnit_Framework_TestCase
{
    public function testFactoryAndOr()
    {
        $subfilters = [
            m::mock(Filter::class),
            m::mock(Filter::class),
            m::mock(Filter::class),
        ];

        $filter = Filter::_and(...$subfilters);
        $this->assertInstanceOf(CompositeFilter::class, $filter);
        $this->assertSame(CompositeFilter::OP_AND, $filter->operation());
        $this->assertSame($subfilters, $filter->filters());

        $filter = Filter::_or(...$subfilters);
        $this->assertInstanceOf(CompositeFilter::class, $filter);
        $this->assertSame(CompositeFilter::OP_OR, $filter->operation());
        $this->assertSame($subfilters, $filter->filters());
    }

    public function testArrayToFilter()
    {
        $filter = new CompositeFilter(CompositeFilter::OP_AND, [
            ["foo", "=", "bar"],
            ["bla", "not null"],
        ]);

        $filters = $filter->filters();

        $this->assertCount(2, $filters);

        $this->assertInstanceOf(ColumnFilter::class, $filters[0]);
        $this->assertSame("=", $filters[0]->operation());
        $this->assertSame("foo", $filters[0]->column());
        $this->assertSame("bar", $filters[0]->value());

        $this->assertInstanceOf(ColumnFilter::class, $filters[1]);
        $this->assertSame("NOT NULL", $filters[1]->operation());
        $this->assertSame("bla", $filters[1]->column());
        $this->assertNull($filters[1]->value());
    }

    /**
     * @expectedException Phormium\Exception\InvalidQueryException
     * @expectedExceptionMessage Invalid composite filter operation [foo]. Expected one of: AND, OR
     */
    public function testInvalidOperation()
    {
        new CompositeFilter('foo');
    }

    /**
     * @expectedException Phormium\Exception\InvalidQueryException
     * @expectedExceptionMessage CompositeFilter requires an array of Filter objects as second argument, got [string].
     */
    public function testInvalidSubfilter()
    {
        new CompositeFilter(CompositeFilter::OP_AND, ["foo"]);
    }

    public function testWithAdded()
    {
        $sf1 = m::mock(Filter::class);
        $sf2 = m::mock(Filter::class);
        $sf3 = m::mock(Filter::class);

        $f1 = new CompositeFilter(CompositeFilter::OP_AND);
        $f2 = $f1->withAdded($sf1);
        $f3 = $f2->withAdded($sf2);
        $f4 = $f3->withAdded($sf3);

        $this->assertNotSame($f1, $f2);
        $this->assertNotSame($f2, $f3);
        $this->assertNotSame($f3, $f4);

        $this->assertSame([], $f1->filters());
        $this->assertSame([$sf1], $f2->filters());
        $this->assertSame([$sf1, $sf2], $f3->filters());
        $this->assertSame([$sf1, $sf2, $sf3], $f4->filters());
    }
}
