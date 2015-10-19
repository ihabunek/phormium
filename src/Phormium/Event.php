<?php

namespace Phormium;

/**
 * A static event emitter interface.
 */
class Event
{
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

    public static function on($event, $listener)
    {
        return Orm::emitter()->on($event, $listener);
    }

    public static function once($event, $listener)
    {
        return Orm::emitter()->once($event, $listener);
    }

    public static function emit($event, array $arguments = array())
    {
        return Orm::emitter()->emit($event, $arguments);
    }

    public static function listeners($event)
    {
        return Orm::emitter()->listeners($event);
    }

    public static function removeListeners($event = null)
    {
        return Orm::emitter()->removeAllListeners($event);
    }

    public static function removeListener($event, $listener)
    {
        return Orm::emitter()->removeListener($event, $listener);
    }
}
