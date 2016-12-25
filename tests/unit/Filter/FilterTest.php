<?php

namespace Phormium\Tests\Unit;

use Phormium\Filter\ColumnFilter;
use Phormium\Filter\CompositeFilter;
use Phormium\Filter\Filter;
use Phormium\Filter\RawFilter;

/**
 * @group filter
 */
class FilterTest extends \PHPUnit_Framework_TestCase
{
    public function testFactories()
    {
        $col = Filter::col("foo", "=", 1);
        $raw = Filter::raw("lower(a) = ?", [2]);
        $and = Filter::_and($col, $raw);
        $or = Filter::_or($raw, $col);

        $this->assertSame("foo", $col->column());
        $this->assertSame("=", $col->operation());
        $this->assertSame(1, $col->value());

        $this->assertSame("lower(a) = ?", $raw->condition());
        $this->assertSame([2], $raw->arguments());

        $this->assertSame(CompositeFilter::OP_AND, $and->operation());
        $this->assertSame([$col, $raw], $and->filters());

        $this->assertSame(CompositeFilter::OP_OR, $or->operation());
        $this->assertSame([$raw, $col], $or->filters());
    }

    public function testFactory()
    {
        $col = Filter::col("foo", "=", 1);
        $f = Filter::factory($col);
        $this->assertSame($f, $col);

        // Column filter in array, 3 args
        $f = Filter::factory(['foo', '=', 1]);
        $this->assertInstanceOf(ColumnFilter::class, $f);
        $this->assertSame('foo', $f->column());
        $this->assertSame(ColumnFilter::OP_EQUALS, $f->operation());
        $this->assertSame(1, $f->value());

        // Column filter no array, 3 args
        $f = Filter::factory('foo', '=', 1);
        $this->assertInstanceOf(ColumnFilter::class, $f);
        $this->assertSame('foo', $f->column());
        $this->assertSame(ColumnFilter::OP_EQUALS, $f->operation());
        $this->assertSame(1, $f->value());

        // Column filter in array, 2 args
        $f = Filter::factory(['foo', 'is null']);
        $this->assertInstanceOf(ColumnFilter::class, $f);
        $this->assertSame('foo', $f->column());
        $this->assertSame(ColumnFilter::OP_IS_NULL, $f->operation());
        $this->assertNull($f->value());

        // Column filter no array, 2 args
        $f = Filter::factory('foo', 'is null');
        $this->assertInstanceOf(ColumnFilter::class, $f);
        $this->assertSame('foo', $f->column());
        $this->assertSame(ColumnFilter::OP_IS_NULL, $f->operation());
        $this->assertNull($f->value());

        // Raw filter, no arguments
        $f = Filter::factory('bla(tra)');
        $this->assertInstanceOf(RawFilter::class, $f);
        $this->assertSame('bla(tra)', $f->condition());
        $this->assertSame([], $f->arguments());

        // Raw filter, with arguments
        $f = Filter::factory('bla(tra)', [1, 2, 3]);
        $this->assertInstanceOf(RawFilter::class, $f);
        $this->assertSame('bla(tra)', $f->condition());
        $this->assertSame([1, 2, 3], $f->arguments());
    }

    /**
     * @expectedException Phormium\Exception\InvalidQueryException
     * @expectedExceptionMessage Invalid filter arguments.
     */
    public function testFactoryInvalidInput()
    {
        Filter::factory(1, 2, 3, 4, 5);
    }
}
