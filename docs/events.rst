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

Query events
------------

The following events are emitted when running a database query.

+-----------------+---------------------------------+
| Event name      | Description                     |
+=================+=================================+
| query.started   | Before contacting the database. |
+-----------------+---------------------------------+
| query.preparing | Before preparing the query.     |
+-----------------+---------------------------------+
| query.prepared  | After preparing the query.      |
+-----------------+---------------------------------+
| query.executing | Before executing the query.     |
+-----------------+---------------------------------+
| query.executed  | After executing the query.      |
+-----------------+---------------------------------+
| query.fetching  | Before fetching resulting data. |
+-----------------+---------------------------------+
| query.fetched   | After fetching resulting data.  |
+-----------------+---------------------------------+
| query.completed | On successful completion.       |
+-----------------+---------------------------------+
| query.error     | On error.                       |
+-----------------+---------------------------------+

Note that not all events are triggered for each query. Only prepared queries
will trigger `preparing` and `prepared` events. Only queries which return data
will trigger `fetching` and `fetched` events.

Event callback functions have the following arguments:

+-------------+----------------------+--------------------------------------+----------------+
| Name        | Type                 | Description                          | Events         |
+=============+======================+======================================+================+
| $query      | string               | Query SQL code                       | all            |
+-------------+----------------------+--------------------------------------+----------------+
| $arguments  | array                | Query arguments                      | all            |
+-------------+----------------------+--------------------------------------+----------------+
| $connection | Phormium\\Connection | Connection on which the query is run | all            |
+-------------+----------------------+--------------------------------------+----------------+
| $data       | array                | The data fetched from the database.  | query.prepared |
+-------------+----------------------+--------------------------------------+----------------+
| $exception  | Exception            | Exception thrown on query failure    | query.error    |
+-------------+----------------------+--------------------------------------+----------------+

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

A simple logging example using Apache log4php.

.. code-block:: php

    use Phormium\Events;

    Event::on('query.started', function($query, $arguments) {
        $logger = \Logger::getLogger('query');
        $logger->info("Running query: $query");
    });

    Event::on('query.error', function ($query, $arguments, $connection, $ex) {
        $logger = \Logger::getLogger('query');
        $logger->error("Query failed: $ex");
    });

