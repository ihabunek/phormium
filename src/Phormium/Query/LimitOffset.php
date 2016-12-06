<?php

namespace Phormium\Query;

use Phormium\Exception\OrmException;
use Phormium\Helper\Assert;
use Phormium\Query\ColumnOrder;

/**
 * A value object representing an LIMIT/OFFSET clause.
 */
class LimitOffset
{
    /**
     * Maximum number of rows to fetch.
     *
     * @var integer
     */
    private $limit;

    /**
     * Offset of the first row to fetch.
     *
     * @var integer
     */
    private $offset;

    public function __construct($limit, $offset = null)
    {
        if (isset($limit) && !Assert::isPositiveInteger($limit)) {
            throw new OrmException("\$limit must be a positive integer or null.");
        }

        if (isset($offset) && !Assert::isPositiveInteger($offset)) {
            throw new OrmException("\$offset must be a positive integer or null.");
        }

        if (isset($offset) && !isset($limit)) {
            throw new OrmException("\$offset cannot be given without a \$limit.");
        }

        $this->limit = $limit;
        $this->offset = $offset;
    }

    // -- Accessors ------------------------------------------------------------

    public function limit()
    {
        return $this->limit;
    }

    public function offset()
    {
        return $this->offset;
    }
}
