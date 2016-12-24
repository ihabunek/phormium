<?php

namespace Phormium\Tests\Unit\QueryBuilder;

use Phormium\Filter\Filter;
use Phormium\Filter\RawFilter;
use Phormium\Query\QuerySegment;
use Phormium\QueryBuilder\Common\FilterRenderer;
use Phormium\QueryBuilder\Common\Quoter;

/**
 * @group unit
 * @group querybuilder
 */
class RawFilterRendererTest extends \PHPUnit_Framework_TestCase
{
    private function render(Filter $filter)
    {
        $renderer = new FilterRenderer(new Quoter());
        return $renderer->renderFilter($filter);
    }

    function testConstruction()
    {
        $condition = "lower(name) = ?";
        $arguments = ['foo'];

        $filter = new RawFilter($condition, $arguments);
        $actual = $this->render($filter);
        $expected = new QuerySegment($condition, $arguments);

        $this->assertEquals($expected, $actual);
    }
}