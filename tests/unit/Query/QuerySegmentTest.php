<?php

use Phormium\Query\QuerySegment;

/**
 * @group query
 * @group unit
 */
class QuerySegmentTest extends \PHPUnit_Framework_TestCase
{
    public function testConstruct()
    {
        $query = "foo = ?";
        $args = ["bar"];

        $qs = new QuerySegment($query, $args);
        $this->assertSame($query, $qs->query());
        $this->assertSame($args, $qs->args());

        // Default args
        $qs = new QuerySegment();
        $this->assertSame("", $qs->query());
        $this->assertSame([], $qs->args());
    }

    public function testCombine()
    {
        $qs1 = new QuerySegment("WHERE a = ?", ["foo"]);
        $qs2 = new QuerySegment("AND b = ?", ["bar"]);

        $qsc = $qs1->combine($qs2);
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

        $this->assertInstanceOf("Phormium\Query\QuerySegment", $reduced);
        $this->assertSame($expectedQuery, $reduced->query());
        $this->assertSame($expectedArgs, $reduced->args());
    }

    public function testImplode()
    {
        $qs1 = new QuerySegment("foo", [1]);
        $qs2 = new QuerySegment("bar", []);
        $qs3 = new QuerySegment("baz", [3, 4]);
        $separator = new QuerySegment("x", ['y']);

        $imploded = QuerySegment::implode($separator, [$qs1, $qs2, $qs3]);

        $expectedQuery = "foo x bar x baz";
        $expectedArgs = [1, 'y', 'y', 3, 4];

        $this->assertInstanceOf("Phormium\Query\QuerySegment", $imploded);
        $this->assertSame($expectedQuery, $imploded->query());
        $this->assertSame($expectedArgs, $imploded->args());
    }

    public function testImplodeEmpty()
    {
        $separator = new QuerySegment("x", ['y']);

        $imploded = QuerySegment::implode($separator, []);
        $this->assertSame("", $imploded->query());
        $this->assertSame([], $imploded->args());

    }

    public function testImplodeSingle()
    {
        $segment = new QuerySegment("foo", ['bar']);
        $separator = new QuerySegment("bla", ['tra']);

        $imploded = QuerySegment::implode($separator, [$segment]);
        $this->assertSame($segment, $imploded);

    }

    public function testEmbrace()
    {
        $query = "a = ? AND b = ?";
        $args = [1, 2];

        $segment = new QuerySegment($query, $args);
        $embraced = QuerySegment::embrace($segment);

        $this->assertSame("($query)", $embraced->query());
        $this->assertSame($args, $embraced->args());
    }
}
