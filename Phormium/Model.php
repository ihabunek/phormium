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

    /**
     * Holds the model meta data.
     * @var Meta
     */
    protected static $meta;

    /**
     * Parses the model object to construct a meta.
     * @return Meta
     */
    public static function getMeta()
    {
        if (!isset(self::$meta)) {
            $class = get_called_class(); // Late static binding in work
            self::$meta = Parser::parse($class);
        }
        return self::$meta;
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

    /**
     * Saves the current Model.
     * If it already exists, performs an UPDATE, otherwise an INSERT.
     */
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
        $meta = self::getMeta();

        // If PK is set, include it in query, otherwise skip for autogen to work
        if (isset($this->{$meta->pk})) {
            $columns = $meta->columns;
        } else {
            $columns = $meta->nonPK;
        }

        // Collect query arguments
        $args = array();
        foreach ($columns as $column) {
            $args[] = $this->{$column};
        }

        // Construct the query
        $query = "INSERT INTO {$meta->table} (";
        $query .= implode(', ', $columns);
        $query .= ") VALUES (";
        $query .= implode(', ', array_fill(0, count($columns), '?'));
        $query .= ");";

        $conn = DB::getConnection($meta->connection);
        $conn->executeNoFetch($query, $args);

        if (!isset($this->{$meta->pk})) {
            $this->{$meta->pk} = $conn->getLastInsertID();
        }

        return $this->{$meta->pk};
    }

    public function update()
    {
        $meta = self::getMeta();
        $columns = array_keys($meta->columns);

        // Just for safety
        if (empty($this->{$meta->pk})) {
            throw new \Exception("Cannot update model if primary key [$pk] is not set.");
        }

        // Collect query arguments (primary key goes last, skip it here)
        $args = array();
        $updates = array();
        foreach ($meta->nonPK as $column) {
            $updates[] = "$column = ?";
            $args[] = $this->{$column};
        }

        // Add primary key to arguments
        $args[] = $this->{$meta->pk};

        // Construct the query
        $query = "UPDATE {$meta->table} SET ";
        $query .= implode(', ', $updates);
        $query .= " WHERE {$meta->pk} = ?;";

        $conn = DB::getConnection($meta->connection);
        $conn->executeNoFetch($query, $args);
        return $conn->getLastRowCount();
    }

    public function toJSON()
    {
        return json_encode($this);
    }
}
