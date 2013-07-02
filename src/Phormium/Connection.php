<?php

namespace Phormium;

use PDO;

/**
 * Wrapper for a pdo connection, which enables direct
 * SQL executions and access to the Phormium constructed
 * PDO object.
 */
class Connection
{
    /** the wrapped PDO connection */
    private $pdoConnection;

    /**
     * Constructs a new wrapper with the given PDO connection
     *
     * @param PDO $connection
     */
    public function __construct(PDO $connection)
    {
        $this->pdoConnection = $connection;
    }

    /**
     * Creates and executes a prepared query based
     * on the given SQL and arguments. The result
     * will be completely fetched and returned.
     *
     * @param $query
     * @param null $arguments
     * @param null $fetchType
     * @param null $class
     * @return array
     */
    public function preparedQuery($query, $arguments = null, $fetchType = null, $class = null)
    {
        $stmt = $this->pdoConnection->prepare($query);

        if ($fetchType === PDO::FETCH_CLASS) {
            $stmt->setFetchMode(PDO::FETCH_CLASS, $class);
        }

        $stmt->execute($arguments);

        $rc = $stmt->rowCount();
        Log::debug("Finished prepared query execution. Row count: $rc.");

        return $stmt->fetchAll();
    }

    /**
     * Executed a prepared statement which do not have
     * return values, like INSERT or DELETE
     *
     * @param $query string the query to execute
     * @param null $arguments
     * @return bool
     */
    public function preparedExecute($query, $arguments = null)
    {
        $stmt = $this->pdoConnection->prepare($query);
        return $stmt->execute($arguments);
    }

    /**
     * A query without preparing the statement.
     * If queries are repeated the preparedQuery
     * is most often the better method from performance
     * perspective
     *
     * @param $query the query to execute
     * @param int $fetchStyle
     * @return array
     */
    public function query($query, $fetchStyle = PDO::FETCH_BOTH)
    {
        $stmt = $this->pdoConnection->query($query);

        $rc = $stmt->rowCount();
        Log::debug("Finished query execution. Row count: $rc.");

        return $stmt->fetchAll($fetchStyle);
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
        $affectedRows = $this->pdoConnection->exec($query);
        Log::debug("Executed query. Affected rows: $affectedRows.");
        return $affectedRows;
    }

    /**
     * Returns the underlying PDO connection
     *
     * @return PDO
     */
    public function getPdoConnection()
    {
        return $this->pdoConnection;
    }
}