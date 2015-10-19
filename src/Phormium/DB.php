<?php

namespace Phormium;

use \PDO;

/**
 * Legacy database handler class.
 *
 * @deprecated 0.9.0 Will be removed in 1.0.0
 */
class DB
{
    public static function configure($config)
    {
        self::deprecationNotice(__METHOD__, "Orm::configure()");
        return Orm::configure($config);
    }

    public static function getConnection($name)
    {
        self::deprecationNotice(__METHOD__, "Orm::database()->getConnection()");
        return Orm::database()->getConnection($name);
    }

    public static function isConnected($name)
    {
        self::deprecationNotice(__METHOD__, "Orm::database()->isConnected()");
        return Orm::database()->isConnected($name);
    }

    public static function setConnection($name, Connection $connection)
    {
        self::deprecationNotice(__METHOD__, "Orm::database()->setConnection()");
        return Orm::database()->setConnection($name, $connection);
    }

    public static function disconnect($name)
    {
        self::deprecationNotice(__METHOD__, "Orm::database()->disconnect()");
        return Orm::database()->disconnect($name);
    }

    public static function disconnectAll()
    {
        self::deprecationNotice(__METHOD__, "Orm::database()->disconnectAll()");
        return Orm::database()->disconnectAll();
    }

    public static function begin()
    {
        self::deprecationNotice(__METHOD__, "Orm::begin()");
        return Orm::begin();
    }

    public static function commit()
    {
        self::deprecationNotice(__METHOD__, "Orm::commit()");
        return Orm::commit();
    }

    public static function rollback()
    {
        self::deprecationNotice(__METHOD__, "Orm::rollback()");
        return Orm::rollback();
    }

    public static function transaction(callback $callback)
    {
        self::deprecationNotice(__METHOD__, "Orm::commit()");
        return Orm::transaction($callback);
    }

    private static function deprecationNotice($method, $new)
    {
        $msg = "Method $method is deprecated and will be removed.";
        $msg .= " Please use $new instead.";
        trigger_error($msg, E_USER_WARNING);
    }
}
