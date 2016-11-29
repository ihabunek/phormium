<?php

namespace Phormium\Tests\Query;

use Phormium\Query\QuerySegment;


class QuerySegmentTest extends \PHPUnit_Framework_TestCase
{
    public function testConstruct()
    {
        $query = "foo = ?";
        $args = ["bar"];

        $qs = new QuerySegment($query, $args);
        $this->assertSame($query, $qs->query());
        $this->assertSame($args, $qs->args());
    }

    public function testCombine()
    {
        $qs1 = new QuerySegment("WHERE a = ?", ["foo"]);
        $qs2 = new QuerySegment("AND b = ?", ["bar"]);

        $qsc = QuerySegment::combine($qs1, $qs2);
        $this->assertSame("WHERE a = ? AND b = ?", $qsc->query());
        $this->assertSame(["foo", "bar"], $qsc->args());
    }

    public function testReduce()
    {
        $qs1 = new QuerySegment("SELECT *", []);
        $qs2 = new QuerySegment("FROM table", []);
        $qs3 = new QuerySegment("WHERE a = ?", ["foo"]);
        $qs4 = new QuerySegment("AND b BETWEEN ? AND ?", ["bar", "baz"]);
        $qs5 = new QuerySegment("AND c > ?", ["qux"]);

        $reduced = QuerySegment::reduce([$qs1, $qs2, $qs3, $qs4, $qs5]);

        $expectedQuery = "SELECT * FROM table WHERE a = ? AND b BETWEEN ? AND ? AND c > ?";
        $expectedArgs = ["foo", "bar", "baz", "qux"];

        $this->assertSame($expectedQuery, $reduced->query());
        $this->assertSame($expectedArgs, $reduced->args());
    }
}
