<?php

namespace Phormium\Tests;

use \Phormium\Tests\Models\Person;

use \Phormium\DB;
use \Phormium\ColumnFilter;
use \Phormium\Parser;

class FilterTest extends \PHPUnit_Framework_TestCase
{
    public static function setUpBeforeClass()
    {
        DB::configure(PHORMIUM_CONFIG_FILE);
    }

    public function testEq()
    {
        $filter = new ColumnFilter('test', '=', 1);
        $actual = $filter->render();
        $expected = array("test = ?", array(1));
        self::assertSame($expected, $actual);
    }

    public function testNeq1()
    {
        $filter = new ColumnFilter('test', '!=', 1);
        $actual = $filter->render();
        $expected = array("test != ?", array(1));
        self::assertSame($expected, $actual);
    }

    public function testNeq2()
    {
        $filter = new ColumnFilter('test', '<>', 1);
        $actual = $filter->render();
        $expected = array("test <> ?", array(1));
        self::assertSame($expected, $actual);
    }

    public function testGt()
    {
        $filter = new ColumnFilter('test', '>', 1);
        $actual = $filter->render();
        $expected = array("test > ?", array(1));
        self::assertSame($expected, $actual);
    }

    public function testGte()
    {
        $filter = new ColumnFilter('test', '>=', 1);
        $actual = $filter->render();
        $expected = array("test >= ?", array(1));
        self::assertSame($expected, $actual);
    }

    public function testLt()
    {
        $filter = new ColumnFilter('test', '<', 1);
        $actual = $filter->render();
        $expected = array("test < ?", array(1));
        self::assertSame($expected, $actual);
    }

    public function testLte()
    {
        $filter = new ColumnFilter('test', '<=', 1);
        $actual = $filter->render();
        $expected = array("test <= ?", array(1));
        self::assertSame($expected, $actual);
    }

    public function testIn()
    {
        $filter = new ColumnFilter('test', 'in', array(1, 2, 3));
        $actual = $filter->render();
        $expected = array("test IN (?, ?, ?)", array(1, 2, 3));
        self::assertSame($expected, $actual);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage IN filter requires an array with one or more values
     */
    public function testInWrongParam()
    {
        $filter = new ColumnFilter('test', 'in', 1);
        $filter->render();
    }

    public function testNotIn()
    {
        $filter = new ColumnFilter('test', 'not in', array(1, 2, 3));
        $actual = $filter->render();
        $expected = array("test NOT IN (?, ?, ?)", array(1, 2, 3));
        self::assertSame($expected, $actual);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage NOT IN filter requires an array with one or more values
     */
    public function testNotInWrongParam()
    {
        $filter = new ColumnFilter('test', 'not in', 1);
        $filter->render();
    }

    public function testIsNull()
    {
        $filter = new ColumnFilter('test', 'is null');
        $actual = $filter->render();
        $expected = array("test IS NULL", array());
        self::assertSame($expected, $actual);
    }

    public function testNotNull()
    {
        $filter = new ColumnFilter('test', 'not null');
        $actual = $filter->render();
        $expected = array("test IS NOT NULL", array());
        self::assertSame($expected, $actual);
    }

    public function testLike()
    {
        $filter = new ColumnFilter('test', 'like', '%foo%');
        $actual = $filter->render();
        $expected = array("test LIKE ?", array('%foo%'));
        self::assertSame($expected, $actual);
    }

    public function testNotLike()
    {
        $filter = new ColumnFilter('test', 'not like', '%bar%');
        $actual = $filter->render();
        $expected = array("test NOT LIKE ?", array('%bar%'));
        self::assertSame($expected, $actual);
    }

    public function testBetween()
    {
        $filter = new ColumnFilter('test', 'between', array(10, 20));
        $actual = $filter->render();
        $expected = array("test BETWEEN ? AND ?", array(10, 20));
        self::assertSame($expected, $actual);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage BETWEEN filter requires an array of two values.
     */
    public function testBetweenWrongParam1()
    {
        $filter = new ColumnFilter('test', 'between', 'xxx');
        $filter->render();
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage BETWEEN filter requires an array of two values.
     */
    public function testBetweenWrongParam2()
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

    public function testCaseInsensitiveLike()
    {
        $qs = Person::objects()->filter('name', 'ilike', 'pero');

        $qs->delete();
        self::assertFalse($qs->exists());

        Person::fromArray(array('name' => "PERO"))->insert();
        Person::fromArray(array('name' => "pero"))->insert();
        Person::fromArray(array('name' => "Pero"))->insert();
        Person::fromArray(array('name' => "pERO"))->insert();

        self::assertSame(4, $qs->count());
        self::assertCount(4, $qs->fetch());
    }

    public function testFilterFromArray()
    {
        $actual = ColumnFilter::fromArray(array('id', '=', 123));

        self::assertInstanceOf('\\Phormium\\ColumnFilter', $actual);
        self::assertSame('id', $actual->column);
        self::assertSame('=', $actual->operation);
        self::assertSame(123, $actual->value);

        $actual = ColumnFilter::fromArray(array('email', 'null'));

        self::assertInstanceOf('\\Phormium\\ColumnFilter', $actual);
        self::assertSame('email', $actual->column);
        self::assertSame('NULL', $actual->operation);
        self::assertNull($actual->value);
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

    /**
     * @expectedException \Exception
     */
    public function testFilterFromArrayExceptionWrongType()
    {
        $actual = ColumnFilter::fromArray(1);
    }
}
