<?php

namespace Phormium\QueryBuilder\Mysql;

use Phormium\Query\QuerySegment;
use Phormium\QueryBuilder\Common\QueryBuilder as CommonQueryBuilder;

class QueryBuilder extends CommonQueryBuilder
{
    /**
     * MySQL doesn't support binding limit/offset params. :/
     */
    public function renderLimitOffset($limit, $offset)
    {
        if (!isset($limit) && !isset($offset)) {
            return new QuerySegment();
        }

        if (isset($offset) && !is_numeric($offset)) {
            throw new \InvalidArgumentException("Invalid offset given [$offset].");
        }

        if (isset($limit) && !is_numeric($limit)) {
            throw new \InvalidArgumentException("Invalid limit given [$limit].");
        }

        // Offset should not be set without a limit
        if (isset($offset) && !isset($limit)) {
            throw new \InvalidArgumentException("Offset given without a limit.");
        }

        $limitSegment = isset($limit) ?
            new QuerySegment("LIMIT $limit") :
            new QuerySegment();

        $offsetSegment = isset($offset) ?
            new QuerySegment("OFFSET $offset") :
            new QuerySegment();

        return $limitSegment->combine($offsetSegment);
    }
}
