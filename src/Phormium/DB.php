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
     * @return Connection
     */
    public static function getConnection($name)
    {
        if (!isset(self::$connections[$name])) {
            // Fetch database configuration
            $db = Config::getDatabase($name);

            // Establish a connection
            $pdo = new PDO($db['dsn'], $db['username'], $db['password']);

            // Force lower case column names
            $pdo->setAttribute(PDO::ATTR_CASE, PDO::CASE_LOWER);

            // Force an exception to be thrown on error
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            self::$connections[$name] = new Connection($pdo);
        }

        if (self::$beginTriggered && !in_array($name, self::$inTransaction)) {
            self::$connections[$name]->beginTransaction();
            self::$inTransaction[] = $name;
        }

        return self::$connections[$name];
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
        Log::debug("BEGIN global transaction.");

        if (self::$beginTriggered) {
            throw new \Exception("Already in transaction.");
        }

        self::$beginTriggered = true;
    }

    /**
     * Ends the global transaction by committing all changes on all connections.
     */
    public static function commit()
    {
        Log::debug("COMMIT global transaction.");

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
        Log::debug("ROLLBACK global transaction.");

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
