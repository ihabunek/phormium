<?php

namespace Phormium;

use \PDO;

/**
 * Handles database connections.
 */
class DB
{
    /** An array of established database connections. */
    private static $connections;

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
     * Returns a PDO connection for the given database name.
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

        return self::$connections[$name];
    }

    public static function disconnect($name)
    {
        if (isset(self::$connections[$name])) {
            self::$connections[$name] = null;
        } else {
            trigger_error("Disconnect called for a non-connected connection [$name].", E_USER_WARNING);
        }
    }

    public static function disconnectAll()
    {
        self::$connections = array();
    }
}
