<?php

namespace Phormium\Filter;

use Phormium\Exception\InvalidQueryException;

/**
 * A filter which consists of several Filter objects which are joined by an
 * AND or OR operation.
 */
class CompositeFilter extends Filter
{
    const OP_AND = "AND";
    const OP_OR = "OR";

    /** Array of Filter objects. */
    private $filters = [];

    /** The operation to use to join $filters. */
    private $operation;

    public function __construct($operation, array $filters = [])
    {
        $operations = [self::OP_AND, self::OP_OR];
        if (!in_array($operation, $operations)) {
            $operations = implode(', ', $operations);
            throw new InvalidQueryException("Invalid composite filter operation [$operation]. Expected one of: $operations");
        }

        foreach ($filters as &$filter) {
            if (is_array($filter)) {
                $filter = ColumnFilter::fromArray($filter);
            }

            if (!($filter instanceof Filter)) {
                $type = gettype($filter);
                throw new InvalidQueryException("CompositeFilter requires an array of Filter objects as second argument, got [$type].");
            }
        }

        $this->operation = $operation;
        $this->filters = $filters;
    }

    /**
     * Returns a new instance of CompositeFilter with the given filter added to
     * existing $filters.
     *
     * Does not mutate the object.
     *
     * @param  Filter $filter The filter to add.
     *
     * @return CompositeFilter
     */
    public function withAdded(Filter $filter)
    {
        $operation = $this->operation();
        $filters = $this->filters();
        $filters[] = $filter;

        return new CompositeFilter($operation, $filters);
    }

    public function filters()
    {
        return $this->filters;
    }

    public function operation()
    {
        return $this->operation;
    }
}
