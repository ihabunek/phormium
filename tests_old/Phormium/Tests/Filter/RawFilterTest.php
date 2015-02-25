<?php

namespace Phormium\Tests;

use Phormium\Tests\Models\Person;

use Phormium\Filter\Filter;
use Phormium\Filter\RawFilter;

/**
 * @group filter
 */
class RawFilterTest extends \PHPUnit_Framework_TestCase
{
    function testConstruction()
    {
        $condition = "lower(name) = ?";
        $arguments = array('foo');

        $filter = new RawFilter($condition, $arguments);
        $actual = $filter->render();
        $expected = array($condition, $arguments);
    }

    function testFactory()
    {
        $condition = "lower(name) = ?";
        $arguments = array('foo');

        $filter = Filter::raw($condition, $arguments);
        $actual = $filter->render();
        $expected = array($condition, $arguments);
    }

    function testQuerySet()
    {
        $condition = "lower(name) = ?";
        $arguments = array('foo');

        $qs = Person::objects()->filter($condition, $arguments);

        $filter1 = $qs->getFilter();
        $expected = "\\Phormium\\Filter\\CompositeFilter";
        $this->assertInstanceOf($expected, $filter1);
        $this->assertSame('AND', $filter1->getOperation());

        $filters = $filter1->getFilters();
        $this->assertCount(1, $filters);

        $filter2 = $filters[0];
        $expected = "\\Phormium\\Filter\\RawFilter";
        $this->assertInstanceOf($expected, $filter2);

        $this->assertSame($condition, $filter2->condition);
        $this->assertSame($arguments, $filter2->arguments);
    }
}
