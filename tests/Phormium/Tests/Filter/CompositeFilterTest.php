<?php

namespace Phormium\Tests;


use Phormium\Filter\ColumnFilter;
use Phormium\Filter\CompositeFilter;
use Phormium\Filter\Filter;

/**
 * @group filter
 */
class CompositeFilterTest extends \PHPUnit_Framework_TestCase
{
    public function testFactoryAndOr()
    {
        $actual = Filter::_and();
        $expected = new CompositeFilter(CompositeFilter::OP_AND);
        $this->assertEquals($expected, $actual);

        $actual = Filter::_or();
        $expected = new CompositeFilter(CompositeFilter::OP_OR);
        $this->assertEquals($expected, $actual);
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

        $actual = $filter->render();
        $expected = ["(id = ? OR id = ? OR id = ?)", [1, 2, 3]];
        $this->assertSame($expected, $actual);
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

        $actual = $filter->render();
        $expected = ["(id = ? OR id = ? OR id = ?)", [1, 2, 3]];
        $this->assertSame($expected, $actual);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Invalid composite filter operation [foo]. Expected one of: AND, OR
     */
    public function testInvalidOperation()
    {
        $filter = new CompositeFilter('foo');
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Canot render composite filter. No filters defined.
     */
    public function testRenderEmpty()
    {
        $filter = new CompositeFilter("AND");
        $filter->render();
    }
}
