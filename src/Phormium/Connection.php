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
     * Fetches and returns the resulting data.
     *
     * @param string $query The SQL query to execute, may contain named params.
     * @param array $arguments The arguments used to substitute params.
     * @param integer $fetchType One of PDO::FETCH_* constants.
     * @param string $class When using PDO::FETCH_CLASS, class to fetch into.
     * @return array The resulting data.
     */
    public function preparedQuery($query, $arguments = null, $fetchType = PDO::FETCH_ASSOC, $class = null)
    {
        $stmt = $this->pdo->prepare($query);

        if ($fetchType === PDO::FETCH_CLASS && isset($class)) {
            $stmt->setFetchMode(PDO::FETCH_CLASS, $class);
        }

        $stmt->execute($arguments);

        $rc = $stmt->rowCount();
        Log::debug("Finished prepared query execution. Row count: $rc.");

        return $stmt->fetchAll();
    }

    /**
     * Executes a query without preparing the statement. Fetches and returns the
     * resulting data.
     *
     * If queries are repeated it's often the better to use preparedQuery()
     * from performance perspective.
     *
     * @param string $query the SQL query to execute.
     * @param integer $fetchStyle One of PDO::FETCH_* constants.
     * @return array The resulting data.
     */
    public function query($query, $fetchStyle = PDO::FETCH_ASSOC)
    {
        $stmt = $this->pdo->query($query);

        $rc = $stmt->rowCount();
        Log::debug("Finished query execution. Row count: $rc.");

        return $stmt->fetchAll($fetchStyle);
    }

    /**
     * Executed a prepared statement which do not have
     * return values, like INSERT or DELETE
     *
     * @param $query the query to execute
     * @param null $arguments
     * @return bool
     */
    public function preparedExecute($query, $arguments = null)
    {
        $stmt = $this->pdoConnection->prepare($query);
        return $stmt->execute($arguments);
    }

    /**
     * Executes a statement and returns the number of affected rows.
     * The method is useful for updates or deletes, which do
     * not return anything.
     *
     * @param $query
     * @return int
     */
    public function execute($query)
    {
        $affectedRows = $this->pdo->exec($query);
        Log::debug("Executed query. Affected rows: $affectedRows.");
        return $affectedRows;
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
}
