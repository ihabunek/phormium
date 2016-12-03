<?php

namespace Phormium\Tests;


use Phormium\Filter\ColumnFilter;
use Phormium\Filter\Filter;

/**
 * @group filter
 */
class ColumnFilterTest extends \PHPUnit_Framework_TestCase
{
    public function testFactory()
    {
        $filter = Filter::col('test', '=', 1);

        $this->assertInstanceOf("Phormium\Filter\ColumnFilter", $filter);
        $this->assertSame('=', $filter->operation);
        $this->assertSame('test', $filter->column);
        $this->assertSame(1, $filter->value);
    }

    public function testFilterFromArray()
    {
        $actual = ColumnFilter::fromArray(['id', '=', 123]);

        $this->assertInstanceOf('\\Phormium\\Filter\\ColumnFilter', $actual);
        $this->assertSame('id', $actual->column);
        $this->assertSame('=', $actual->operation);
        $this->assertSame(123, $actual->value);

        $actual = ColumnFilter::fromArray(['email', 'null']);

        $this->assertInstanceOf('\\Phormium\\Filter\\ColumnFilter', $actual);
        $this->assertSame('email', $actual->column);
        $this->assertSame('NULL', $actual->operation);
        $this->assertNull($actual->value);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Invalid filter sepecification
     */
    public function testFilterFromArrayExceptionTooMany()
    {
        $actual = ColumnFilter::fromArray([1, 2, 3, 4, 5]);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Invalid filter sepecification
     */
    public function testFilterFromArrayExceptionTooFew()
    {
        $actual = ColumnFilter::fromArray([1]);
    }

}
