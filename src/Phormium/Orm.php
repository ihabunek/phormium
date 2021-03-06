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
            throw new OrmException("Phormium is not configured.");
        }

        return self::$container;
    }

    public static function configure(...$args)
    {
        self::$container = new Container(...$args);
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
        $cache = self::container()['query_builder.cache'];
        $factory = self::container()['query_builder.factory'];

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
        $config = self::container()->offsetGet('config');

        if (!isset($config['databases'][$database])) {
            throw new OrmException("Database [$database] is not configured.");
        }

        return $config['databases'][$database]['driver'];
    }

    /**
     * Returns the Query class for a given Model class (cached).
     *
     * @param  string $class Model class name.
     * @return Query The corresponding Query class.
     */
    public static function getQuery($class)
    {
        $cache = self::container()['query.cache'];

        if (!isset($cache[$class])) {
            $meta = self::getMeta($class);
            $database = $meta->getDatabase();
            $driver = self::getDatabaseDriver($database);
            $queryBuilder = self::getQueryBuilder($driver);

            $cache[$class] = new Query($meta, $queryBuilder, self::database());
        }

        return $cache[$class];
    }

    /**
     * Returns the Meta class for a given Model class (cached).
     *
     * @param  string $class Model class name.
     * @return Meta The corresponding Meta class.
     */
    public static function getMeta($class)
    {
        $cache = self::container()['meta.cache'];
        $builder = self::container()['meta.builder'];

        if (!isset($cache[$class])) {
            $cache[$class] = $builder->build($class);
        }

        return $cache[$class];
    }
}
