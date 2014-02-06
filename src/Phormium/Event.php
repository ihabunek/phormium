<?php

namespace Phormium;

/**
 * Simple event emitter/listener implementation.
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

    public function once($event, $listener)
    {
        $onceListener = function () use (&$onceListener, $event, $listener) {
            $this->removeListener($event, $onceListener);

            call_user_func_array($listener, func_get_args());
        };

        $this->on($event, $onceListener);
    }

    public static function emit($event, array $arguments)
    {
        if (isset(self::$listeners[$event])) {
            foreach(self::$listeners[$event] as $listener) {
                call_user_func_array($listener, $arguments);
            }
        }
    }

    public static function listeners($event = null)
    {
        if (isset($event)) {
            if (!isset(self::$listneers[$event])) {
                self::$listneers[$event] = array();
            }
            return self::$listeners[$event];
        } else {
            return self::$listeners;
        }
    }

    public static function removeListeners($event = null)
    {
        if (isset($event)) {
            self::$listeners[$event] = array();
        } else {
            self::$listeners = array();
        }
    }

    public function removeListener($event, $listener)
    {
        if (isset(self::$listeners[$event])) {
            $index = array_search($listener, self::$listeners[$event], true);
            if (false !== $index) {
                unset(self::$listeners[$event][$index]);
            }
        }
    }
}
