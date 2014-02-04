<?php

namespace Phormium;

use \PDO;

/**
 * Handles the Phormium configuration.
 */
class Config
{
    /** The configuration array. */
    private static $config;

    /** Default configuration */
    private static $defaultConfig = array(
        'databases' => array()
    );


    /** Resets to default config options. */
    public static function reset()
    {
        self::$config = self::$defaultConfig;
    }

    public static function getDatabases()
    {
        return self::$config['databases'];
    }

    public static function getDatabase($name)
    {
        if (!isset(self::$config['databases'][$name])) {
            throw new \Exception("Database [$name] not defined.");
        }
        return self::$config['databases'][$name];
    }

    public static function addDatabase($name, $dsn, $username = null, $password = null)
    {
        self::$config['databases'][$name] = compact('dsn', 'username', 'password');
    }

    public static function statsEnabled()
    {
        return isset(self::$config['stats']['enabled']) &&
            self::$config['stats']['enabled'];
    }

    /** Load configuration from array or file. */
    public static function load($config)
    {
        if (is_string($config)) {
            $config = self::loadFile($config);
        }

        self::validate($config);
        self::$config = $config;
    }

    public static function loadFile($path)
    {
        if (!file_exists($path)) {
            throw new \Exception("Config file not found at [$path].");
        }

        $data = file_get_contents($path);
        if ($data === false) {
            throw new \Exception("Error loading config path from [$path].");
        }

        $ext = pathinfo($path, PATHINFO_EXTENSION);

        switch (strtolower($ext)) {
            case "json":
                $config = self::parseJSON($data);
                break;
            default:
                throw new \Exception("Unknown config file format [$ext].");
        }

        return $config;
    }

    private static function validate($config)
    {
        if (!is_array($config)) {
            throw new \Exception("Configuration is not an array.");
        }

        self::checkArray($config, "databases");

        foreach ($config['databases'] as $name => $connection) {
            if (!is_string($name)) {
                throw new \Exception("Invalid configuration. Array 'databases' should have string keys.");
            }

            self::checkArray($config, array("databases", $name), true);
            self::checkString($config, array("databases", $name, "dsn"), true);
            self::checkString($config, array("databases", $name, "username"));
            self::checkString($config, array("databases", $name, "password"));
        }

        self::checkArray($config, "stats");
        self::checkBoolean($config, array("stats", "enabled"));
        self::checkInteger($config, array("stats", "buffer"));
    }

    private static function checkArray($config, $path, $mandatory = false)
    {
        self::checkElement($config, $path, "array", $mandatory);
    }

    private static function checkBoolean($config, $path, $mandatory = false)
    {
        self::checkElement($config, $path, "boolean", $mandatory);
    }

    private static function checkInteger($config, $path, $mandatory = false)
    {
        self::checkElement($config, $path, "integer", $mandatory);
    }

    private static function checkString($config, $path, $mandatory = false)
    {
        self::checkElement($config, $path, "string", $mandatory);
    }

    private static function checkElement($config, $path, $check, $mandatory = false)
    {
        $path = self::processPath($path);
        $element = self::getElement($config, $path, $mandatory);

        if ($element === null) {
            return;
        }

        if (!self::checkValue($element, $check)) {
            $path = implode(".", $path);
            $type = gettype($element);
            throw new \Exception("Configuration value [$path] should be of type [$check], [$type] given.");
        }
    }

    private static function checkValue($value, $check)
    {
        switch($check) {
            case "boolean":
                return is_bool($value);
            case "array":
                return is_array($value);
            case "string":
                return is_string($value);
            default:
                throw new \Exception("Unknown check [$check].");
        }
    }

    /**
     * Returns a value from the config array corresponding to the given path.
     *
     * For example, given path ['foo', 'bar'], this method will return
     * self::$config['foo']['bar'] if it exists, or null if it doesn't.
     *
     * If $mandatory is set to TRUE, will throw an exception if not found.
     */
    private static function getElement(array $config, array $path, $mandatory = false)
    {
        foreach($path as $element) {
            if (isset($config[$element])) {
                $config = $config[$element];
            } else {
                if ($mandatory) {
                    $path = implode(".", $path);
                    throw new \Exception("Mandatory configuration value [$path] is missing.");
                } else {
                    return null;
                }
            }
        }

        return $config;
    }

    private static function processPath($path)
    {
        if (is_string($path)) {
            $path = array($path);
        }

        if (!is_array($path)) {
            throw new \Exception("Invalid input path given [$path].");
        }

        return $path;
    }

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
     * Parses a JSON string and returns it as an array.
     *
     * Throws an exception on failure.
     */
    private static function parseJSON($data)
    {
        // Decode json to array
        $config = json_decode($data, true);

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
