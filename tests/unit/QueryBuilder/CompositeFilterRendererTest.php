<?php

namespace Phormium\Tests\Unit\QueryBuilder;

use Phormium\Filter\ColumnFilter;
use Phormium\Filter\CompositeFilter;
use Phormium\Filter\Filter;
use Phormium\Query\QuerySegment;
use Phormium\QueryBuilder\Common\FilterRenderer;
use Phormium\QueryBuilder\Common\Quoter;

/**
 * @group unit
 * @group querybuilder
 */
class CompositeRendererTest extends \PHPUnit_Framework_TestCase
{
    private function render(Filter $filter)
    {
        $renderer = new FilterRenderer(new Quoter());
        return $renderer->renderFilter($filter);
    }

    public function testCompositeFilter1()
    {
        $filter = new CompositeFilter(
            CompositeFilter::OP_OR,
            [
                ColumnFilter::fromArray(['id', '=', 1]),
                ColumnFilter::fromArray(['id', '=', 2]),
                ColumnFilter::fromArray(['id', '=', 3]),
            ]
        );

        $actual = $this->render($filter);
        $expected = new QuerySegment('("id" = ? OR "id" = ? OR "id" = ?)', [1, 2, 3]);
        $this->assertEquals($expected, $actual);
    }

    public function testCompositeFilter2()
    {
        $filter = new CompositeFilter(
            CompositeFilter::OP_OR,
            [
                ['id', '=', 1],
                ['id', '=', 2],
                ['id', '=', 3],
            ]
        );

        $actual = $this->render($filter);
        $expected = new QuerySegment('("id" = ? OR "id" = ? OR "id" = ?)', [1, 2, 3]);
        $this->assertEquals($expected, $actual);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Canot render composite filter. No filters defined.
     */
    public function testRenderEmpty()
    {
        $filter = new CompositeFilter("AND");
        $this->render($filter);
    }
}