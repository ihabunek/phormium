======
Events
======

Phormium emits a series of events.

You can subscribe to an event by invoking `Event::on()` with the event name as
the first parameter, and the callback function as the second. The parameters of
the callback function depend on the event and are documented below.

.. code-block:: php

    Event::on('query.executing' function($query, $arguments, $connection) {
        // Do something
    })

Connection events
-----------------

The following events are emitted by connections.

==================  ===============================  =================================
 Event               Callback arguments               Description
==================  ===============================  =================================
 db.connecting       $name, $settings                 Before contacting the database.
 db.connected        $name, $settings, $connection    Before contacting the database.
 db.disconnecting    $name, $connection               Before preparing the query.
==================  ===============================  =================================

Query events
------------

The following events are emitted when running a database query.

+-------------------+---------------------------------------------+---------------------------------+
| Event             | Callback arguments                          | Description                     |
+===================+=============================================+=================================+
| query.started     | $query, $arguments, $connection             | Before contacting the database. |
+-------------------+---------------------------------------------+---------------------------------+
| query.preparing   | $query, $arguments, $connection             | Before preparing the query.     |
+-------------------+---------------------------------------------+---------------------------------+
| query.prepared    | $query, $arguments, $connection             | After preparing the query.      |
+-------------------+---------------------------------------------+---------------------------------+
| query.executing   | $query, $arguments, $connection             | Before executing the query.     |
+-------------------+---------------------------------------------+---------------------------------+
| query.executed    | $query, $arguments, $connection             | After executing the query.      |
+-------------------+---------------------------------------------+---------------------------------+
| query.fetching    | $query, $arguments, $connection             | Before fetching resulting data. |
+-------------------+---------------------------------------------+---------------------------------+
| query.fetched     | $query, $arguments, $connection, $data      | After fetching resulting data.  |
+-------------------+---------------------------------------------+---------------------------------+
| query.completed   | $query, $arguments, $connection, $data      | On successful completion.       |
+-------------------+---------------------------------------------+---------------------------------+
| query.error       | $query, $arguments, $connection, $exception | On error.                       |
+-------------------+---------------------------------------------+---------------------------------+

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
| $connection   | Phormium\\Connection | Connection on which the query is run |
+---------------+----------------------+--------------------------------------+
| $data         | array                | The data fetched from the database.  |
+---------------+----------------------+--------------------------------------+
| $exception    | Exception            | Exception thrown on query failure    |
+---------------+----------------------+--------------------------------------+

Transaction events
------------------

The following events are triggered when starting or ending a database
transaction.

+----------------------+---------------------------------+
| Event name           | Description                     |
+======================+=================================+
| transaction.begin    | When starting a transaction.    |
+----------------------+---------------------------------+
| transaction.commit   | When committing a transaction.  |
+----------------------+---------------------------------+
| transaction.rollback | When rolling back a transaction.|
+----------------------+---------------------------------+

Callbacks for these events have a single argument: the `Phormium\Connection` on
which the action is executed.

Examples
--------

Logging
~~~~~~~

A simple logging example using
`Apache log4php <https://logging.apache.org/log4php/>`_.

.. code-block:: php

    use Logger;
    use Phormium\Events;

    $log = Logger::getLogger('query');

    Event::on('query.started', function($query, $arguments) use ($log) {
        $log->info("Running query: $query");
    });

    Event::on('query.error', function ($query, $arguments, $connection, $ex) use ($log) {
        $log->error("Query failed: $ex");
    });


Collecting query statistics
~~~~~~~~~~~~~~~~~~~~~~~~~~~

Timing query execution for locating slow queries.

.. code-block:: php

    use Phormium\Event;

    class Stats
    {
        private $active;

        private $stats = array();

        /** Hooks onto relevant events. */
        public function register()
        {
            Event::on('query.started', array($this, 'started'));
            Event::on('query.completed', array($this, 'completed'));
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
