<?php

namespace Phormium;

use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Config\Exception\FileLoaderLoadException;
use Symfony\Component\Config\Loader\DelegatingLoader;
use Symfony\Component\Config\Loader\LoaderResolver;

use Evenement\EventEmitter;

/**
 * This is an utterly horrible class, has global state, statics, even a
 * singleton, you should probably not use it.
 */
class Phormium
{
    private static $instance;

    public static function instance()
    {
        if (!isset(self::$instance)) {
            throw new \Exception("Configure Phormium before using.");
        }

        return self::$instance;
    }

    public static function db()
    {
        return self::instance()->getDatabase();
    }

    public static function configure($config)
    {
        self::reset();

        self::$instance = new self($config);
    }

    public static function isConfigured()
    {
        return isset(self::$instance);
    }

    public static function emitter()
    {
        return self::instance()->getEventEmitter();
    }

    public static function reset()
    {
        if (isset(self::$instance)) {
            self::db()->disconnectAll();
        }

        self::$instance = null;
    }

    // -- Dynamics -------------------------------------------------------------

    /**
     * Holds the parsed configuration.
     *
     * @var array
     */
    private $config;

    /**
     * Database connection handler.
     *
     * @var Phormium\Database
     */
    private $database;

    /**
     * The event emitter.
     *
     * @var Evenement\EventEmitter
     */
    private $emitter;

    public function __construct($config)
    {
        $this->loadConfiguration($config);

        $this->database = new Database($this->config['databases']);

        $this->emitter = new EventEmitter();
    }

    private function loadConfiguration($config)
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
        $configuration = new Configuration();

        $this->config = $processor->processConfiguration(
            $configuration,
            array($config)
        );
    }

    // -- Accessors ------------------------------------------------------------

    public function getEmitter()
    {
        return $this->emitter;
    }

    public function getDatabase()
    {
        return $this->database;
    }
}
