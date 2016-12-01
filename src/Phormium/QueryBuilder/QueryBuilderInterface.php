<?php

namespace Phormium\QueryBuilder;

use Phormium\Database\Driver;
use Phormium\Filter\Filter;

interface QueryBuilderInterface
{
    public function buildSelect(
        $table,
        array $columns,
        Filter $filter = null,
        $limit = null,
        $offset = null,
        array $order = null
    );
}
