<?php

namespace Phormium\Tests\QueryBuilder;

use Phormium\Filter\ColumnFilter;
use Phormium\Filter\Filter;
use Phormium\Query\QuerySegment;
use Phormium\QueryBuilder\Common\FilterRenderer;
use Phormium\QueryBuilder\Common\Quoter;

class ColumnFilterRendererTest extends \PHPUnit_Framework_TestCase
{
    private function render(Filter $filter)
    {
        $renderer = new FilterRenderer(new Quoter());
        return $renderer->renderFilter($filter);
    }

    public function testEq()
    {
        $filter = new ColumnFilter('test', '=', 1);
        $actual = $this->render($filter);
        $expected = new QuerySegment('"test" = ?', [1]);
        $this->assertEquals($expected, $actual);
    }

    public function testEqNull()
    {
        $filter = new ColumnFilter('test', '=', null);
        $actual = $this->render($filter);
        $expected = new QuerySegment('"test" IS NULL', []);
        $this->assertEquals($expected, $actual);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Filter = requires a scalar value, array given.
     */
    public function testEqWrongParam()
    {
        $filter = new ColumnFilter('test', '=', []);
        $this->render($filter);
    }

    public function testNeq1()
    {
        $filter = new ColumnFilter('test', '!=', 1);
        $actual = $this->render($filter);
        $expected = new QuerySegment('"test" != ?', [1]);
        $this->assertEquals($expected, $actual);
    }

    public function testNeq2()
    {
        $filter = new ColumnFilter('test', '<>', 1);
        $actual = $this->render($filter);
        $expected = new QuerySegment('"test" <> ?', [1]);
        $this->assertEquals($expected, $actual);
    }

    public function testNeqNull1()
    {
        $filter = new ColumnFilter('test', '<>', null);
        $actual = $this->render($filter);
        $expected = new QuerySegment('"test" IS NOT NULL', []);
        $this->assertEquals($expected, $actual);
    }

    public function testNeqNull2()
    {
        $filter = new ColumnFilter('test', '!=', null);
        $actual = $this->render($filter);
        $expected = new QuerySegment('"test" IS NOT NULL', []);
        $this->assertEquals($expected, $actual);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Filter != requires a scalar value, array given.
     */
    public function testNeqWrongParam()
    {
        $filter = new ColumnFilter('test', '!=', []);
        $this->render($filter);
    }

    public function testGt()
    {
        $filter = new ColumnFilter('test', '>', 1);
        $actual = $this->render($filter);
        $expected = new QuerySegment('"test" > ?', [1]);
        $this->assertEquals($expected, $actual);
    }

    public function testGte()
    {
        $filter = new ColumnFilter('test', '>=', 1);
        $actual = $this->render($filter);
        $expected = new QuerySegment('"test" >= ?', [1]);
        $this->assertEquals($expected, $actual);
    }

    public function testLt()
    {
        $filter = new ColumnFilter('test', '<', 1);
        $actual = $this->render($filter);
        $expected = new QuerySegment('"test" < ?', [1]);
        $this->assertEquals($expected, $actual);
    }

    public function testLte()
    {
        $filter = new ColumnFilter('test', '<=', 1);
        $actual = $this->render($filter);
        $expected = new QuerySegment('"test" <= ?', [1]);
        $this->assertEquals($expected, $actual);
    }

    public function testIn()
    {
        $filter = new ColumnFilter('test', 'in', [1, 2, 3]);
        $actual = $this->render($filter);
        $expected = new QuerySegment('"test" IN (?, ?, ?)', [1, 2, 3]);
        $this->assertEquals($expected, $actual);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Filter IN requires an array, integer given.
     */
    public function testInWrongParam1()
    {
        $filter = new ColumnFilter('test', 'in', 1);
        $this->render($filter);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Filter IN requires an array, string given.
     */
    public function testInWrongParam2()
    {
        $filter = new ColumnFilter('test', 'in', "1");
        $this->render($filter);
    }
    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Filter IN requires an array, NULL given.
     */
    public function testInWrongParam3()
    {
        $filter = new ColumnFilter('test', 'in', null);
        $this->render($filter);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Filter IN requires a non-empty array, empty array given.
     */
    public function testInWrongParam4()
    {
        $filter = new ColumnFilter('test', 'in', []);
        $this->render($filter);
    }

    public function testNotIn()
    {
        $filter = new ColumnFilter('test', 'not in', [1, 2, 3]);
        $actual = $this->render($filter);
        $expected = new QuerySegment('"test" NOT IN (?, ?, ?)', [1, 2, 3]);
        $this->assertEquals($expected, $actual);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Filter NOT IN requires an array, integer given.
     */
    public function testNotInWrongParam1()
    {
        $filter = new ColumnFilter('test', 'not in', 1);
        $this->render($filter);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Filter NOT IN requires an array, string given.
     */
    public function testNotInWrongParam2()
    {
        $filter = new ColumnFilter('test', 'not in', "1");
        $this->render($filter);
    }
    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Filter NOT IN requires an array, NULL given.
     */
    public function testNotInWrongParam3()
    {
        $filter = new ColumnFilter('test', 'not in', null);
        $this->render($filter);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Filter NOT IN requires a non-empty array, empty array given.
     */
    public function testNotInWrongParam4()
    {
        $filter = new ColumnFilter('test', 'not in', []);
        $this->render($filter);
    }

    public function testIsNull()
    {
        $filter = new ColumnFilter('test', 'is null');
        $actual = $this->render($filter);
        $expected = new QuerySegment('"test" IS NULL', []);
        $this->assertEquals($expected, $actual);
    }

    public function testNotNull()
    {
        $filter = new ColumnFilter('test', 'not null');
        $actual = $this->render($filter);
        $expected = new QuerySegment('"test" IS NOT NULL', []);
        $this->assertEquals($expected, $actual);
    }

    public function testNotNull2()
    {
        $filter = new ColumnFilter('test', 'is not null');
        $actual = $this->render($filter);
        $expected = new QuerySegment('"test" IS NOT NULL', []);
        $this->assertEquals($expected, $actual);
    }

    public function testLike()
    {
        $filter = new ColumnFilter('test', 'like', '%foo%');
        $actual = $this->render($filter);
        $expected = new QuerySegment('"test" LIKE ?', ['%foo%']);
        $this->assertEquals($expected, $actual);
    }

    public function testILike()
    {
        $filter = new ColumnFilter('test', 'ilike', '%foo%');
        $actual = $this->render($filter);
        $expected = new QuerySegment('lower("test") LIKE lower(?)', ['%foo%']);
        $this->assertEquals($expected, $actual);
    }

    public function testNotLike()
    {
        $filter = new ColumnFilter('test', 'not like', '%bar%');
        $actual = $this->render($filter);
        $expected = new QuerySegment('"test" NOT LIKE ?', ['%bar%']);
        $this->assertEquals($expected, $actual);
    }

    public function testBetween()
    {
        $filter = new ColumnFilter('test', 'between', [10, 20]);
        $actual = $this->render($filter);
        $expected = new QuerySegment('"test" BETWEEN ? AND ?', [10, 20]);
        $this->assertEquals($expected, $actual);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Filter BETWEEN requires an array, string given.
     */
    public function testBetweenWrongParam1()
    {
        $filter = new ColumnFilter('test', 'between', 'xxx');
        $this->render($filter);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Filter BETWEEN requires an array, NULL given.
     */
    public function testBetweenWrongParam2()
    {
        $filter = new ColumnFilter('test', 'between', null);
        $this->render($filter);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Filter BETWEEN requires an array with 2 values, given array has 1 values.
     */
    public function testBetweenWrongParam3()
    {
        $filter = new ColumnFilter('test', 'between', [1]);
        $this->render($filter);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Unknown filter operation [XXX]
     */
    public function testUnknownOp()
    {
        $filter = new ColumnFilter('test', 'xxx');
        $this->render($filter);
    }



}