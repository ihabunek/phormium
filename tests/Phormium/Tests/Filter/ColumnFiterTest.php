<?php

namespace Phormium\Tests;


use Phormium\Filter\ColumnFilter;
use Phormium\Filter\Filter;

/**
 * @group filter
 */
class ColumnFilterTest extends \PHPUnit_Framework_TestCase
{
    function testFactory()
    {
        $filter = Filter::col('test', '=', 1);
        $actual = $filter->render();
        $expected = array("test = ?", array(1));
        $this->assertSame($expected, $actual);
    }

    public function testEq()
    {
        $filter = new ColumnFilter('test', '=', 1);
        $actual = $filter->render();
        $expected = array("test = ?", array(1));
        $this->assertSame($expected, $actual);
    }

    public function testEqNull()
    {
        $filter = new ColumnFilter('test', '=', null);
        $actual = $filter->render();
        $expected = array("test IS NULL", array());
        $this->assertSame($expected, $actual);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Filter = requires a scalar value, array given.
     */
    public function testEqWrongParam()
    {
        $filter = new ColumnFilter('test', '=', array());
        $filter->render();
    }

    public function testNeq1()
    {
        $filter = new ColumnFilter('test', '!=', 1);
        $actual = $filter->render();
        $expected = array("test != ?", array(1));
        $this->assertSame($expected, $actual);
    }

    public function testNeq2()
    {
        $filter = new ColumnFilter('test', '<>', 1);
        $actual = $filter->render();
        $expected = array("test <> ?", array(1));
        $this->assertSame($expected, $actual);
    }

    public function testNeqNull1()
    {
        $filter = new ColumnFilter('test', '<>', null);
        $actual = $filter->render();
        $expected = array("test IS NOT NULL", array());
        $this->assertSame($expected, $actual);
    }

    public function testNeqNull2()
    {
        $filter = new ColumnFilter('test', '!=', null);
        $actual = $filter->render();
        $expected = array("test IS NOT NULL", array());
        $this->assertSame($expected, $actual);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Filter != requires a scalar value, array given.
     */
    public function testNeqWrongParam()
    {
        $filter = new ColumnFilter('test', '!=', array());
        $filter->render();
    }

    public function testGt()
    {
        $filter = new ColumnFilter('test', '>', 1);
        $actual = $filter->render();
        $expected = array("test > ?", array(1));
        $this->assertSame($expected, $actual);
    }

    public function testGte()
    {
        $filter = new ColumnFilter('test', '>=', 1);
        $actual = $filter->render();
        $expected = array("test >= ?", array(1));
        $this->assertSame($expected, $actual);
    }

    public function testLt()
    {
        $filter = new ColumnFilter('test', '<', 1);
        $actual = $filter->render();
        $expected = array("test < ?", array(1));
        $this->assertSame($expected, $actual);
    }

    public function testLte()
    {
        $filter = new ColumnFilter('test', '<=', 1);
        $actual = $filter->render();
        $expected = array("test <= ?", array(1));
        $this->assertSame($expected, $actual);
    }

    public function testIn()
    {
        $filter = new ColumnFilter('test', 'in', array(1, 2, 3));
        $actual = $filter->render();
        $expected = array("test IN (?, ?, ?)", array(1, 2, 3));
        $this->assertSame($expected, $actual);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Filter IN requires an array, integer given.
     */
    public function testInWrongParam1()
    {
        $filter = new ColumnFilter('test', 'in', 1);
        $filter->render();
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Filter IN requires an array, string given.
     */
    public function testInWrongParam2()
    {
        $filter = new ColumnFilter('test', 'in', "1");
        $filter->render();
    }
    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Filter IN requires an array, NULL given.
     */
    public function testInWrongParam3()
    {
        $filter = new ColumnFilter('test', 'in', null);
        $filter->render();
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Filter IN requires a non-empty array, empty array given.
     */
    public function testInWrongParam4()
    {
        $filter = new ColumnFilter('test', 'in', array());
        $filter->render();
    }

    public function testNotIn()
    {
        $filter = new ColumnFilter('test', 'not in', array(1, 2, 3));
        $actual = $filter->render();
        $expected = array("test NOT IN (?, ?, ?)", array(1, 2, 3));
        $this->assertSame($expected, $actual);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Filter NOT IN requires an array, integer given.
     */
    public function testNotInWrongParam1()
    {
        $filter = new ColumnFilter('test', 'not in', 1);
        $filter->render();
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Filter NOT IN requires an array, string given.
     */
    public function testNotInWrongParam2()
    {
        $filter = new ColumnFilter('test', 'not in', "1");
        $filter->render();
    }
    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Filter NOT IN requires an array, NULL given.
     */
    public function testNotInWrongParam3()
    {
        $filter = new ColumnFilter('test', 'not in', null);
        $filter->render();
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Filter NOT IN requires a non-empty array, empty array given.
     */
    public function testNotInWrongParam4()
    {
        $filter = new ColumnFilter('test', 'not in', array());
        $filter->render();
    }

    public function testIsNull()
    {
        $filter = new ColumnFilter('test', 'is null');
        $actual = $filter->render();
        $expected = array("test IS NULL", array());
        $this->assertSame($expected, $actual);
    }

    public function testNotNull()
    {
        $filter = new ColumnFilter('test', 'not null');
        $actual = $filter->render();
        $expected = array("test IS NOT NULL", array());
        $this->assertSame($expected, $actual);
    }

    public function testNotNull2()
    {
        $filter = new ColumnFilter('test', 'is not null');
        $actual = $filter->render();
        $expected = array("test IS NOT NULL", array());
        $this->assertSame($expected, $actual);
    }

    public function testLike()
    {
        $filter = new ColumnFilter('test', 'like', '%foo%');
        $actual = $filter->render();
        $expected = array("test LIKE ?", array('%foo%'));
        $this->assertSame($expected, $actual);
    }

    public function testILike()
    {
        $filter = new ColumnFilter('test', 'ilike', '%foo%');
        $actual = $filter->render();
        $expected = array("lower(test) LIKE lower(?)", array('%foo%'));
        $this->assertSame($expected, $actual);
    }

    public function testNotLike()
    {
        $filter = new ColumnFilter('test', 'not like', '%bar%');
        $actual = $filter->render();
        $expected = array("test NOT LIKE ?", array('%bar%'));
        $this->assertSame($expected, $actual);
    }

    public function testBetween()
    {
        $filter = new ColumnFilter('test', 'between', array(10, 20));
        $actual = $filter->render();
        $expected = array("test BETWEEN ? AND ?", array(10, 20));
        $this->assertSame($expected, $actual);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Filter BETWEEN requires an array, string given.
     */
    public function testBetweenWrongParam1()
    {
        $filter = new ColumnFilter('test', 'between', 'xxx');
        $filter->render();
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Filter BETWEEN requires an array, NULL given.
     */
    public function testBetweenWrongParam2()
    {
        $filter = new ColumnFilter('test', 'between', null);
        $filter->render();
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Filter BETWEEN requires an array with 2 values, given array has 1 values.
     */
    public function testBetweenWrongParam3()
    {
        $filter = new ColumnFilter('test', 'between', array(1));
        $filter->render();
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Unknown filter operation [XXX]
     */
    public function testUnknownOp()
    {
        $filter = new ColumnFilter('test', 'xxx');
        $filter->render();
    }

    public function testFilterFromArray()
    {
        $actual = ColumnFilter::fromArray(array('id', '=', 123));

        $this->assertInstanceOf('\\Phormium\\Filter\\ColumnFilter', $actual);
        $this->assertSame('id', $actual->column);
        $this->assertSame('=', $actual->operation);
        $this->assertSame(123, $actual->value);

        $actual = ColumnFilter::fromArray(array('email', 'null'));

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
        $actual = ColumnFilter::fromArray(array(1, 2, 3, 4, 5));
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Invalid filter sepecification
     */
    public function testFilterFromArrayExceptionTooFew()
    {
        $actual = ColumnFilter::fromArray(array(1));
    }
}
