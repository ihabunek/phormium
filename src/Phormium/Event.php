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
    // Connection events
    const DB_CONNECTING    = 'db.connecting';
    const DB_CONNECTED     = 'db.connected';
    const DB_DISCONNECTING = 'db.disconnecting';
    const DB_DISCONNECTED  = 'db.disconnected';

    // Query events
    const QUERY_STARTED   = 'query.started';
    const QUERY_PREPARING = 'query.preparing';
    const QUERY_PREPARED  = 'query.prepared';
    const QUERY_EXECUTING = 'query.executing';
    const QUERY_EXECUTED  = 'query.executed';
    const QUERY_FETCHING  = 'query.fetching';
    const QUERY_FETCHED   = 'query.fetched';
    const QUERY_COMPLETED = 'query.completed';
    const QUERY_ERROR     = 'query.error';

    // Transaction events
    const TRANSACTION_BEGIN    = 'transaction.begin';
    const TRANSACTION_COMMIT   = 'transaction.commit';
    const TRANSACTION_ROLLBACK = 'transaction.rollback';

    private static $listeners = array();

    public static function on($event, $listener)
    {
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
            foreach (self::$listeners[$event] as $listener) {
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
