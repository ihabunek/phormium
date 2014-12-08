<?php

namespace Phormium;

use \PDO;

/**
 * Handles database connections.
 */
class DB
{
    /** An array of established database connections. */
    private static $connections = array();

    /** Set to true when a global transaction has been triggered. */
    private static $beginTriggered = false;

    /**
     * Configures database definitions.
     *
     * @param string|array $config Either a path to the JSON encoded
     *      configuration file, or the configuration as an array.
     */
    public static function configure($config)
    {
        DB::disconnectAll();
        Config::load($config);
    }

    /**
     * Returns a PDO connection for the given database name. If the connection
     * does not exist, it is established.
     *
     * @param string $name Connection name.
     * @return Connection
     */
    public static function getConnection($name)
    {
        if (!isset(self::$connections[$name])) {
            self::$connections[$name] = self::newConnection($name);
        }

        $connection = self::$connections[$name];

        if (self::$beginTriggered && !$connection->inTransaction()) {
            $connection->beginTransaction();
        }

        return $connection;
    }

    /**
     * Checks whether a connection is connnected (a PDO object exists).
     *
     * @param string $name Connection name.
     *
     * @return boolean
     */
    public static function isConnected($name)
    {
        return isset(self::$connections[$name]);
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
    public static function setConnection($name, Connection $connection)
    {
        if (isset(self::$connections[$name])) {
            throw new \Exception("Connection \"$name\" is already connected. Please disconnect it before calling setConnection().");
        }

        self::$connections[$name] = $connection;
    }

    /** Connection factory */
    private static function newConnection($name)
    {
        // Fetch database configuration
        $db = Config::getDatabase($name);

        // Establish a connection
        $pdo = new PDO($db['dsn'], $db['username'], $db['password']);

        $attributes = $db['attributes'];

        // Don't allow ATTR_ERRORMODE to be changed by the configuration,
        // because Phormium depends on errors throwing exceptions.
        if (isset($attributes[PDO::ATTR_ERRMODE])
            && $attributes[PDO::ATTR_ERRMODE] !== PDO::ERRMODE_EXCEPTION) {
            trigger_error("Phormium: On connection $name, attribute PDO::ATTR_ERRMODE is set to something other than PDO::ERRMODE_EXCEPTION. This is not allowed because Phormium depends on this setting. Skipping attribute definition.", E_USER_WARNING);
        }

        $attributes[PDO::ATTR_ERRMODE] = PDO::ERRMODE_EXCEPTION;


        // Apply the attributes
        foreach ($attributes as $key => $value) {
            if (!$pdo->setAttribute($key, $value)) {
                throw new \Exception("Failed setting attribute \"$key\" to \"$value\"");
            }
        }

        return new Connection($name, $pdo);
    }

    /**
     * Closes a connection if it's established.
     *
     * @param string $name Connection name
     */
    public static function disconnect($name)
    {
        if (!isset(self::$connections[$name])) {
            return;
        }

        $connection = self::$connections[$name];

        if ($connection->inTransaction()) {
            $connection->rollback();
        }

        unset(self::$connections[$name]);
    }

    /**
     * Closes all active connections. If in global transaction, the transaction
     * is rolled back.
     */
    public static function disconnectAll()
    {
        if (self::$beginTriggered) {
            self::rollback();
        }

        self::$connections = array();
    }

    /**
     * Starts the global transaction. This causes any connection which is
     * used to have "BEGIN" executed before any other transactions.
     */
    public static function begin()
    {
        if (self::$beginTriggered) {
            throw new \Exception("Already in transaction.");
        }

        self::$beginTriggered = true;
    }

    /**
     * Ends the global transaction by committing changes on all connections.
     */
    public static function commit()
    {
        if (!self::$beginTriggered) {
            throw new \Exception("Cannot commit. Not in transaction.");
        }

        // Commit all started transactions
        foreach(self::$connections as $name => $connection) {
            if ($connection->inTransaction()) {
                $connection->commit();
            }
        }

        // End global transaction
        self::$beginTriggered = false;
    }

    /**
     * Ends the global transaction by rolling back changes on all connections.
     */
    public static function rollback()
    {
        if (!self::$beginTriggered) {
            throw new \Exception("Cannot roll back. Not in transaction.");
        }

        // Roll back all started transactions
        foreach(self::$connections as $name => $connection) {
            if ($connection->inTransaction()) {
                $connection->rollBack();
            }
        }

        // End global transaction
        self::$beginTriggered = false;
    }

    /**
     * Executes given callback within a transaction. Rolls back if an
     * exception is thrown within the callback.
     */
    public static function transaction($callback)
    {
        if (!is_callable($callback)) {
            throw new \Exception("Given argument is not callable.");
        }

        self::begin();

        try {
            $callback();
        } catch (\Exception $ex) {
            self::rollback();
            throw new \Exception("Transaction failed. Rolled back.", 0, $ex);
        }

        self::commit();
    }
}
