<?php

namespace Phormium;

use PDO;

/**
 * Wrapper for a PDO connection, which enables direct SQL executions and access
 * to the underlying PDO connection object.
 */
class Connection
{
    /** The wrapped PDO connection */
    private $pdo;

    /**
     * Constructs a new wrapper with the given PDO connection
     *
     * @param PDO $pdo
     */
    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Prepares and executes an SQL query using the given SQL and arguments.
     *
     * Fetches and returns the resulting data.
     *
     * @param string $query The SQL query to execute, may contain named params.
     * @param array $arguments The arguments used to substitute params.
     * @param integer $fetchStyle One of PDO::FETCH_* constants.
     * @param string $class When using PDO::FETCH_CLASS, class to fetch into.
     * @return array The resulting data.
     */
    public function preparedQuery($query, $arguments = array(), $fetchStyle = PDO::FETCH_ASSOC, $class = null)
    {
        $statsEnabled = Config::statsEnabled();

        if ($statsEnabled) {
            $time1 = microtime(true);
        }

        $stmt = $this->pdo->prepare($query);

        if ($statsEnabled) {
            $time2 = microtime(true);
        }

        if ($fetchStyle === PDO::FETCH_CLASS && isset($class)) {
            $stmt->setFetchMode(PDO::FETCH_CLASS, $class);
        }

        $stmt->execute($arguments);

        if ($statsEnabled) {
            $time3 = microtime(true);
        }

        $data = $this->fetchAll($stmt, $fetchStyle);

        if ($statsEnabled) {
            $time4 = microtime(true);

            Stats::add(array(
                'query' => $query,
                'arguments' => $arguments,
                'prepare' => $time2 - $time1,
                'execute' => $time3 - $time2,
                'fetch' => $time4 - $time3,
                'total' => $time4 - $time1,
                'numrows' => count($data),
            ));
        }

        return $data;
    }

    /**
     * Executes a query without preparing the statement. Fetches and returns the
     * resulting data.
     *
     * If queries are repeated it's often the better to use preparedQuery()
     * from performance perspective.
     *
     * @param string $query The SQL query to execute.
     * @param integer $fetchStyle One of PDO::FETCH_* constants.
     * @return array The resulting data.
     */
    public function query($query, $fetchStyle = PDO::FETCH_ASSOC, $class = null)
    {
        $statsEnabled = Config::statsEnabled();

        if ($statsEnabled) {
            $time1 = microtime(true);
        }

        $stmt = $this->pdo->query($query);

        if ($statsEnabled) {
            $time2 = microtime(true);
        }

        if ($fetchStyle === PDO::FETCH_CLASS && isset($class)) {
            $stmt->setFetchMode(PDO::FETCH_CLASS, $class);
        }

        $data = $this->fetchAll($stmt, $fetchStyle);

        if ($statsEnabled) {
            $time3 = microtime(true);

            Stats::add(array(
                'query' => $query,
                'arguments' => null,
                'prepare' => null,
                'execute' => $time2 - $time1,
                'fetch' => $time3 - $time2,
                'total' => $time3 - $time1,
                'numrows' => count($data),
            ));
        }

        return $data;
    }

    /**
     * Executes a statement and returns the number of affected rows.
     * The method is useful for updates or deletes, which do
     * not return anything.
     *
     * @param $query The SQL query to execute.
     * @return integer Number of rows affected by the query.
     */
    public function execute($query)
    {
        $statsEnabled = Config::statsEnabled();

        if ($statsEnabled) {
            $time1 = microtime(true);
        }

        $numRows = $this->pdo->exec($query);

        if ($statsEnabled) {
            $time2 = microtime(true);

            Stats::add(array(
                'query' => $query,
                'arguments' => null,
                'prepare' => null,
                'execute' => $time2 - $time1,
                'fetch' => $time3 - $time2,
                'total' => $time3 - $time1,
                'numrows' => $numRows,
            ));
        }

        return $numRows;
    }

    /**
     * Prepares, then executes a statement and returns the number of affected
     * rows.
     *
     * The method is useful for updates or deletes, which do
     * not return anything.
     *
     * @param string $query The SQL query to execute.
     * @param array $arguments The arguments used to substitute params.
     * @return integer Number of rows affected by the query.
     */
    public function preparedExecute($query, $arguments = array())
    {
        $statsEnabled = Config::statsEnabled();

        if ($statsEnabled) {
            $time1 = microtime(true);
        }

        $stmt = $this->pdo->prepare($query);

        if ($statsEnabled) {
            $time2 = microtime(true);
        }

        $stmt->execute($arguments);
        $numRows = $stmt->rowCount();

        if ($statsEnabled) {
            $time3 = microtime(true);

            Stats::add(array(
                'query' => $query,
                'arguments' => $arguments,
                'prepare' => $time2 - $time1,
                'execute' => $time3 - $time2,
                'fetch' => null,
                'total' => $time3 - $time1,
                'numrows' => $numRows,
            ));
        }

        return $numRows;
    }

    /**
     * Returns the underlying PDO connection
     *
     * @return PDO
     */
    public function getPDO()
    {
        return $this->pdo;
    }

    /** Returns the name of the driver for the underlying PDO connection. */
    public function getDriver()
    {
        return $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
    }

    /** Calls BEGIN on the underlying PDO connection */
    public function beginTransaction()
    {
        $this->pdo->beginTransaction();
    }

    /** Calls COMMIT on the underlying PDO connection */
    public function commit()
    {
        $this->pdo->commit();
    }

    /** Calls ROLLBACK on the underlying PDO connection */
    public function rollback()
    {
        $this->pdo->rollback();
    }

    /**
     * Fetches all resulting records from a PDO statement.
     *
     * This method uses fetch() in a loop instead of fetchAll(), because the
     * latter method has problems on Informix: If a record is locked, fetchAll()
     * will only return records upto the locked record, without raising an
     * error. Fetch, on the other hand will produce an error.
     */
    private function fetchAll($stmt, $fetchStyle)
    {
        $data = array();
        while ($row = $stmt->fetch($fetchStyle)) {
            $data[] = $row;
        }
        return $data;
    }
}
