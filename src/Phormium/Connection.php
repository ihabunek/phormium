<?php

namespace Phormium;

use PDO;
use PDOStatement;

/**
 * Wrapper for a PDO connection, which enables direct SQL executions and access
 * to the underlying PDO connection object.
 */
class Connection
{
    /** Name of the connection. */
    private $name;

    /** The wrapped PDO connection */
    private $pdo;

    /** The driver name extracted from the PDO connection. */
    private $driver;

    /** Flag to determine if the connection is currently in a transaction. */
    private $inTransaction = false;

    /**
     * Constructs a new wrapper with the given PDO connection
     *
     * @param string $name Unique connection name
     * @param PDO $pdo
     */
    public function __construct($name, PDO $pdo)
    {
        $this->name = $name;
        $this->pdo = $pdo;
        $this->driver = $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
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
    public function preparedQuery($query, array $arguments = array(), $fetchStyle = PDO::FETCH_ASSOC, $class = null)
    {
        DB::getConnection($this->name); // Handles transactions

        Event::emit(Event::QUERY_STARTED, array($query, $arguments, $this));

        $stmt = $this->pdoPrepare($query, $arguments);
        $this->pdoExecute($query, $arguments, $stmt);
        $data = $this->pdoFetch($query, $arguments, $stmt, $fetchStyle, $class);

        Event::emit(Event::QUERY_COMPLETED, array($query, $arguments, $this, $data));

        return $data;
    }

    /**
     * Executes a query without preparing the statement. Fetches and returns the
     * resulting data.
     *
     * If queries are repeated it's often the better to use preparedQuery()
     * from performance perspective.
     *
     * @param string    $query      The SQL query to execute.
     * @param integer   $fetchStyle One of PDO::FETCH_* constants.
     * @param string    $class      Specifies that the fetch method shall return a new instance of the
     *                              requested class, mapping the columns to named properties in the class
     *
     * @return array                The resulting data.
     */
    public function query($query, $fetchStyle = PDO::FETCH_ASSOC, $class = null)
    {
        DB::getConnection($this->name); // Handles transactions

        $arguments = array();

        Event::emit(Event::QUERY_STARTED, array($query, $arguments, $this));

        $stmt = $this->pdoQuery($query, $arguments);
        $data = $this->pdoFetch($query, $arguments, $stmt, $fetchStyle, $class);

        Event::emit(Event::QUERY_COMPLETED, array($query, $arguments, $this, $data));

        return $data;
    }

    /**
     * Executes a statement and returns the number of affected rows.
     * The method is useful for updates or deletes, which do
     * not return anything.
     *
     * @param string $query The SQL query to execute.
     *
     * @return integer Number of rows affected by the query.
     */
    public function execute($query)
    {
        DB::getConnection($this->name); // Handles transactions

        $arguments = array();

        Event::emit(Event::QUERY_STARTED, array($query, $arguments, $this));

        $numRows = $this->pdoExec($query);

        Event::emit(Event::QUERY_COMPLETED, array($query, $arguments, $this, null));

        return $numRows;
    }

    /**
     * Prepares, then executes a statement and returns the number of affected
     * rows.
     *
     * The method is useful for updates or deletes, which do
     * not return anything.
     *
     * @param string    $query      The SQL query to execute.
     * @param array     $arguments  The arguments used to substitute params.
     *
     * @return integer              Number of rows affected by the query.
     */
    public function preparedExecute($query, $arguments = array())
    {
        DB::getConnection($this->name); // Handles transactions

        Event::emit(Event::QUERY_STARTED, array($query, $arguments, $this));

        $stmt = $this->pdoPrepare($query, $arguments);
        $this->pdoExecute($query, $arguments, $stmt);

        Event::emit(Event::QUERY_COMPLETED, array($query, $arguments, $this, null));

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
        return $this->driver;
    }

    /** Calls BEGIN on the underlying PDO connection */
    public function beginTransaction()
    {
        Event::emit(Event::TRANSACTION_BEGIN, array($this));
        $this->pdo->beginTransaction();

        $this->inTransaction = true;
    }

    /** Calls COMMIT on the underlying PDO connection */
    public function commit()
    {
        Event::emit(Event::TRANSACTION_COMMIT, array($this));
        $this->pdo->commit();

        $this->inTransaction = false;
    }

    /** Calls ROLLBACK on the underlying PDO connection */
    public function rollback()
    {
        Event::emit(Event::TRANSACTION_ROLLBACK, array($this));
        $this->pdo->rollback();

        $this->inTransaction = false;
    }

    /** Returns true if the connection is in a transaction. */
    public function inTransaction()
    {
        return $this->inTransaction;
    }

    private function pdoPrepare($query, $arguments)
    {
        Event::emit(Event::QUERY_PREPARING, array($query, $arguments, $this));

        try {
            $stmt = $this->pdo->prepare($query);
        } catch (\Exception $ex) {
            Event::emit(Event::QUERY_ERROR, array($query, $arguments, $this, $ex));
            throw $ex;
        }

        Event::emit(Event::QUERY_PREPARED, array($query, $arguments, $this));

        return $stmt;
    }

    private function pdoExec($query)
    {
        $arguments = array();

        Event::emit(Event::QUERY_EXECUTING, array($query, $arguments, $this));

        try {
            $this->pdo->exec($query);
        } catch (\Exception $ex) {
            Event::emit(Event::QUERY_ERROR, array($query, $arguments, $this, $ex));
            throw $ex;
        }

        Event::emit(Event::QUERY_EXECUTED, array($query, $arguments, $this));
    }

    private function pdoExecute($query, $arguments, PDOStatement $stmt)
    {
        Event::emit(Event::QUERY_EXECUTING, array($query, $arguments, $this));

        try {
            $stmt->execute($arguments);
        } catch (\Exception $ex) {
            Event::emit(Event::QUERY_ERROR, array($query, $arguments, $this, $ex));
            throw $ex;
        }

        Event::emit(Event::QUERY_EXECUTED, array($query, $arguments, $this));
    }

    private function pdoQuery($query, $arguments)
    {
        Event::emit(Event::QUERY_EXECUTING, array($query, $arguments, $this));

        try {
            $stmt = $this->pdo->query($query);
        } catch (\Exception $ex) {
            Event::emit(Event::QUERY_ERROR, array($query, $arguments, $this, $ex));
            throw $ex;
        }

        Event::emit(Event::QUERY_EXECUTED, array($query, $arguments, $this));

        return $stmt;
    }

    /** Fetches all resulting records from a PDO statement. */
    private function pdoFetch($query, $arguments, PDOStatement $stmt, $fetchStyle, $class)
    {
        Event::emit(Event::QUERY_FETCHING, array($query, $arguments, $this));

        $fetchIntoClass = $fetchStyle === PDO::FETCH_CLASS && isset($class);

        try {
            // For Informix use fetch() in a loop instead of fetchAll(), because
            // the latter method has problems with pdo_informix. If a record is
            // locked, fetchAll() will only return records upto the locked
            // record, without raising an error. Fetch, on the other hand will
            // produce an error.
            if ($this->getDriver() == 'informix') {
                $data = array();
                if ($fetchIntoClass) {
                    $stmt->setFetchMode(PDO::FETCH_CLASS, $class);
                }
                while ($row = $stmt->fetch($fetchStyle)) {
                    $data[] = $row;
                }
            } else {
                if ($fetchIntoClass) {
                    $data = $stmt->fetchAll($fetchStyle, $class);
                } else {
                    $data = $stmt->fetchAll($fetchStyle);
                }
            }
        } catch (\Exception $ex) {
            Event::emit(Event::QUERY_ERROR, array($query, $arguments, $this, $ex));
            throw $ex;
        }

        Event::emit(Event::QUERY_FETCHED, array($query, $arguments, $this));

        return $data;
    }
}
