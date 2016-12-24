<?php

namespace Phormium\Tests\Unit\QueryBuilder;

use Phormium\Filter\ColumnFilter;
use Phormium\Filter\Filter;
use Phormium\Query\QuerySegment;
use Phormium\QueryBuilder\Common\FilterRenderer;
use Phormium\QueryBuilder\Common\Quoter;

/**
 * @group unit
 * @group querybuilder
 */
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

    public function testNotIn()
    {
        $filter = new ColumnFilter('test', 'not in', [1, 2, 3]);
        $actual = $this->render($filter);
        $expected = new QuerySegment('"test" NOT IN (?, ?, ?)', [1, 2, 3]);
        $this->assertEquals($expected, $actual);
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
}