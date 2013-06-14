<?php

namespace Phormium;

/**
 * A passthrough class for logging. Requires Apache log4php.
 */
class Log
{
    private static $logger;

    public static function getLogger()
    {
        if (!isset(self::$logger)) {
            self::$logger = self::createLogger();
        }
        return self::$logger;
    }

    public static function trace($msg)
    {
        self::getLogger()->trace($msg);
    }

    public static function debug($msg)
    {
        self::getLogger()->debug($msg);
    }

    public static function info($msg)
    {
        self::getLogger()->info($msg);
    }

    public static function warn($msg)
    {
        self::getLogger()->warn($msg);
    }

    public static function error($msg)
    {
        self::getLogger()->error($msg);
    }

    public static function fatal($msg)
    {
        self::getLogger()->fatal($msg);
    }

    protected static function createLogger()
    {
        if (Config::isLoggingEnabled()) {
            if (class_exists('\Logger') && method_exists('\Logger', 'getLogger')) {
                return \Logger::getLogger("Phormium");
            } else {
                trigger_error("Phormium: Apache log4php not found. Logging is disabled.");
            }
        }

        return new NullLogger();
    }
}
