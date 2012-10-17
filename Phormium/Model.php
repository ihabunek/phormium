<?php

namespace Phormium;

/**
 * Parent class for database-mapped classes.
 */
abstract class Model
{
    // ******************************************
    // *** Statics                            ***
    // ******************************************

    /** Caches the meta so it's not parsed multiple times. */
    protected static $meta;

    /**
     * Parses the model object to construct a meta.
     */
    public static function getMeta()
    {
        if (!isset(self::$meta)) {
            $class = get_called_class(); // Late static binding in work
            self::$meta = Parser::parse($class);
        }
        return self::$meta;
    }

    public static function getUpdateQuery()
    {
        $meta = self::getMeta();
        $columns = array_keys($meta->columns);
        $pk = $meta->pk;

        $updates = array();
        foreach ($meta->columns as $column => $config) {
            // Skip primary key
            if ($column != $meta->pk) {
                $updates[] = "$column = ?";
            }
        }

        $query = "UPDATE {$meta->table} SET ";
        $query .= implode(', ', $updates);
        $query .= " WHERE {$pk} = ?;";

        return $query;
    }

    public static function getInsertQuery()
    {
        $meta = self::getMeta();
        $columns = array_keys($meta->columns);

        $inserts = array();
        foreach ($meta->columns as $column => $config) {
            // Skip primary key
            if ($column != $meta->pk) {
                $updates[] = "$column = ?";
            }
        }

        $query = "INSERT INTO {$meta->table} (";
        $query .= implode(', ', $columns);
        $query .= ") VALUES (";
        $query .= implode(', ', array_fill(0, count($columns), '?'));
        $query .= ");";

        return $query;
    }

    /**
     * Constructs a QuerySet for the model.
     */
    public static function objects()
    {
        $meta = self::getMeta();
        return new QuerySet($meta);
    }

    // ******************************************
    // *** Dynamics                           ***
    // ******************************************

    public function __construct($data = null)
    {
        if (isset($data)) {

            if (!is_array($data)) {
                throw new \Exception("\$data must be an array");
            }

            foreach ($data as $name => $value) {
                if (property_exists($this, $name)) {
                    $this->$name = $value;
                } else {
                    $class = get_class($this);
                    throw new \Exception("Property [$name] does not exist in class [$class].");
                }
            }
        }
    }

    public function save()
    {
        $meta = static::getMeta();
        $conn = DB::getConnection($meta->connection);

        $pkColumn = $meta->pk;

        if (empty($this->{$meta->pk})) {
            // If primary key value is not set, do an INSERT
            $this->insert();
        } else {
            // Otherwise, try to UPDATE, and if nothing is updated then INSERT
            $count = $this->update();
            if ($count == 0) {
                $this->insert();
            }
        }
    }

    public function insert()
    {
        $query = self::getInsertQuery();
        $meta = self::getMeta();

        // Collect query arguments
        $args = array();
        foreach ($meta->columns as $column => $config) {
            $args[] = $this->{$column};
        }

        $conn = DB::getConnection($meta->connection);
        $conn->executeNoFetch($query, $args);

        if (empty($this->{$meta->pk})) {
            $this->{$meta->pk} = $conn->getLastInsertID();
        }

        return $this->{$meta->pk};
    }

    public function update()
    {
        $query = self::getUpdateQuery();
        $meta = self::getMeta();
        $columns = array_keys($meta->columns);

        // Just for safety
        if (empty($this->{$meta->pk})) {
            throw new \Exception("Cannot update model if primary key [$pk] is not set.");
        }

        // Collect query arguments
        $args = array();
        foreach ($meta->columns as $column => $config) {
            // Primary key goes last, skip it here
            if ($column != $meta->pk) {
                $args[] = $this->{$column};
            }
        }
        $args[] = $this->{$meta->pk};

        $conn = DB::getConnection($meta->connection);
        $conn->executeNoFetch($query, $args);
        return $conn->getLastRowCount();
    }

    public function toJSON()
    {
        return json_encode($this);
    }
}
