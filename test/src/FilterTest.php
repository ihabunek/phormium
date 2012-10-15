<?php

namespace Phormium\Test;

use \Phormium\Parser;
use \Phormium\Filter;
use \Phormium\f;

class FilterTest extends \PHPUnit_Framework_TestCase
{
    private $model;

    public function __construct()
    {
        $this->model = TestEntity::getModel();
    }

    public function testEq()
    {
        $filter = f::eq('test', 1);
        $actual = $filter->render($this->model);
        $expected = array("test = ?", array(1));
        self::assertSame($expected, $actual);
    }

    public function testNeq()
    {
        $filter = f::neq('test', 1);
        $actual = $filter->render($this->model);
        $expected = array("test <> ?", array(1));
        self::assertSame($expected, $actual);
    }

    public function testGt()
    {
        $filter = f::gt('test', 1);
        $actual = $filter->render($this->model);
        $expected = array("test > ?", array(1));
        self::assertSame($expected, $actual);
    }

    public function testGte()
    {
        $filter = f::gte('test', 1);
        $actual = $filter->render($this->model);
        $expected = array("test >= ?", array(1));
        self::assertSame($expected, $actual);
    }

    public function testLt()
    {
        $filter = f::lt('test', 1);
        $actual = $filter->render($this->model);
        $expected = array("test < ?", array(1));
        self::assertSame($expected, $actual);
    }

    public function testLte()
    {
        $filter = f::lte('test', 1);
        $actual = $filter->render($this->model);
        $expected = array("test <= ?", array(1));
        self::assertSame($expected, $actual);
    }

    public function testIn()
    {
        $filter = f::in('test', array(1, 2, 3));
        $actual = $filter->render($this->model);
        $expected = array("test IN (?, ?, ?)", array(1, 2, 3));
        self::assertSame($expected, $actual);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessageBETWEEN filter requires an array of two values.
     */
    public function testInWrongParam()
    {
        $filter = new Filter(Filter::OP_IN, 'test', 1);
        $filter->render($this->model);
    }

    public function testNotIn()
    {
        $filter = f::nin('test', array(1, 2, 3));
        $actual = $filter->render($this->model);
        $expected = array("test NOT IN (?, ?, ?)", array(1, 2, 3));
        self::assertSame($expected, $actual);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessageBETWEEN filter requires an array of two values.
     */
    public function testNotInWrongParam()
    {
        $filter = new Filter(Filter::OP_NOT_IN, 'test', 1);
        $filter->render($this->model);
    }

    public function testPK()
    {
        $filter = f::pk(1);
        $actual = $filter->render($this->model);
        $expected = array("id = ?", array(1));
        self::assertSame($expected, $actual);
    }

    public function testIsNull()
    {
        $filter = f::isNull('test');
        $actual = $filter->render($this->model);
        $expected = array("test IS NULL", array());
        self::assertSame($expected, $actual);
    }

    public function testNotNull()
    {
        $filter = f::notNull('test');
        $actual = $filter->render($this->model);
        $expected = array("test IS NOT NULL", array());
        self::assertSame($expected, $actual);
    }

    public function testLike()
    {
        $filter = f::like('test', '%foo%');
        $actual = $filter->render($this->model);
        $expected = array("test LIKE ?", array('%foo%'));
        self::assertSame($expected, $actual);
    }

    public function testNotLike()
    {
        $filter = f::notLike('test', '%bar%');
        $actual = $filter->render($this->model);
        $expected = array("test NOT LIKE ?", array('%bar%'));
        self::assertSame($expected, $actual);
    }

    public function testBetween()
    {
        $filter = f::between('test', 10, 20);
        $actual = $filter->render($this->model);
        $expected = array("test BETWEEN ? AND ?", array(10, 20));
        self::assertSame($expected, $actual);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessageBETWEEN filter requires an array of two values.
     */
    public function testBetweenWrongParam1()
    {
        $filter = new Filter(Filter::OP_BETWEEN, 'test', 1);
        $filter->render($this->model);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessageBETWEEN filter requires an array of two values.
     */
    public function testBetweenWrongParam2()
    {
        $filter = new Filter(Filter::OP_BETWEEN, 'test', array(1));
        $filter->render($this->model);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Filter [foo] is not implemented
     */
    public function testUnknownOp1()
    {
        f::foo('test', 10);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Render not defined for operation [foo].
     */
    public function testUnknownOp2()
    {
        $filter = new Filter('foo', 'test', 1);
        $filter->render($this->model);
    }
}
