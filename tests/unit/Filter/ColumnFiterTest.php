<?php

namespace Phormium\Tests\Unit\Filter;

use Phormium\Filter\ColumnFilter;
use Phormium\Filter\Filter;

/**
 * @group unit
 * @group filter
 */
class ColumnFilterTest extends \PHPUnit_Framework_TestCase
{
    public function testFactory()
    {
        $filter = Filter::col('test', '=', 1);

        $this->assertInstanceOf(ColumnFilter::class, $filter);
        $this->assertSame('=', $filter->operation());
        $this->assertSame('test', $filter->column());
        $this->assertSame(1, $filter->value());
    }

    public function testFilterFromArray()
    {
        $actual = ColumnFilter::fromArray(['id', '=', 123]);

        $this->assertInstanceOf(ColumnFilter::class, $actual);
        $this->assertSame('id', $actual->column());
        $this->assertSame('=', $actual->operation());
        $this->assertSame(123, $actual->value());

        $actual = ColumnFilter::fromArray(['email', 'is null']);

        $this->assertInstanceOf(ColumnFilter::class, $actual);
        $this->assertSame('email', $actual->column());
        $this->assertSame('IS NULL', $actual->operation());
        $this->assertNull($actual->value());
    }

    /**
     * @expectedException Phormium\Exception\InvalidQueryException
     * @expectedExceptionMessage Invalid filter sepecification
     */
    public function testFilterFromArrayExceptionTooMany()
    {
        ColumnFilter::fromArray([1, 2, 3, 4, 5]);
    }

    /**
     * @expectedException Phormium\Exception\InvalidQueryException
     * @expectedExceptionMessage Invalid filter sepecification
     */
    public function testFilterFromArrayExceptionTooFew()
    {
        ColumnFilter::fromArray([1]);
    }

    /**
     * @expectedException Phormium\Exception\InvalidQueryException
     * @expectedExceptionMessage Argument $column must be a string, integer given.
     */
    public function testInvalidColumn()
    {
        new ColumnFilter(1, "=", 1);
    }

    /**
     * @expectedException Phormium\Exception\InvalidQueryException
     * @expectedExceptionMessage Argument $operation must be a string, integer given.
     */
    public function testInvalidOperation()
    {
        new ColumnFilter("foo", 1, 1);
    }

    /**
     * @expectedException Phormium\Exception\InvalidQueryException
     * @expectedExceptionMessage Filter = requires a scalar value, array given.
     */
    public function testEqWrongParam()
    {
        new ColumnFilter('test', '=', []);
    }

    public function testGt()
    {
        $filter = new ColumnFilter('test', '>', 123);

        $this->assertSame('test', $filter->column());
        $this->assertSame('>', $filter->operation());
        $this->assertSame(123, $filter->value());
    }

    /**
     * @expectedException Phormium\Exception\InvalidQueryException
     * @expectedExceptionMessage Filter > requires a scalar value, array given.
     */
    public function testGtWrongParam()
    {
        new ColumnFilter('test', '>', []);
    }

    /**
     * @expectedException Phormium\Exception\InvalidQueryException
     * @expectedExceptionMessage Filter != requires a scalar value, array given.
     */
    public function testNeqWrongParam()
    {
        new ColumnFilter('test', '!=', []);
    }

    public function testIn()
    {
        $filter = new ColumnFilter('test', 'in', [1, 2, 3]);

        $this->assertSame('test', $filter->column());
        $this->assertSame('IN', $filter->operation());
        $this->assertSame([1, 2, 3], $filter->value());

    }

    /**
     * @expectedException Phormium\Exception\InvalidQueryException
     * @expectedExceptionMessage Filter IN requires an array, integer given.
     */
    public function testInWrongParam1()
    {
        new ColumnFilter('test', 'in', 1);
    }

    /**
     * @expectedException Phormium\Exception\InvalidQueryException
     * @expectedExceptionMessage Filter IN requires an array, string given.
     */
    public function testInWrongParam2()
    {
        new ColumnFilter('test', 'in', "1");
    }
    /**
     * @expectedException Phormium\Exception\InvalidQueryException
     * @expectedExceptionMessage Filter IN requires an array, NULL given.
     */
    public function testInWrongParam3()
    {
        new ColumnFilter('test', 'in', null);
    }

    /**
     * @expectedException Phormium\Exception\InvalidQueryException
     * @expectedExceptionMessage Filter IN requires a non-empty array, empty array given.
     */
    public function testInWrongParam4()
    {
        new ColumnFilter('test', 'in', []);
    }

    /**
     * @expectedException Phormium\Exception\InvalidQueryException
     * @expectedExceptionMessage Filter NOT IN requires an array, integer given.
     */
    public function testNotInWrongParam1()
    {
        new ColumnFilter('test', 'not in', 1);
    }

    /**
     * @expectedException Phormium\Exception\InvalidQueryException
     * @expectedExceptionMessage Filter NOT IN requires an array, string given.
     */
    public function testNotInWrongParam2()
    {
        new ColumnFilter('test', 'not in', "1");
    }
    /**
     * @expectedException Phormium\Exception\InvalidQueryException
     * @expectedExceptionMessage Filter NOT IN requires an array, NULL given.
     */
    public function testNotInWrongParam3()
    {
        new ColumnFilter('test', 'not in', null);
    }

    /**
     * @expectedException Phormium\Exception\InvalidQueryException
     * @expectedExceptionMessage Filter NOT IN requires a non-empty array, empty array given.
     */
    public function testNotInWrongParam4()
    {
        new ColumnFilter('test', 'not in', []);
    }

    public function testBetween()
    {
        $filter = new ColumnFilter('test', 'between', ['x', 'y']);

        $this->assertSame('test', $filter->column());
        $this->assertSame('BETWEEN', $filter->operation());
        $this->assertSame(['x', 'y'], $filter->value());
    }

    /**
     * @expectedException Phormium\Exception\InvalidQueryException
     * @expectedExceptionMessage Filter BETWEEN requires an array, string given.
     */
    public function testBetweenWrongParam1()
    {
        new ColumnFilter('test', 'between', 'xxx');
    }

    /**
     * @expectedException Phormium\Exception\InvalidQueryException
     * @expectedExceptionMessage Filter BETWEEN requires an array, NULL given.
     */
    public function testBetweenWrongParam2()
    {
        new ColumnFilter('test', 'between', null);
    }

    /**
     * @expectedException Phormium\Exception\InvalidQueryException
     * @expectedExceptionMessage Filter BETWEEN requires an array with 2 values, given array has 1 values.
     */
    public function testBetweenWrongParam3()
    {
        new ColumnFilter('test', 'between', [1]);
    }

    /**
     * @expectedException Phormium\Exception\InvalidQueryException
     * @expectedExceptionMessage Unknown filter operation [XXX]
     */
    public function testUnknownOp()
    {
        new ColumnFilter('test', 'xxx');
    }
}
