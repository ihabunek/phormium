<?php

namespace Phormium\Filter;

/**
 * A filter which does not tie to a specific column like ColumnFilter. The user
 * may specify any SQL code which will be added as a part of the WHERE clause.
 */
class RawFilter extends Filter
{
    public $condition;

    public $arguments;

    public function __construct($condition, array $arguments = array())
    {
        $this->condition = $condition;
        $this->arguments = $arguments;
    }

    public function render()
    {
        return array(
            $this->condition,
            $this->arguments
        );
    }
}
