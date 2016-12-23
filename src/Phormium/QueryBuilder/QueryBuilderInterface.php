<?php

namespace Phormium\QueryBuilder;

use Phormium\Filter\Filter;
use Phormium\Query\Aggregate;
use Phormium\Query\LimitOffset;
use Phormium\Query\OrderBy;

interface QueryBuilderInterface
{
    public function buildSelect($table, array $columns, Filter $filter = null, LimitOffset $limitOffset, OrderBy $orderBy = null, $distinct = false);

    public function buildSelectAggregate($table, Aggregate $aggregate, Filter $filter = null);

    public function buildInsert($table, array $columns, array $values, $returningColumn);

    public function buildUpdate($table, array $columns, array $values, Filter $filter = null);

    public function buildDelete($table, Filter $filter = null);
}
