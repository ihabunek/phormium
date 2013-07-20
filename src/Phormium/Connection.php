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
        Log::debug("Preparing query: $query");
        $stmt = $this->pdo->prepare($query);

        if ($fetchStyle === PDO::FETCH_CLASS && isset($class)) {
            $stmt->setFetchMode(PDO::FETCH_CLASS, $class);
        }

        $this->logExecute($arguments);
        $stmt->execute($arguments);

        return $this->fetchAll($stmt, $fetchStyle);
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
        Log::debug("Executing query: $query");
        $stmt = $this->pdo->query($query);

        if ($fetchStyle === PDO::FETCH_CLASS && isset($class)) {
            $stmt->setFetchMode(PDO::FETCH_CLASS, $class);
        }

        return $this->fetchAll($stmt, $fetchStyle);
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
        Log::debug("Executing query: $query");
        return $this->pdo->exec($query);
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
        Log::debug("Preparing query: $query");
        $stmt = $this->pdo->prepare($query);

        $this->logExecute($arguments);
        $stmt->execute($arguments);
        return $stmt->rowCount();
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

    /** Logs the execute arguments if logging is enabled. */
    public function logExecute($args)
    {
        if (Config::isLoggingEnabled()) {
            foreach ($args as &$arg) {
                if ($arg === null) {
                    $arg = "NULL";
                } elseif (is_string($arg)) {
                    $arg = '"' . $arg . '"';
                }
            }

            if (empty($args)) {
                Log::debug("Executing query with no args.");
            } else {
                Log::debug("Executing query with args: " . implode(', ', $args));
            }
        }
    }
}
