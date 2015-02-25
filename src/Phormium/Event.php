<?php

namespace Phormium;

/**
 * A catalogue of existing events.
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
}
