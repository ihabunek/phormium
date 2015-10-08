<?php

namespace Phormium;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Config\Exception\FileLoaderLoadException;
use Symfony\Component\Config\Loader\DelegatingLoader;
use Symfony\Component\Config\Loader\LoaderResolver;

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

    public static function addDatabase($name, $dsn, $username = null, $password = null, array $attributes = [])
    {
        $attributes = self::processAttributes($name, $attributes);

        self::$config['databases'][$name] = compact('dsn', 'username', 'password', 'attributes');
    }

    /**
     * Load configuration from array or file.
     *
     * @param array|string $config configuration array or file path
     * @throws \Exception Thrown when the given config is in a unsupported format
     */
    public static function load($config)
    {
        $loaderResolver = new LoaderResolver(array(
            new Config\ArrayConfigLoader(),
            new Config\JsonConfigLoader(),
            new Config\YamlConfigLoader(),
        ));

        $delegatingLoader = new DelegatingLoader($loaderResolver);

        try {
            $config = $delegatingLoader->load($config);
        } catch (FileLoaderLoadException $ex) {
            // Thrown when no loader matches given config
            throw new \Exception("Unsupported configuration format.", 0, $ex);
        }

        $processor = new Processor();
        $tree = self::getConfigTreeBuilder()->buildTree();
        $config = $processor->process($tree, array($config));
        self::$config = self::processConfig($config);
    }

    /** Process and validate a configuration array. */
    private static function processConfig(array $config)
    {
        foreach ($config['databases'] as $name => $dbConfig) {
            $config['databases'][$name] = self::processDbConfig($name, $dbConfig);
        }

        return $config;
    }

    private static function processDbConfig($name, $config)
    {
        $config['attributes'] = self::processAttributes($name, $config['attributes']);

        return $config;
    }

    private static function processAttributes($dbName, $attributes)
    {
        $processed = [];

        foreach ($attributes as $name => $value) {
            try {
                $procName = self::processConstant($name, false);
            } catch (\Exception $ex) {
                throw new \Exception("Invalid attribute \"$name\" specified in configuration for database \"$dbName\".");
            }

            try {
                $procValue = self::processConstant($value, true);
            } catch (\Exception $ex) {
                throw new \Exception("Invalid value \"$value\" given for attribute \"$name\", in configuration for database \"$dbName\".");
            }

            $processed[$procName] = $procValue;
        }

        return $processed;
    }

    private static function processConstant($value, $allowScalar = false)
    {
        // If the value is an integer, assume it's a PDO::* constant value
        // and leave it as-is
        if (is_integer($value)) {
            return $value;
        }

        // If it's a string which starts with "PDO::", try to find the
        // corresponding PDO constant
        if (is_string($value) && substr($value, 0, 5) === 'PDO::' && defined($value)) {
            return constant($value);
        }

        if ($allowScalar && is_scalar($value)) {
            return $value;
        }

        throw new \Exception("Invalid constant value");
    }

    private static function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();

        $rootNode = $treeBuilder->root('phormium');

        $rootNode
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
                            ->arrayNode('attributes')
                                ->useAttributeAsKey('name')
                                ->prototype('scalar')
                                    ->isRequired()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
