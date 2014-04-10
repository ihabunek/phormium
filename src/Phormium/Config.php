<?php

namespace Phormium;

use PDO;

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

    public static function addDatabase($name, $dsn, $username = null, $password = null)
    {
        self::$config['databases'][$name] = compact('dsn', 'username', 'password');
    }

    /** Load configuration from array or file. */
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
}
