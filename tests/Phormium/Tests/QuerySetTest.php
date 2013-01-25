<?php

namespace Phormium\Tests;

use \Phormium\a;
use \Phormium\Aggregate;
use \Phormium\Filter;
use \Phormium\Meta;
use \Phormium\QuerySet;
use \Phormium\Tests\Models\Person;

class QuerySetTest extends \PHPUnit_Framework_TestCase
{
    public function testCloneQS()
    {
        $qs1 = Person::objects();
        $qs2 = $qs1->all();

        self::assertEquals($qs1, $qs2);
        self::assertNotSame($qs1, $qs2);
        self::assertEmpty($qs1->getFilters());
        self::assertEmpty($qs2->getFilters());
        self::assertEmpty($qs1->getOrder());
        self::assertEmpty($qs2->getOrder());
    }

    public function testFilterQS()
    {
        $f = new Filter('name', '=', 'x');
        $qs1 = Person::objects();
        $qs2 = $qs1->filter('name', '=', 'x');

        self::assertNotEquals($qs1, $qs2);
        self::assertNotSame($qs1, $qs2);

        self::assertEmpty($qs1->getFilters());
        self::assertEmpty($qs1->getOrder());
        self::assertEmpty($qs2->getOrder());

        $expected = array($f);
        $actual = $qs2->getFilters();
        self::assertEquals($expected, $actual);
    }

    public function testOrderQS()
    {
        $qs1 = Person::objects();
        $qs2 = $qs1->orderBy('name', 'desc');

        self::assertNotEquals($qs1, $qs2);
        self::assertNotSame($qs1, $qs2);

        self::assertEmpty($qs1->getFilters());
        self::assertEmpty($qs1->getOrder());
        self::assertEmpty($qs2->getFilters());

        $expected = array('name desc');
        $actual = $qs2->getOrder();
        self::assertSame($expected, $actual);

        $qs3 = $qs2->orderBy('id');
        $expected = array('name desc', 'id asc');
        $actual = $qs3->getOrder();
        self::assertSame($expected, $actual);
    }

    public function testAggregates()
    {
        // Create some sample data
        $uniq = uniqid('agg');

        $p1 = array(
            'name' => "{$uniq}_1",
            'birthday' => '2000-01-01',
            'income' => 10000
        );

        $p2 = array(
            'name' => "{$uniq}_2",
            'birthday' => '2001-01-01',
            'income' => 20000
        );

        $p3 = array(
            'name' => "{$uniq}_3",
            'birthday' => '2002-01-01',
            'income' => 30000
        );

		self::assertFalse(Person::objects()->filter('birthday', '=', '2000-01-01')->exists());

        Person::fromArray($p1)->save();
        Person::fromArray($p2)->save();
        Person::fromArray($p3)->save();

		self::assertTrue(Person::objects()->filter('birthday', '=', '2000-01-01')->exists());

        // Query set filtering the above created records
        $qs = Person::objects()->filter('name', 'like', "$uniq%");

        $count = $qs->count();
        self::assertSame(3, $count);

        self::assertSame('2000-01-01', $qs->aggregate(a::min('birthday')));
        self::assertSame('2002-01-01', $qs->aggregate(a::max('birthday')));

        self::assertEquals(10000, $qs->aggregate(a::min('income')));
        self::assertEquals(20000, $qs->aggregate(a::avg('income')));
        self::assertEquals(60000, $qs->aggregate(a::sum('income')));
        self::assertEquals(30000, $qs->aggregate(a::max('income')));
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Invalid aggregate type [foo]
     */
    public function testAggregatesFail1()
    {
        a::foo('bar');
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Invalid aggregate type [foo]
     */
    public function testAggregatesFail2()
    {
        new Aggregate('foo', 'bar');
    }
}
