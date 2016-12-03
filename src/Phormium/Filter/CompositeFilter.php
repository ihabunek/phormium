<?php

namespace Phormium\Filter;

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
            throw new \Exception("Invalid composite filter operation [$operation]. Expected one of: $operations");
        }

        $this->operation = $operation;

        foreach ($filters as $filter) {
            if (is_array($filter)) {
                $filter = ColumnFilter::fromArray($filter);
            }

            $this->add($filter);
        }
    }

    public function add(Filter $filter)
    {
        $this->filters[] = $filter;
    }

    public function getFilters()
    {
        return $this->filters;
    }

    public function getOperation()
    {
        return $this->operation;
    }
}
