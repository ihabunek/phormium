<?php

namespace Phormium\Tests;

use \Phormium\f;
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
        $f = f::eq('name', 'x');
        $qs1 = Person::objects();
        $qs2 = $qs1->filter($f);

        self::assertNotEquals($qs1, $qs2);
        self::assertNotSame($qs1, $qs2);

        self::assertEmpty($qs1->getFilters());
        self::assertEmpty($qs1->getOrder());
        self::assertEmpty($qs2->getOrder());

        $expected = array($f);
        $actual = $qs2->getFilters();
        self::assertSame($expected, $actual);
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
}
