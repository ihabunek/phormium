<?php

namespace Phormium\Tests\QueryBuilder;

use Phormium\Database\Driver;
use Phormium\Filter\Filter;
use Phormium\Query\QuerySegment;
use Phormium\QueryBuilder\QueryBuilderFactory;
use Phormium\QueryBuilder\QueryBuilderInterface;
use Phormium\Aggregate;

class QueryBuilderTest extends \PHPUnit_Framework_TestCase
{
    /** @return QueryBuilderInterface */
    private function getQueryBuilder($driver = "common")
    {
        $factory = new QueryBuilderFactory();
        return $factory->getQueryBuilder($driver);
    }

    public function testBuildSelectCommon()
    {
        $queryBuilder = $this->getQueryBuilder();

        $table = "foo";
        $columns = ["a", "b", "c"];
        $filter = Filter::_and(
            Filter::col("xx", "=", "yy"),
            Filter::col("yy", "not null"),
            Filter::_or(
                Filter::col("zz", "between", [1, 2]),
                Filter::raw("max(?) > 0", [100])
            )
        );

        $limit = 123;
        $offset = 321;
        $order = ["a desc", "b asc"];

        $segment = $queryBuilder->buildSelect($table, $columns, $filter, $limit, $offset, $order);

        $expectedQuery =
            'SELECT "a", "b", "c" ' .
            'FROM "foo" '  .
            'WHERE ("xx" = ? AND "yy" IS NOT NULL AND ("zz" BETWEEN ? AND ? OR max(?) > 0)) ' .
            'ORDER BY a desc, b asc ' .
            'LIMIT ? OFFSET ?';

        $expectedArgs = ["yy", 1, 2, 100, 123, 321];

        $this->assertSame($expectedQuery, $segment->query());
        $this->assertSame($expectedArgs, $segment->args());
    }

    public function testBuildSelectMysql()
    {
        $queryBuilder = $this->getQueryBuilder(Driver::MYSQL);

        $table = "foo";
        $columns = ["a", "b", "c"];
        $filter = Filter::_and(
            Filter::col("xx", "=", "yy"),
            Filter::col("yy", "not null"),
            Filter::_or(
                Filter::col("zz", "between", [1, 2]),
                Filter::raw("max(?) > 0", [100])
            )
        );

        $limit = 123;
        $offset = 321;
        $order = ["a desc", "b asc"];

        $segment = $queryBuilder->buildSelect($table, $columns, $filter, $limit, $offset, $order);

        $expectedQuery =
            'SELECT `a`, `b`, `c` ' .
            'FROM `foo` '  .
            'WHERE (`xx` = ? AND `yy` IS NOT NULL AND (`zz` BETWEEN ? AND ? OR max(?) > 0)) ' .
            'ORDER BY a desc, b asc ' .
            'LIMIT ? OFFSET ?';

        $expectedArgs = ["yy", 1, 2, 100, 123, 321];

        $this->assertSame($expectedQuery, $segment->query());
        $this->assertSame($expectedArgs, $segment->args());
    }

    public function testBuildSelectAggregate()
    {
        $queryBuilder = $this->getQueryBuilder();

        $table = "foo";
        $aggregate = new Aggregate(Aggregate::MAX, 'xx');
        $filter = Filter::_and(
            Filter::col("xx", "=", "yy"),
            Filter::col("yy", "not null"),
            Filter::_or(
                Filter::col("zz", "between", [1, 2]),
                Filter::raw("max(?) > 0", [100])
            )
        );

        $segment = $queryBuilder->buildSelectAggregate($table, $aggregate, $filter);

        $expectedQuery =
            'SELECT max("xx") AS aggregate ' .
            'FROM "foo" '  .
            'WHERE ("xx" = ? AND "yy" IS NOT NULL AND ("zz" BETWEEN ? AND ? OR max(?) > 0))';

        $expectedArgs = ["yy", 1, 2, 100];

        $this->assertSame($expectedQuery, $segment->query());
        $this->assertSame($expectedArgs, $segment->args());
    }

    public function testBuildInsert()
    {
        $queryBuilder = $this->getQueryBuilder();

        $table = "foo";
        $columns = ["a", "b", "c"];
        $values = [42, 17, 33];
        $returning = "a";

        $segment = $queryBuilder->buildInsert($table, $columns, $values, $returning);

        $expectedQuery = 'INSERT INTO "foo" ("a", "b", "c") VALUES (?, ?, ?)';

        $this->assertSame($expectedQuery, $segment->query());
        $this->assertSame($values, $segment->args());
    }

    public function testBuildInsertPostgres()
    {
        $queryBuilder = $this->getQueryBuilder(Driver::PGSQL);

        $table = "foo";
        $columns = ["a", "b", "c"];
        $values = [42, 17, 33];
        $returning = "a";

        $segment = $queryBuilder->buildInsert($table, $columns, $values, $returning);

        $expectedQuery = 'INSERT INTO "foo" ("a", "b", "c") VALUES (?, ?, ?) RETURNING "a"';

        $this->assertSame($expectedQuery, $segment->query());
        $this->assertSame($values, $segment->args());
    }

    public function testBuildUpdate()
    {
        $queryBuilder = $this->getQueryBuilder();

        $table = "foo";
        $columns = ["a", "b", "c"];
        $values = [42, 17, 33];
        $filter = Filter::_and(
            Filter::col("xx", "=", "yy"),
            Filter::col("yy", "not null"),
            Filter::_or(
                Filter::col("zz", "between", [1, 2]),
                Filter::raw("max(?) > 0", [100])
            )
        );

        $segment = $queryBuilder->buildUpdate($table, $columns, $values, $filter);

        $expectedQuery = 'UPDATE "foo" SET "a" = ?, "b" = ?, "c" = ? ' .
            'WHERE ("xx" = ? AND "yy" IS NOT NULL AND ("zz" BETWEEN ? AND ? OR max(?) > 0))';

        $expectedArgs = [42, 17, 33, 'yy', 1, 2, 100];

        $this->assertSame($expectedQuery, $segment->query());
        $this->assertSame($expectedArgs, $segment->args());
    }

    public function testBuildDelete()
    {
        $queryBuilder = $this->getQueryBuilder();

        $table = "foo";
        $filter = Filter::_and(
            Filter::col("xx", "=", "yy"),
            Filter::col("yy", "not null"),
            Filter::_or(
                Filter::col("zz", "between", [1, 2]),
                Filter::raw("max(?) > 0", [100])
            )
        );

        $segment = $queryBuilder->buildDelete($table, $filter);

        $expectedQuery = 'DELETE FROM "foo" ' .
            'WHERE ("xx" = ? AND "yy" IS NOT NULL AND ("zz" BETWEEN ? AND ? OR max(?) > 0))';

        $expectedArgs = ['yy', 1, 2, 100];

        $this->assertSame($expectedQuery, $segment->query());
        $this->assertSame($expectedArgs, $segment->args());
    }
}
