<?php

namespace Phormium\Database;

use Evenement\EventEmitter;
use Phormium\Event;

/**
 * Handles database connections.
 */
class Database
{
    /**
     * The connection factory.
     *
     * @var Phormium\Database\Factory
     */
    private $factory;

    /**
     * Set to true when a global transaction has been triggered.
     *
     * @var boolean
     */
    private $beginTriggered = false;

    /**
     * Database configuration array.
     *
     * @var array
     */
    protected $databases;

    public function __construct(Factory $factory, EventEmitter $emitter)
    {
        $this->factory = $factory;

        // Handle database transactions
        // If a global transaction is triggered, start the database transaction
        // before executing a query on the connection.
        $emitter->on(Event::QUERY_STARTED, function($query, $args, Connection $conn) {
            if ($this->beginTriggered() && !$conn->inTransaction()) {
                $conn->beginTransaction();
            }
        });
    }

    /**
     * Returns a PDO connection for the given database name.
     *
     * If the connection does not exist, it is established.
     *
     * @param  string $name Connection name.
     *
     * @return Connection
     */
    public function getConnection($name)
    {
        if (!isset($this->connections[$name])) {
            $this->connections[$name] = $this->factory->newConnection($name);
        }

        return $this->connections[$name];
    }

    /**
     * Checks whether a connection is connnected (a PDO object exists).
     *
     * @param string $name Connection name.
     *
     * @return boolean
     */
    public function isConnected($name)
    {
        return isset($this->connections[$name]);
    }

    /**
     * Manually set a connection. Useful for mocking.
     *
     * If you want to replace an existing connection call `disconnect()` before
     * `setConnection()`.
     *
     * @param string     $name       Connection name
     * @param Connection $connection The connection object
     *
     * @throws \Exception If the connection with the given name already exists.
     */
    public function setConnection($name, Connection $connection)
    {
        if (isset($this->connections[$name])) {
            throw new \Exception("Connection \"$name\" is already connected. Please disconnect it before calling setConnection().");
        }

        $this->connections[$name] = $connection;
    }

    /**
     * Closes a connection if it's established.
     *
     * @param string $name Connection name
     */
    public function disconnect($name)
    {
        if (!isset($this->connections[$name])) {
            return;
        }

        $connection = $this->connections[$name];

        if ($connection->inTransaction()) {
            $connection->rollback();
        }

        unset($this->connections[$name]);
    }

    /**
     * Closes all active connections. If in global transaction, the transaction
     * is rolled back.
     */
    public function disconnectAll()
    {
        if ($this->beginTriggered) {
            $this->rollback();
        }

        // Disconnect all connections
        $names = array_keys($this->connections);
        foreach ($names as $name) {
            $this->disconnect($name);
        }
    }

    /**
     * Starts the global transaction. This causes any connection which is
     * used to have "BEGIN" executed before any other transactions.
     */
    public function begin()
    {
        if ($this->beginTriggered) {
            throw new \Exception("Already in transaction.");
        }

        $this->beginTriggered = true;
    }

    /**
     * Ends the global transaction by committing changes on all connections.
     */
    public function commit()
    {
        if (!$this->beginTriggered) {
            throw new \Exception("Cannot commit. Not in transaction.");
        }

        // Commit all started transactions
        foreach ($this->connections as $name => $connection) {
            if ($connection->inTransaction()) {
                $connection->commit();
            }
        }

        // End global transaction
        $this->beginTriggered = false;
    }

    /**
     * Ends the global transaction by rolling back changes on all connections.
     */
    public function rollback()
    {
        if (!$this->beginTriggered) {
            throw new \Exception("Cannot roll back. Not in transaction.");
        }

        // Roll back all started transactions
        foreach ($this->connections as $name => $connection) {
            if ($connection->inTransaction()) {
                $connection->rollBack();
            }
        }

        // End global transaction
        $this->beginTriggered = false;
    }

    /**
     * Executes given callback within a transaction. Rolls back if an
     * exception is thrown within the callback.
     */
    public function transaction(callable $callback)
    {
        $this->begin();

        try {
            $callback();
        } catch (\Exception $ex) {
            $this->rollback();
            throw new \Exception("Transaction failed. Rolled back.", 0, $ex);
        }

        $this->commit();
    }

    public function beginTriggered()
    {
        return $this->beginTriggered;
    }
}
