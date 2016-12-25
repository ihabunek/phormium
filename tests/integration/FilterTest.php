<?php

namespace Phormium\Tests\Integration;

use Phormium\Filter\CompositeFilter;
use Phormium\Filter\RawFilter;
use Phormium\Orm;
use Phormium\Tests\Models\Person;

/**
 * @group filter
 */
class FilterTest extends \PHPUnit_Framework_TestCase
{
    public static function setUpBeforeClass()
    {
        Orm::configure(PHORMIUM_CONFIG_FILE);
    }

    public function testCaseInsensitiveLike()
    {
        $qs = Person::objects()->filter('name', 'ilike', 'pero');

        $qs->delete();
        $this->assertFalse($qs->exists());

        Person::fromArray(['name' => "PERO"])->insert();
        Person::fromArray(['name' => "pero"])->insert();
        Person::fromArray(['name' => "Pero"])->insert();
        Person::fromArray(['name' => "pERO"])->insert();

        $this->assertSame(4, $qs->count());
        $this->assertCount(4, $qs->fetch());
    }

    function testRawFilter()
    {
        $condition = "lower(name) = ?";
        $arguments = ['foo'];

        $qs = Person::objects()->filter($condition, $arguments);

        $filter1 = $qs->getFilter();
        $expected = CompositeFilter::class;
        $this->assertInstanceOf($expected, $filter1);
        $this->assertSame('AND', $filter1->operation());

        $filters = $filter1->filters();
        $this->assertCount(1, $filters);

        $filter2 = $filters[0];
        $expected = RawFilter::class;
        $this->assertInstanceOf($expected, $filter2);

        $this->assertSame($condition, $filter2->condition());
        $this->assertSame($arguments, $filter2->arguments());
    }
}
