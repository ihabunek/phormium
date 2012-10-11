<?php

namespace Phormium;

/**
 * Handles database connections.
 */
class DB
{
    /** Specifies that the fetch method should return each row as an array. */
    const FETCH_ARRAY = 1;

    /** Specifies that the fetch method should return each row as an object. */
    const FETCH_OBJECT = 2;

    /** Specifies that the fetch method should return each row as a json encoded object. */
    const FETCH_JSON = 3;

    /** The loaded configuration. */
    private static $config;

    /**
     * An array of established database connections - {@link Connection}
     * objects.
     */
    private static $connections;

    /**
     * Human readable JSON error descriptions.
     * Literals are used instead of JSON_ERROR_* constants to have backward
     * compatibility.
     */
    private static $jsonErrors = array (
        1 => 'JSON_ERROR_DEPTH - Maximum stack depth exceeded',
        2 => 'JSON_ERROR_STATE_MISMATCH - Underflow or the modes mismatch',
        3 => 'JSON_ERROR_CTRL_CHAR - Unexpected control character found',
        4 => 'JSON_ERROR_SYNTAX - Syntax error, malformed JSON',
        5 => 'JSON_ERROR_UTF8 - Malformed UTF-8 characters, possibly incorrectly encoded',
    );

    /**
     * Loads connection setttings from the provided config file.
     * @param string $env Path to the JSON encoded configuration file.
     */
    public static function configure($path)
    {
        if (!file_exists($path)) {
            throw new \Exception("Config file not found at [$path].");
        }

        $json = file_get_contents($path);
        if ($json === false) {
            throw new \Exception("Error loading config path from [$path].");
        }

        // Decode json to array
        $config = json_decode($json, true);

        $errorCode = json_last_error();
        if ($errorCode !== JSON_ERROR_NONE) {
            $errorText = isset(self::$jsonErrors[$errorCode]) ?
                self::$jsonErrors[$errorCode] : "Unknown error code [$errorCode]";
            throw new \Exception("Failed parsing json config file: $errorText");
        }

        self::$config = $config;
    }

    /**
     * Returns a Connection object for the given connection name.
     * @param string $name Connection name.
     * @return Connection
     */
    public static function getConnection($name)
    {
        if (isset(self::$connections[$name])) {
            return self::$connections[$name];
        }

        if (!isset(self::$config[$name])) {
            throw new \Exception("No configuration defined for connection [$name].");
        }

        $config = self::$config[$name];
        self::$connections[$name] = new Connection($name, $config);

        return self::$connections[$name];
    }
}
