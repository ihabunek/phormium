<?php

namespace Phormium;

use \PDO;

/**
 * Handles database connections.
 */
class Database
{
    /** An array of established database connections. */
    private $connections = array();

    /** Set to true when a global transaction has been triggered. */
    private $beginTriggered = false;

    /** Array of database configuration options. */
    private $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * Returns a PDO connection for the given database name. If the connection
     * does not exist, it is established.
     *
     * @param string $name Connection name.
     * @return Connection
     */
    public function getConnection($name)
    {
        if (!isset($this->connections[$name])) {
            $this->connections[$name] = $this->newConnection($name);
        }

        $connection = $this->connections[$name];

        $this->handleTransaction($connection);

        return $connection;
    }

    /**
     * Starts a transaction on a connection if the global transaction is
     * triggered, but the given connection is not in transaction.
     */
    public function handleTransaction(Connection $connection)
    {
        if ($this->beginTriggered && !$connection->inTransaction()) {
            $connection->beginTransaction();
        }
    }

    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Returns the configuration array for the given connection, or throws an
     * exception if there is no such connection.
     *
     * @param  string $name Connection name.
     *
     * @return array An array with "dsn", "username" and "password", the latter
     *               two are optional.
     */
    public function getConnectionConfig($name)
    {
        if (!isset($this->config[$name])) {
            throw new \Exception("Database \"$name\" is not configured.");
        }

        return $this->config[$name];
    }

    public function setConnectionConfig($name, $dsn, $username = null, $password = null)
    {
        $this->config[$name] = array(
            'dsn' => $dsn,
            'username' => $username,
            'password' => $password
        );
    }

    /**
     * Checks whether a connection is connected (a PDO object exists).
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

    /** Connection factory */
    private function newConnection($name)
    {
        // Fetch database configuration
        $db = $this->getConnectionConfig($name);

        // Establish a connection
        $pdo = new PDO($db['dsn'], $db['username'], $db['password']);

        // Force lower case column names
        $pdo->setAttribute(PDO::ATTR_CASE, PDO::CASE_LOWER);

        // Force an exception to be thrown on error
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        return new Connection($name, $pdo, $this);
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

        $this->connections = array();
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
        foreach($this->connections as $name => $connection) {
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
        foreach($this->connections as $name => $connection) {
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
    public function transaction($callback)
    {
        if (!is_callable($callback)) {
            throw new \Exception("Given argument is not callable.");
        }

        $this->begin();

        try {
            $callback();
        } catch (\Exception $ex) {
            $this->rollback();
            throw new \Exception("Transaction failed. Rolled back.", 0, $ex);
        }

        $this->commit();
    }
}
