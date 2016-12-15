<?php

namespace Phormium;

use Evenement\EventEmitter;
use Phormium\Database\Database;
use Phormium\Exception\OrmException;
use Phormium\QueryBuilder\QueryBuilderInterface;

/**
 * Central class. Global state everywhere. Such is Active Record.
 */
class Orm
{
    /**
     * Container holding application components.
     *
     * @var Container
     */
    private static $container;

    /**
     * Returns the container or throws an exception if not configured.
     *
     * @return Container
     */
    public static function container()
    {
        if (!isset(self::$container)) {
            throw new \Exception("Phormium is not configured.");
        }

        return self::$container;
    }

    public static function configure()
    {
        // Pass arguments to the container constructor
        $reflection = new \ReflectionClass("Phormium\\Container");
        self::$container = $reflection->newInstanceArgs(func_get_args());
    }

    public static function reset()
    {
        self::database()->disconnectAll();
        self::$container = null;
    }

    /**
     * Returns the event emitter.
     *
     * @return EventEmitter
     */
    public static function emitter()
    {
        return self::container()->offsetGet('emitter');
    }

    /**
     * Returns the database manager object.
     *
     * @return Database
     */
    public static function database()
    {
        return self::container()->offsetGet('database');
    }

    /**
     * Starts a global transaction.
     *
     * Shorthand for `Orm::database()->begin()`.
     */
    public static function begin()
    {
        self::database()->begin();
    }

    /**
     * Ends the global transaction by committing changes on all connections.
     *
     * Shorthand for `Orm::database()->commit()`.
     */
    public static function commit()
    {
        self::database()->commit();
    }

    /**
     * Ends the global transaction by rolling back changes on all connections.
     *
     * Shorthand for `Orm::database()->rollback()`.
     */
    public static function rollback()
    {
        self::database()->rollback();
    }

    /**
     * Executes given callback within a transaction. Rolls back if an
     * exception is thrown within the callback.
     */
    public static function transaction(callable $callback)
    {
        return self::database()->transaction($callback);
    }

    /**
     * Returns the QueryBuilder for a given database driver.
     *
     * @return QueryBuilderInterface
     */
    public static function getQueryBuilder($driver)
    {
        $cache = self::$container['query_builder.cache'];
        $factory = self::$container['query_builder.factory'];

        if (!isset($cache[$driver])) {
            $cache[$driver] = $factory->getQueryBuilder($driver);
        }

        return $cache[$driver];
    }

    /**
     * For a given database, returns the driver name.
     *
     * @param  string $database Name of the database.
     * @return string           Driver name.
     */
    private static function getDatabaseDriver($database)
    {
        $databases = self::$container['config']['databases'];

        if (!isset($databases[$database])) {
            throw new OrmException("Database [$database] is not configured.");
        }

        return $databases[$database]['driver'];
    }

    /**
     * Returns the Query class for a given Model class (cached).
     *
     * @param  string $class Model class name.
     * @return Query The corresponding Query class.
     */
    public static function getQuery($class)
    {
        $cache = self::$container['query.cache'];

        if (!isset($cache[$class])) {
            $meta = self::getMeta($class);
            $database = $meta->getDatabase();
            $driver = self::getDatabaseDriver($database);
            $queryBuilder = self::getQueryBuilder($driver);

            $cache[$class] = new Query($meta, $queryBuilder, self::database());
        }

        return $cache[$class];
    }

    public static function getMeta($class)
    {
        // Return from cache if exists
        $cache = self::$container['meta.cache'];
        if (isset($cache[$class])) {
            return $cache[$class];
        }

        // Build the meta object
        $builder = self::$container['meta.builder'];
        $meta = $builder->build($class);

        // Save to cache
        $cache[$class] = $meta;

        return $meta;
    }
}
