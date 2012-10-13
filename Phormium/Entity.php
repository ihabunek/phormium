<?php

namespace Phormium;

/**
 * Parent class for database-mapped classes.
 */
abstract class Entity
{
    // ******************************************
    // *** Statics                            ***
    // ******************************************

    /** Caches the model so it's not parsed multiple times. */
    protected static $model;

    /**
     * Parses the entity object to construct a Model.
     */
    public static function getModel()
    {
        if (!isset(self::$model)) {
            $class = get_called_class(); // Late static binding in work
            self::$model = Parser::parse($class);
        }
        return self::$model;
    }

    public static function getUpdateQuery()
    {
        $model = self::getModel();
        $columns = array_keys($model->columns);
        $pk = $model->pk;

        $updates = array();
        foreach ($model->columns as $column => $config) {
            // Skip primary key
            if ($column != $model->pk) {
                $updates[] = "$column = ?";
            }
        }

        $query = "UPDATE {$model->table} SET ";
        $query .= implode(', ', $updates);
        $query .= " WHERE {$pk} = ?;";

        return $query;
    }

    public static function getInsertQuery()
    {
        $model = self::getModel();
        $columns = array_keys($model->columns);

        $inserts = array();
        foreach ($model->columns as $column => $config) {
            // Skip primary key
            if ($column != $model->pk) {
                $updates[] = "$column = ?";
            }
        }

        $query = "INSERT INTO {$model->table} (";
        $query .= implode(', ', $columns);
        $query .= ") VALUES (";
        $query .= implode(', ', array_fill(0, count($columns), '?'));
        $query .= ");";

        return $query;
    }

    /**
     * Constructs a QuerySet for the entity.
     */
    public static function objects()
    {
        $model = self::getModel();
        return new QuerySet($model);
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
        $model = static::getModel();
        $conn = DB::getConnection($model->connection);

        $pkColumn = $model->pk;

        if (empty($this->{$model->pk})) {
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
        $model = self::getModel();

        // Collect query arguments
        $args = array();
        foreach ($model->columns as $column => $config) {
            $args[] = $this->{$column};
        }

        $conn = DB::getConnection($model->connection);
        $conn->executeNoFetch($query, $args);

        if (empty($this->{$model->pk})) {
            $this->{$model->pk} = $conn->getLastInsertID();
        }

        return $this->{$model->pk};
    }

    public function update()
    {
        $query = self::getUpdateQuery();
        $model = self::getModel();
        $columns = array_keys($model->columns);

        // Just for safety
        if (empty($this->{$model->pk})) {
            throw new \Exception("Cannot update entity if primary key [$pk] is not set.");
        }

        // Collect query arguments
        $args = array();
        foreach ($model->columns as $column => $config) {
            // Primary key goes last, skip it here
            if ($column != $model->pk) {
                $args[] = $this->{$column};
            }
        }
        $args[] = $this->{$model->pk};

        $conn = DB::getConnection($model->connection);
        $conn->executeNoFetch($query, $args);
        return $conn->getLastRowCount();
    }

    public function toJSON()
    {
        return json_encode($this);
    }
}
