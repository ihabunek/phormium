<?php

namespace Phormium\Tests;

use \Phormium\Parser;
use \Phormium\Filter;
use \Phormium\f;

class FilterTest extends \PHPUnit_Framework_TestCase
{
    private $metaPerson;
    private $metaTrade;

    public function setUp()
    {
        $this->metaPerson = Models\Person::getMeta();
        $this->metaTrade = Models\Trade::getMeta();
    }

    public function testEq()
    {
        $filter = new Filter('test', '=', 1) ;
        $actual = $filter->render();
        $expected = array("test = ?", array(1));
        self::assertSame($expected, $actual);
    }

    public function testNeq1()
    {
        $filter = new Filter('test', '!=', 1) ;
        $actual = $filter->render();
        $expected = array("test != ?", array(1));
        self::assertSame($expected, $actual);
    }

    public function testNeq2()
    {
        $filter = new Filter('test', '<>', 1) ;
        $actual = $filter->render();
        $expected = array("test <> ?", array(1));
        self::assertSame($expected, $actual);
    }

    public function testGt()
    {
        $filter = new Filter('test', '>', 1) ;
        $actual = $filter->render();
        $expected = array("test > ?", array(1));
        self::assertSame($expected, $actual);
    }

    public function testGte()
    {
        $filter = new Filter('test', '>=', 1) ;
        $actual = $filter->render();
        $expected = array("test >= ?", array(1));
        self::assertSame($expected, $actual);
    }

    public function testLt()
    {
        $filter = new Filter('test', '<', 1) ;
        $actual = $filter->render();
        $expected = array("test < ?", array(1));
        self::assertSame($expected, $actual);
    }

    public function testLte()
    {
        $filter = new Filter('test', '<=', 1) ;
        $actual = $filter->render();
        $expected = array("test <= ?", array(1));
        self::assertSame($expected, $actual);
    }

    public function testIn()
    {
        $filter = new Filter('test', 'in', array(1, 2, 3)) ;
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
        $filter = new Filter('test', 'in', 1);
        $filter->render();
    }

    public function testNotIn()
    {
        $filter = new Filter('test', 'not in', array(1, 2, 3)) ;
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
        $filter = new Filter('test', 'not in', 1) ;
        $filter->render();
    }

    public function testIsNull()
    {
        $filter = new Filter('test', 'is null');
        $actual = $filter->render();
        $expected = array("test IS NULL", array());
        self::assertSame($expected, $actual);
    }

    public function testNotNull()
    {
        $filter = new Filter('test', 'not null');
        $actual = $filter->render();
        $expected = array("test IS NOT NULL", array());
        self::assertSame($expected, $actual);
    }

    public function testLike()
    {
        $filter = new Filter('test', 'like', '%foo%');
        $actual = $filter->render();
        $expected = array("test LIKE ?", array('%foo%'));
        self::assertSame($expected, $actual);
    }

    public function testNotLike()
    {
        $filter = new Filter('test', 'not like', '%bar%');
        $actual = $filter->render();
        $expected = array("test NOT LIKE ?", array('%bar%'));
        self::assertSame($expected, $actual);
    }

    public function testBetween()
    {
        $filter = new Filter('test', 'between', array(10, 20));
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
        $filter = new Filter('test', 'between', 'xxx');
        $filter->render();
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage BETWEEN filter requires an array of two values.
     */
    public function testBetweenWrongParam2()
    {
        $filter = new Filter('test', 'between', array(1));
        $filter->render();
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Unknown filter operation [XXX]
     */
    public function testUnknownOp()
    {
        $filter = new Filter('test', 'xxx');
        $filter->render();
    }
}
