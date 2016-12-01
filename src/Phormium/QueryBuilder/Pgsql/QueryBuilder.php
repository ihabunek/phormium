<?php

namespace Phormium\QueryBuilder\Pgsql;

use Phormium\Query\QuerySegment;
use Phormium\QueryBuilder\Common\QueryBuilder as CommonQueryBuilder;

class QueryBuilder extends CommonQueryBuilder
{
    /** RETURNING clause */
    public function renderInsertReturning($column)
    {
        if (!empty($column)) {
            $column = $this->quoter->quote($column);
            return new QuerySegment("RETURNING $column");
        }

        return new QuerySegment();
    }
}
