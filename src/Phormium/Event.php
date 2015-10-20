<?php

namespace Phormium;

/**
 * A catalogue of Phormium events.
 *
 * The static event functions are deprecated. Use `Orm::emitter()` instead.
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

    /**
     * @deprecated 0.9.0 Use Orm::emitter()->on(). Will be removed in 1.0.0.
     */
    public static function on($event, $listener)
    {
        self::deprecationNotice(__METHOD__, "Orm::emitter()->on()");
        return Orm::emitter()->on($event, $listener);
    }

    /**
     * @deprecated 0.9.0 Use Orm::emitter()->once(). Will be removed in 1.0.0.
     */
    public static function once($event, $listener)
    {
        self::deprecationNotice(__METHOD__, "Orm::emitter()->once()");
        return Orm::emitter()->once($event, $listener);
    }

    /**
     * @deprecated 0.9.0 Use Orm::emitter()->emit(). Will be removed in 1.0.0.
     */
    public static function emit($event, array $arguments = [])
    {
        self::deprecationNotice(__METHOD__, "Orm::emitter()->emit()");
        return Orm::emitter()->emit($event, $arguments);
    }

    /**
     * @deprecated 0.9.0 Use Orm::emitter()->listeners(). Will be removed in 1.0.0.
     */
    public static function listeners($event)
    {
        self::deprecationNotice(__METHOD__, "Orm::emitter()->listeners()");
        return Orm::emitter()->listeners($event);
    }

    /**
     * @deprecated 0.9.0 Use Orm::emitter()->removeAllListeners(). Will be removed in 1.0.0.
     */
    public static function removeListeners($event = null)
    {
        self::deprecationNotice(__METHOD__, "Orm::emitter()->removeAllListeners()");
        return Orm::emitter()->removeAllListeners($event);
    }

    /**
     * @deprecated 0.9.0 Use Orm::emitter()->removeListener(). Will be removed in 1.0.0.
     */
    public static function removeListener($event, $listener)
    {
        self::deprecationNotice(__METHOD__, "Orm::emitter()->removeListener()");
        return Orm::emitter()->removeListener($event, $listener);
    }

    private static function deprecationNotice($method, $new)
    {
        $msg = "Method $method is deprecated and will be removed.";
        $msg .= " Please use $new instead.";
        trigger_error($msg, E_USER_WARNING);
    }
}
