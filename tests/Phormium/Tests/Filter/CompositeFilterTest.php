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

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Invalid composite filter operation [foo]. Expected one of: AND, OR
     */
    public function testInvalidOperation()
    {
        $filter = new CompositeFilter('foo');
    }
}
