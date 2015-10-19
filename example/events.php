<?php

/**
 * Demonstrates using query events for collecting statistics.
 */

use Phormium\Orm;

// Include Phormium and models
require __DIR__ . "/../vendor/autoload.php";
require __DIR__ . "/models/Person.php";

// Configure Phormium
Orm::configure('config.json');

use Phormium\Event;

/** A class which collects query execution durations */
class Stats
{
    private $active;

    private $stats;

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

$stats = new Stats();
$stats->register();

// Execute some queries
Person::find(10);
Person::objects()->fetch();

// Print collected statistics
print_r($stats->getStats());
