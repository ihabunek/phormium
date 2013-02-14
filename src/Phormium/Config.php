<?php

namespace Phormium;

use \PDO;

/**
 * Handles the Phormium configuration.
 */
class Config
{
    /**
     * Defined databases.
     * @var array
     */
    private static $databases;

    /**
     * Whether to enable logging.
     * @var boolean
     */
    private static $logging = false;

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

    /** Resets to default config options. */
    public static function reset()
    {
        self::$databases = array();
        self::$logging = false;
    }

    public static function getDatabases()
    {
        return self::$databases;
    }

    public static function getDatabase($name)
    {
        if (!isset(self::$databases[$name])) {
            throw new \Exception("Database [$name] not defined.");
        }
        return self::$databases[$name];
    }

    public static function addDatabase($name, $dsn, $username = null, $password = null)
    {
        self::$databases[$name] = compact('dsn', 'username', 'password');
    }

    public static function isLoggingEnabled()
    {
        return self::$logging;
    }

    public static function enableLogging()
    {
        self::$logging = true;
    }

    public static function disableLogging()
    {
        self::$logging = false;
    }

    /** Load configuration from array or file. */
    public static function load($config)
    {
        if (is_string($config)) {
            $config = self::parseJSON($config);
        } elseif (!is_array($config)) {
            throw new \InvalidArgumentException("Configuration should be an array or a path to config file.");
        }

        self::validate($config);

        // Apply the config
        self::$databases = $config['databases'];

        if (isset($config['logging'])) {
            self::$logging = $config['logging'];
        }
    }

    private static function validate($config)
    {
        if (!is_array($config)) {
            throw new \Exception("Configuration is not an array.");
        }

        if (!isset($config['databases'])) {
            throw new \Exception("Invalid configuration. Option 'databases' is mandatory.");
        }
        if (!is_array($config['databases'])) {
            throw new \Exception("Invalid configuration. Option 'databases' must be an array.");
        }

        foreach ($config['databases'] as $name => $connection) {
            if (!is_string($name)) {
                throw new \Exception("Invalid configuration. Option 'databases' should be an associative array.");
            }
            if (!isset($connection['dsn'])) {
                throw new \Exception("Invalid configuration. Missing 'dsn' for database [$name].");
            }
        }

        if (isset($config['logging']) && !is_boolean($config['logging'])) {
            $type = gettype($config['logging']);
            throw new \Exception("Invalid configuration. Option 'logging' should be boolean. Given [$type].");
        }
    }

    /** Parses a JSON configuration file and returns config as an array. */
    private static function parseJSON($path)
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

        // Error handling
        $errorCode = json_last_error();
        if ($errorCode !== JSON_ERROR_NONE) {
            $errorText = isset(self::$jsonErrors[$errorCode]) ?
            self::$jsonErrors[$errorCode] : "Unknown error code [$errorCode]";
            throw new \Exception("Failed parsing json config file: $errorText");
        }

        return $config;
    }
}
