<?php

namespace Phormium;

/**
 * Collects query execution statistics.
 */
class Stats
{
    /** Holds the stats */
    private static $stats = array();

    /** Max number of queries to keep. */
    private static $limit = 100;

    /** Add a stats array. */
    public static function add(array $stat)
    {
        self::$stats[] = $stat;

        // Enforce max stat items
        $count = count(self::$stats);
        if ($count > self::$limit) {
            self::$stats = array_slice($count - self::$limit);
        }
    }

    /** Returns all stats. */
    public static function get()
    {
        return self::$stats;
    }

    /** Clears stats. */
    public static function clear()
    {
        self::$stats = array();
    }

    /** Sets max number of stats records to keep. */
    public static function setLimit($limit)
    {
        self::$limit = $limit;
    }
}
