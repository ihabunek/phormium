<?php

namespace Phormium;

use PDO;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Processor;

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
            throw new \Exception("Database \"$name\" not defined.");
        }

        return self::$config['databases'][$name];
    }

    public static function addDatabase($name, $dsn, $username = null, $password = null)
    {
        self::$config['databases'][$name] = compact('dsn', 'username', 'password');
    }

    /** Load configuration from array or file. */
    public static function load($config)
    {
        if (is_string($config)) {
            $config = self::loadFile($config);
        }

        $processor = new Processor();
        $tree = self::getConfigTreeBuilder()->buildTree();
        self::$config = $processor->process($tree, array($config));
    }

    private static function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();

        $rootNode = $treeBuilder->root('phormium')
            ->children()
                ->arrayNode('databases')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('dsn')
                                ->cannotBeEmpty()
                            ->end()
                            ->scalarNode('username')
                                ->defaultNull()
                            ->end()
                            ->scalarNode('password')
                                ->defaultNull()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }

    /**
     * Loads and decodes a config file. Throws an exception on failure.
     */
    private static function loadFile($path)
    {
        if (!file_exists($path)) {
            throw new \Exception("Config file not found at \"$path\".");
        }

        $data = file_get_contents($path);
        if ($data === false) {
            throw new \Exception("Error loading config path from \"$path\".");
        }

        $ext = pathinfo($path, PATHINFO_EXTENSION);

        switch (strtolower($ext)) {
            case "json":
                $config = self::parseJSON($data);
                break;
            default:
                throw new \Exception("Unknown config file extension \"$ext\".");
        }

        return $config;
    }

    /**
     * Parses a JSON string and returns it as an array.
     * Throws an exception on failure.
     */
    private static function parseJSON($data)
    {
        $config = json_decode($data, true);

        $errorCode = json_last_error();
        if ($errorCode !== JSON_ERROR_NONE) {
            // Introduced in PHP 5.5
            if (function_exists('json_last_error_msg')) {
                $msg = json_last_error_msg();
            } else {
                // @codeCoverageIgnoreStart Unreachanble code for PHP >= 5.5
                $msg = "Error code \"$errorCode\".";
                // @codeCoverageIgnoreEnd
            }

            throw new \Exception("Failed parsing JSON configuration: $msg");
        }

        return $config;
    }
}
