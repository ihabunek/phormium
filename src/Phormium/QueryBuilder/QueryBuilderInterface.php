<?php

namespace Phormium\QueryBuilder;

use Phormium\Aggregate;
use Phormium\Filter\Filter;


interface QueryBuilderInterface
{
    public function buildSelect($table, array $columns, Filter $filter = null, $limit = null, $offset = null, array $order = null, $distinct = false);

    public function buildSelectAggregate($table, Aggregate $aggregate, Filter $filter = null);

    public function buildInsert($table, array $columns, array $values, $returningColumn);

    public function buildUpdate($table, array $columns, array $values, Filter $filter = null);

    public function buildDelete($table, Filter $filter = null);
}
