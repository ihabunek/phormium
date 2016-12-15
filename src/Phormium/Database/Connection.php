<?php

namespace Phormium\Database;

use Evenement\EventEmitter;
use PDO;
use PDOStatement;
use Phormium\Event;
use Phormium\Query\QuerySegment;

/**
 * Wrapper for a PDO connection, which enables direct SQL executions and access
 * to the underlying PDO connection object.
 */
class Connection
{
    /**
     * The wrapped PDO connection.
     *
     * @var PDO
     */
    private $pdo;

    /**
     * The driver name extracted from the PDO connection DSN.
     *
     * @var string
     */
    private $driver;

    /**
     * Flag to determine if the connection is currently in a transaction.
     *
     * @var boolean
     */
    private $inTransaction = false;

    /**
     * Constructs a new wrapper with the given PDO connection
     *
     * @param PDO          $pdo      Underlying PDO connection.
     * @param EventEmitter $emitter  Event emitter.
     */
    public function __construct(PDO $pdo, EventEmitter $emitter)
    {
        $this->emitter = $emitter;
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
    public function preparedQuery(QuerySegment $segment, $fetchStyle = PDO::FETCH_ASSOC, $class = null)
    {
        $query = $segment->query();
        $arguments = $segment->args();

        $this->emitter->emit(Event::QUERY_STARTED, [$query, $arguments, $this]);

        $stmt = $this->pdoPrepare($query, $arguments);
        $this->pdoExecute($query, $arguments, $stmt);
        $data = $this->pdoFetch($query, $arguments, $stmt, $fetchStyle, $class);

        $this->emitter->emit(Event::QUERY_COMPLETED, [$query, $arguments, $this, $data]);

        return $data;
    }

    public function preparedQueryGenerator(QuerySegment $segment, $class)
    {
        $query = $segment->query();
        $arguments = $segment->args();

        $this->emitter->emit(Event::QUERY_STARTED, [$query, $arguments, $this]);

        $finally = function () use ($query, $arguments) {
            $this->emitter->emit(Event::QUERY_COMPLETED, [$query, $arguments, $this, null]);
        };

        $stmt = $this->pdoPrepare($query, $arguments);
        $this->pdoExecute($query, $arguments, $stmt);

        return $this->pdoFetchGenerator($query, $arguments, $stmt, $class, $finally);
    }

    /**
     * Performs a prepared query and returns only a single column.
     *
     * @param  QuerySegment $segment SQL to execute.
     * @param  string       $column  Column to fetch.
     * @return array
     */
    public function singleColumnPreparedQuery(QuerySegment $segment, $column)
    {
        $query = $segment->query();
        $arguments = $segment->args();

        $this->emitter->emit(Event::QUERY_STARTED, [$query, $arguments, $this]);

        $stmt = $this->pdoPrepare($query, $arguments);
        $this->pdoExecute($query, $arguments, $stmt);
        $data = $this->pdoFetchSingleColumn($query, $arguments, $stmt, $column);

        $this->emitter->emit(Event::QUERY_COMPLETED, [$query, $arguments, $this, $data]);

        return $data;
    }

    /**
     * Executes a query without preparing the statement. Fetches and returns the
     * resulting data.
     *
     * If queries are repeated it's often the better to use preparedQuery()
     * from performance perspective.
     *
     * @param  QuerySegment $segment SQL to execute.
     * @param  integer $fetchStyle One of PDO::FETCH_* constants.
     * @return array The resulting data.
     */
    public function query(QuerySegment $segment, $fetchStyle = PDO::FETCH_ASSOC, $class = null)
    {
        $query = $segment->query();
        $arguments = $segment->args();

        $this->emitter->emit(Event::QUERY_STARTED, [$query, $arguments, $this]);

        $stmt = $this->pdoQuery($query, $arguments);
        $data = $this->pdoFetch($query, $arguments, $stmt, $fetchStyle, $class);

        $this->emitter->emit(Event::QUERY_COMPLETED, [$query, $arguments, $this, $data]);

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
    public function execute(QuerySegment $segment)
    {
        $query = $segment->query();
        $arguments = $segment->args();

        $this->emitter->emit(Event::QUERY_STARTED, [$query, $arguments, $this]);

        $numRows = $this->pdoExec($query);

        $this->emitter->emit(Event::QUERY_COMPLETED, [$query, $arguments, $this, null]);

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
    public function preparedExecute(QuerySegment $segment)
    {
        $query = $segment->query();
        $arguments = $segment->args();

        $this->emitter->emit(Event::QUERY_STARTED, [$query, $arguments, $this]);

        $stmt = $this->pdoPrepare($query, $arguments);
        $this->pdoExecute($query, $arguments, $stmt);

        $this->emitter->emit(Event::QUERY_COMPLETED, [$query, $arguments, $this, null]);

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
        $this->emitter->emit(Event::TRANSACTION_BEGIN, [$this]);
        $this->pdo->beginTransaction();

        $this->inTransaction = true;
    }

    /** Calls COMMIT on the underlying PDO connection */
    public function commit()
    {
        $this->emitter->emit(Event::TRANSACTION_COMMIT, [$this]);
        $this->pdo->commit();

        $this->inTransaction = false;
    }

    /** Calls ROLLBACK on the underlying PDO connection */
    public function rollback()
    {
        $this->emitter->emit(Event::TRANSACTION_ROLLBACK, [$this]);
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
        $this->emitter->emit(Event::QUERY_PREPARING, [$query, $arguments, $this]);

        try {
            $stmt = $this->pdo->prepare($query);
        } catch (\Exception $ex) {
            $this->emitter->emit(Event::QUERY_ERROR, [$query, $arguments, $this, $ex]);
            throw $ex;
        }

        $this->emitter->emit(Event::QUERY_PREPARED, [$query, $arguments, $this]);

        return $stmt;
    }

    private function pdoExec($query)
    {
        $arguments = [];

        $this->emitter->emit(Event::QUERY_EXECUTING, [$query, $arguments, $this]);

        try {
            $numRows = $this->pdo->exec($query);
        } catch (\Exception $ex) {
            $this->emitter->emit(Event::QUERY_ERROR, [$query, $arguments, $this, $ex]);
            throw $ex;
        }

        $this->emitter->emit(Event::QUERY_EXECUTED, [$query, $arguments, $this]);

        return $numRows;
    }

    private function pdoExecute($query, $arguments, PDOStatement $stmt)
    {
        $this->emitter->emit(Event::QUERY_EXECUTING, [$query, $arguments, $this]);

        try {
            $stmt->execute($arguments);
        } catch (\Exception $ex) {
            $this->emitter->emit(Event::QUERY_ERROR, [$query, $arguments, $this, $ex]);
            throw $ex;
        }

        $this->emitter->emit(Event::QUERY_EXECUTED, [$query, $arguments, $this]);
    }

    private function pdoQuery($query, $arguments)
    {
        $this->emitter->emit(Event::QUERY_EXECUTING, [$query, $arguments, $this]);

        try {
            $stmt = $this->pdo->query($query);
        } catch (\Exception $ex) {
            $this->emitter->emit(Event::QUERY_ERROR, [$query, $arguments, $this, $ex]);
            throw $ex;
        }

        $this->emitter->emit(Event::QUERY_EXECUTED, [$query, $arguments, $this]);

        return $stmt;
    }

    /** Fetches all resulting records from a PDO statement. */
    private function pdoFetch($query, $arguments, PDOStatement $stmt, $fetchStyle, $class)
    {
        $this->emitter->emit(Event::QUERY_FETCHING, [$query, $arguments, $this]);

        $fetchIntoClass = $fetchStyle === PDO::FETCH_CLASS && isset($class);

        try {
            // For Informix use fetch() in a loop instead of fetchAll(), because
            // the latter method has problems with pdo_informix. If a record is
            // locked, fetchAll() will only return records upto the locked
            // record, without raising an error. Fetch, on the other hand will
            // produce an error.
            if ($this->getDriver() == 'informix') {
                $data = [];
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
            $this->emitter->emit(Event::QUERY_ERROR, [$query, $arguments, $this, $ex]);
            throw $ex;
        }

        $this->emitter->emit(Event::QUERY_FETCHED, [$query, $arguments, $this]);

        return $data;
    }

    /**
     * Similar to pdoFetch, but returns a generator which yields results
     * instead of returning a fully fetched array.
     *
     * @return Generator
     */
    private function pdoFetchGenerator($query, $arguments, PDOStatement $stmt, $class, callable $finally)
    {
        $this->emitter->emit(Event::QUERY_FETCHING, [$query, $arguments, $this]);

        try {
            $stmt->setFetchMode(PDO::FETCH_CLASS, $class);

            while ($row = $stmt->fetch()) {
                yield $row;
            }
        } catch (\Exception $ex) {
            $this->emitter->emit(Event::QUERY_ERROR, [$query, $arguments, $this, $ex]);
            throw $ex;
        }

        $this->emitter->emit(Event::QUERY_FETCHED, [$query, $arguments, $this]);

        $finally();
    }

    private function pdoFetchSingleColumn($query, $arguments, PDOStatement $stmt, $column)
    {
        $this->emitter->emit(Event::QUERY_FETCHING, [$query, $arguments, $this]);

        try {
            $data = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $data[] = $row[$column];
            }
        } catch (\Exception $ex) {
            $this->emitter->emit(Event::QUERY_ERROR, [$query, $arguments, $this, $ex]);
            throw $ex;
        }

        $this->emitter->emit(Event::QUERY_FETCHED, [$query, $arguments, $this]);

        return $data;
    }
}
