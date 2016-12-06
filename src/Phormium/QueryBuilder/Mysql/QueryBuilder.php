<?php

namespace Phormium\QueryBuilder\Mysql;

use Phormium\Query\LimitOffset;
use Phormium\Query\QuerySegment;
use Phormium\QueryBuilder\Common\QueryBuilder as CommonQueryBuilder;

class QueryBuilder extends CommonQueryBuilder
{
    /**
     * MySQL doesn't support binding limit/offset params. :/
     */
    public function renderLimitOffset(LimitOffset $limitOffset = null)
    {
        if (!isset($limitOffset)) {
            return new QuerySegment();
        }

        $limit = $limitOffset->limit();
        $offset = $limitOffset->offset();

        $limitSegment = isset($limit) ?
            new QuerySegment("LIMIT $limit") :
            new QuerySegment();

        $offsetSegment = isset($offset) ?
            new QuerySegment("OFFSET $offset") :
            new QuerySegment();

        return $limitSegment->combine($offsetSegment);
    }
}
