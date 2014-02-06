<?php

namespace Phormium;

use Evenement\EventEmitter;

/**
 * Central class for Phormium.
 *
 * Used for configuring and as an event dispatcher.
 */
class Phormium
{
    private static $emitter;

    private static $configured = false;

    /**
     * Configures database definitions.
     *
     * @param string|array $config Either a path to the JSON encoded
     *      configuration file, or the configuration as an array.
     */
    public static function configure($config)
    {
        DB::disconnectAll();

        Config::load($config);

        self::$configured = true;
    }
}
