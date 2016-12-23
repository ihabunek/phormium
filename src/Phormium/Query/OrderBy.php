<?php

namespace Phormium\Query;

use Phormium\Exception\OrmException;
use Phormium\Query\ColumnOrder;

/**
 * A value object representing an ORDER BY clause.
 */
class OrderBy
{
    /**
     * A collection of ColumnOrder objects used to form the clause.
     *
     * @var ColumnOrder[]
     */
    private $orders = [];


    public function __construct(array $orders)
    {
        if (empty($orders)) {
            throw new OrmException("OrderBy needs at least one ColumnOrder element, empty array given.");
        }

        foreach ($orders as $order) {
            if (!($order instanceof ColumnOrder)) {
                $type = gettype($order);
                throw new OrmException("Expected \$orders to be instances of Phormium\\Query\\ColumnOrder. Given [$type].");
            }
        }

        $this->orders = $orders;
    }

    public function orders()
    {
        return $this->orders;
    }

    /**
     * Returns a new instance of OrderBy with the given ColumnOrder added onto
     * the $orders collection.
     *
     * @param  ColumnOrder  $order The order to add.
     *
     * @return OrderBy
     */
    public function withAdded(ColumnOrder $order)
    {
        $orders = $this->orders;
        $orders[] = $order;

        return new OrderBy($orders);
    }
}
