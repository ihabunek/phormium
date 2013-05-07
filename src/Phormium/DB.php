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

    /** Array of connection names for which BEGIN has been executed. */
    private static $inTransaction = array();

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
     * @return PDO
     */
    public static function getConnection($name)
    {
        if (!isset(self::$connections[$name])) {
            // Fetch database configuration
            $db = Config::getDatabase($name);

            // Establish a connection
            $connection = new PDO($db['dsn'], $db['username'], $db['password']);

            // Force lower case column names
            $connection->setAttribute(PDO::ATTR_CASE, PDO::CASE_LOWER);

            // Force an exception to be thrown on error
            $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            self::$connections[$name] = $connection;
        }

        if (self::$beginTriggered && !in_array($name, self::$inTransaction)) {
            self::$connections[$name]->beginTransaction();
            self::$inTransaction[] = $name;
        }

        return self::$connections[$name];
    }

    /**
     * Closes the database connection. If it was in transaction at the moment,
     * rolls back the changes.
     */
    public static function disconnect($name)
    {
        if (isset(self::$inTransaction[$name])) {
            self::$connections[$name]->rollBack();
        }

        if (isset(self::$connections[$name])) {
            self::$connections[$name] = null;
        }
    }

    /**
     * Closes all active connections.
     */
    public static function disconnectAll()
    {
        foreach(self::$connections as $name => $connection) {
            self::disconnect($name);
        }
    }

    /**
     * Starts the global transaction. This causes any connection which is
     * used to have "BEGIN" executed before any other transactions.
     */
    public static function begin()
    {
        if (Config::isLoggingEnabled()) {
            echo date('Y-m-d H:i:s') . " BEGIN global transaction.\n";
        }

        if (self::$beginTriggered) {
            throw new \Exception("Already in transaction.");
        }

        self::$beginTriggered = true;
    }

    /**
     * Ends the global transaction by commiting all changes on all connections.
     */
    public static function commit()
    {
        if (Config::isLoggingEnabled()) {
            echo date('Y-m-d H:i:s') . " COMMIT global transaction.\n";
        }

        if (!self::$beginTriggered) {
            throw new \Exception("Cannot commit. Not in transaction.");
        }

        // Commit all started transactions
        foreach (self::$inTransaction as $name) {
            self::$connections[$name]->commit();
        }

        // End global transaction
        self::$beginTriggered = false;
        self::$inTransaction = array();
    }

    /**
     * Ends the global transaction by rolling back all changes on all
     * connections.
     */
    public static function rollback()
    {
        if (Config::isLoggingEnabled()) {
            echo date('Y-m-d H:i:s') . " ROLLBACK global transaction.\n";
        }

        if (!self::$beginTriggered) {
            throw new \Exception("Cannot roll back. Not in transaction.");
        }

        // Roll back all started transactions
        foreach (self::$inTransaction as $name) {
            self::$connections[$name]->rollBack();
        }

        // End global transaction
        self::$beginTriggered = false;
        self::$inTransaction = array();
    }

    /**
     * Executes given callback within a transaction. Rolls back if an
     * exception is thrown within the callback.
     */
    public static function transaction(callable $callback) {
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
