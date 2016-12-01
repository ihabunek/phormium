<?php

namespace Phormium\QueryBuilder;

use Phormium\Database\Driver;
use Phormium\QueryBuilder\QueryBuilderInterface;

class QueryBuilderFactory
{
    /**
     * Returns a SELECT query builder for a given driver.
     *
     * @param  string $driver
     * @return QueryBuilderInterface
     */
    public function getQueryBuilder($driver)
    {
        $quoterClass = $this->getClass("Quoter", $driver);
        $filterRendererClass = $this->getClass("FilterRenderer", $driver);
        $queryBuilderClass = $this->getClass("QueryBuilder", $driver);

        $quoter = new $quoterClass();
        $filterRenderer = new $filterRendererClass($quoter);
        return new $queryBuilderClass($quoter, $filterRenderer);
    }

    private function getClass($className, $driver)
    {
        $driverNS = ucfirst($driver);

        $driverClass = __NAMESPACE__ . "\\$driverNS\\$className";
        $commonClass = __NAMESPACE__ . "\\Common\\$className";

        return class_exists($driverClass) ? $driverClass : $commonClass;
    }
}
