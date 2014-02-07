<?php

namespace Phormium;

/**
 * Simple event emitter/listener implementation.
 *
 * Code heavily based on Evenement by Igor Wiedler.
 * https://github.com/igorw/evenement
 */
class Event
{
    private static $listeners = array();

    public static function on($event, $listener) {
        if (!isset(self::$listeners[$event])) {
            self::$listeners[$event] = array();
        }
        self::$listeners[$event][] = $listener;
    }

    public static function once($event, $listener)
    {
        $onceListener = function () use (&$onceListener, $event, $listener) {
            Event::removeListener($event, $onceListener);
            call_user_func_array($listener, func_get_args());
        };

        self::on($event, $onceListener);
    }

    public static function emit($event, array $arguments = array())
    {
        if (isset(self::$listeners[$event])) {
            foreach(self::$listeners[$event] as $listener) {
                call_user_func_array($listener, $arguments);
            }
        }
    }

    public static function listeners($event)
    {
        return isset(self::$listeners[$event]) ? self::$listeners[$event] : array();
    }

    public static function removeListeners($event = null)
    {
        if (isset($event)) {
            self::$listeners[$event] = array();
        } else {
            self::$listeners = array();
        }
    }

    public static function removeListener($event, $listener)
    {
        if (isset(self::$listeners[$event])) {
            $index = array_search($listener, self::$listeners[$event], true);
            if (false !== $index) {
                unset(self::$listeners[$event][$index]);
            }
        }
    }
}
