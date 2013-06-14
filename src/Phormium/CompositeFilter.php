<?php

namespace Phormium;

/**
 * A filter which consists of several Filter objects which are joined by an
 * AND or OR operation.
 */
class CompositeFilter extends Filter
{
    const OP_AND = "AND";
    const OP_OR = "OR";

    private $operations = array(
        self::OP_AND,
        self::OP_OR,
    );

    /** Array of Filter objects. */
    private $filters = array();

    /** The operation to use to join $filters. */
    private $operation;

    public function __construct($operation, array $filters = array())
    {
        if (!in_array($operation, $this->operations)) {
            $operations = implode(', ', $this->operations);
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

    public function render()
    {
        if (empty($this->filters)) {
            throw new \Exception("Canot render composite filter. No filters defined.");
        }

        $where = array();
        $args = array();

        foreach ($this->filters as $filter) {
            list($w, $a) = $filter->render();
            $args = array_merge($args, $a);
            $where[] = $w;
        }

        $separator = " " . $this->operation . " ";
        $where = "(" . implode($separator, $where) . ")";
        return array($where, $args);
    }

    public function getFilters()
    {
        return $this->filters;
    }
}
