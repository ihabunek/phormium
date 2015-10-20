======
Events
======

Phormium uses `Événement <https://github.com/igorw/evenement>`_  as an event
emitter. You can access it emitter by calling ``Orm::emitter()``.

To subscribe to an event, call ``on()`` method on the event emitter with the
event name as the first parameter, and the callback function as the second. The
parameters of the callback function depend on the event and are documented
below.

The ``Event`` class provides a catalogue of all available events.

.. code-block:: php

    Orm::emitter()->on('query.executing' function($query, $arguments, $connection) {
        // Do something
    })

Query events
------------

The following events are emitted when running a database query.

+--------------------------+---------------------------------------------+---------------------------------+
| Event                    | Callback arguments                          | Description                     |
+==========================+=============================================+=================================+
| Event::QUERY_STARTED     | $query, $arguments, $connection             | Before contacting the database. |
+--------------------------+---------------------------------------------+---------------------------------+
| Event::QUERY_PREPARING   | $query, $arguments, $connection             | Before preparing the query.     |
+--------------------------+---------------------------------------------+---------------------------------+
| Event::QUERY_PREPARED    | $query, $arguments, $connection             | After preparing the query.      |
+--------------------------+---------------------------------------------+---------------------------------+
| Event::QUERY_EXECUTING   | $query, $arguments, $connection             | Before executing the query.     |
+--------------------------+---------------------------------------------+---------------------------------+
| Event::QUERY_EXECUTED    | $query, $arguments, $connection             | After executing the query.      |
+--------------------------+---------------------------------------------+---------------------------------+
| Event::QUERY_FETCHING    | $query, $arguments, $connection             | Before fetching resulting data. |
+--------------------------+---------------------------------------------+---------------------------------+
| Event::QUERY_FETCHED     | $query, $arguments, $connection, $data      | After fetching resulting data.  |
+--------------------------+---------------------------------------------+---------------------------------+
| Event::QUERY_COMPLETED   | $query, $arguments, $connection, $data      | On successful completion.       |
+--------------------------+---------------------------------------------+---------------------------------+
| Event::QUERY_ERROR       | $query, $arguments, $connection, $exception | On error.                       |
+--------------------------+---------------------------------------------+---------------------------------+

Note that not all events are triggered for each query. Only prepared queries
will trigger `preparing` and `prepared` events. Only queries which return data
will trigger `fetching` and `fetched` events.

Event callback functions use the following arguments:

+---------------+----------------------+--------------------------------------+
| Name          | Type                 | Description                          |
+===============+======================+======================================+
| $query        | string               | Query SQL code                       |
+---------------+----------------------+--------------------------------------+
| $arguments    | array                | Query arguments                      |
+---------------+----------------------+--------------------------------------+
| $connection   | Connection           | Connection on which the query is run |
+---------------+----------------------+--------------------------------------+
| $data         | array                | The data fetched from the database.  |
+---------------+----------------------+--------------------------------------+
| $exception    | Exception            | Exception thrown on query failure    |
+---------------+----------------------+--------------------------------------+

Transaction events
------------------

The following events are triggered when starting or ending a database
transaction.

+-----------------------------+---------------------------------+
| Event name                  | Description                     |
+=============================+=================================+
| Event::TRANSACTION_BEGIN    | When starting a transaction.    |
+-----------------------------+---------------------------------+
| Event::TRANSACTION_COMMIT   | When committing a transaction.  |
+-----------------------------+---------------------------------+
| Event::TRANSACTION_ROLLBACK | When rolling back a transaction.|
+-----------------------------+---------------------------------+

Callbacks for these events have a single argument: the
``Phormium\Database\Connection`` on which the action is executed.

Examples
--------

Logging
~~~~~~~

A simple logging example using
`Apache log4php <https://logging.apache.org/log4php/>`_.

.. code-block:: php

    use Logger;
    use Phormium\Database\Connection;
    use Phormium\Event;
    use Phormium\Orm;

    $log = Logger::getLogger('query');

    Orm::emitter()->on(Event::QUERY_STARTED, function($query, $arguments) use ($log) {
        $log->info("Running query: $query");
    });

    Orm::emitter()->on(Event::QUERY_ERROR, function ($query, $arguments, Connection $connection, $ex) use ($log) {
        $log->error("Query failed: $ex");
    });


Collecting query statistics
~~~~~~~~~~~~~~~~~~~~~~~~~~~

Timing query execution for locating slow queries.

.. code-block:: php

    use Phormium\Event;
    use Phormium\Orm;

    class Stats
    {
        private $active;

        private $stats = array();

        /** Hooks onto relevant events. */
        public function register()
        {
            Orm::emitter()->on(Event::QUERY_STARTED, array($this, 'started'));
            Orm::emitter()->on(Event::QUERY_COMPLETED, array($this, 'completed'));
        }

        /** Called when a query has started. */
        public function started($query, $arguments)
        {
            $this->active = array(
                'query' => $query,
                'arguments' => $arguments,
                'start' => microtime(true)
            );
        }

        /** Called when a query has completed. */
        public function completed($query)
        {
            $active = $this->active;

            $active['end'] = microtime(true);
            $active['duration'] = $active['end'] - $active['start'];

            $this->stats[] = $active;
            $this->active = null;
        }

        /** Returns the collected statistics. */
        public function getStats()
        {
            return $this->stats;
        }
    }

And to start collecting stats:

.. code-block:: php

    $stats = new Stats();
    $stats->register();

Note that this example misses failed queries, which will never emit
`query.completed`, but `query.error` instead.
