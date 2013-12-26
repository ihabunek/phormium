<?php

namespace Phormium;

/**
 * Collects query execution statistics.
 */
class Stats
{
    /** Holds the stats. */
    private static $stats = array();

    /** Max number of records to keep (null for no limit). */
    private static $limit = null;

    /** How many decimals to keep for timer columns. */
    private static $precision = 4;

    /** A list of columns kept for each stat record. */
    private static $columns = array('query', 'arguments', 'prepare', 'execute', 'fetch', 'total', 'numrows');

    /** A list of columns to be rounded to $precision. */
    private static $roundColumns = array('prepare', 'execute', 'fetch', 'total');

    /** Add a stats array. */
    public static function add(array $stat)
    {
        foreach(self::$roundColumns as $column) {
            if (isset($stat[$column])) {
                $stat[$column] = round($stat[$column], self::$precision);
            }
        }

        self::$stats[] = $stat;

        // Enforce max stat items
        if (isset(self::$limit) && count(self::$stats) > self::$limit) {
            array_shift(self::$stats);
        }
    }

    /**
     * Returns all stats.
     *
     * @param string $sortColumn Column to sort stats by. For possible values,
     * see {@link $columns} If not given, stats will be ordered in order they
     * were recorded.
     */
    public static function get($sortColumn = null)
    {
        if (isset($sortColumn)) {
            return self::getSorted($sortColumn);
        }

        return self::$stats;
    }

    /** Clears stats. */
    public static function clear()
    {
        self::$stats = array();
    }

    /**
     * Dumps statistics to stdout as a table.
     *
     * @param string $sortColumn Column to sort stats by. For possible values,
     * see {@link $columns} If not given, stats will be ordered in order they
     * were recorded.
     */
    public static function dump($sortColumn = null)
    {
        Printer::dump(self::get($sortColumn));
    }

    /** Sets max number of stats records to keep. */
    public static function setLimit($limit)
    {
        self::$limit = $limit;
    }

    /** Returns collected stats, ordered by the given column. */
    private static function getSorted($column)
    {
        // If prefixed by a minus, reverse sort (ascending)
        if (isset($column[0]) && $column[0] === '-') {
            $reverse = true;
            $column = substr($column, 1);
        } else {
            $reverse = false;
        }

        if (!in_array($column, self::$columns)) {
            trigger_error("Invalid sort column [$column].", E_USER_WARNING);
            return self::$stats;
        }

        $stats = self::$stats;
        $comp = self::getCompareFunction($column, $reverse);
        usort($stats, $comp);
        return $stats;
    }

    /** Returns a comparison function used to sort the stats collection by given column. */
    private static function getCompareFunction($column, $reverse = false)
    {
        return function ($left, $right) use ($column, $reverse) {
            if ($left[$column] == $right[$column]) {
               return 0;
            }
            if ($reverse) {
                return $left[$column] > $right[$column] ? -1 : 1;
            } else {
                return $left[$column] < $right[$column] ? -1 : 1;
            }
        };
    }
}
