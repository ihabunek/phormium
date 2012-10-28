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
     * User-populated meta-data. Used to construct a Meta object.
     * @var array
     */
    protected static $_meta;

    /**
     * The Meta object contructed from Model properties and data in <var>$_meta</var>.
     * @var Meta
     */
    protected static $_parsedMeta;

    /**
     * The Query object used to construct an execute SQL queries for this model.
     * @var Query
     */
    protected static $_query;

    /**
     * Returns the Model's Meta object.
     * @return Meta
     */
    public static function getMeta()
    {
        if (!isset(self::$_parsedMeta)) {
            self::$_parsedMeta = self::parseMeta();
        }
        return self::$_parsedMeta;
    }

    public static function getQuery()
    {
        if (!isset(self::$_query)) {
            self::$_query = new Query(self::getMeta());
        }
        return self::$_query;
    }

    /**
     * Constructs a QuerySet for the model.
     */
    public static function objects()
    {
        $meta = self::getMeta();
        $query = self::getQuery();
        return new QuerySet($query, $meta);
    }

    protected static function parseMeta()
    {
        // Late static binding at work
        $class = get_called_class();
        $_meta = static::$_meta;

        if (!is_array($_meta)) {
            throw new \Exception("Invalid $class::\$_meta. Not an array.");
        }

        if (empty($_meta['database'])) {
            throw new \Exception("Invalid $class::\$_meta. Missing 'database'.");
        }

        if (empty($_meta['table'])) {
            throw new \Exception("Invalid $class::\$_meta. Missing 'table'.");
        }

        if (empty($_meta['pk'])) {
            throw new \Exception("Invalid $class::\$_meta. Missing 'pk'.");
        }

        $meta = new Meta();
        $meta->class = $class;
        $meta->database = $_meta['database'];
        $meta->table = $_meta['table'];
        $meta->pk = $_meta['pk'];
        $meta->nonPK = array();
        $meta->columns = array();

        // Fetch class' public properties which correspond to column names
        $rc = new \ReflectionClass($class);
        $props = $rc->getProperties(\ReflectionProperty::IS_PUBLIC);

        foreach ($props as $prop) {
            $name = $prop->name;
            $meta->columns[] = $name;
            if ($name != $meta->pk) {
                $meta->nonPK[] = $name;
            }
        }

        // Check the given primary key exists as column
        if (!in_array($meta->pk, $meta->columns)) {
            throw new \Exception("Invalid $class::\$_meta. Given primary key [{$meta->pk}] is not a column.");
        }

        return $meta;
    }

    /** Creates a Model instance from data in the given array. */
    public static function fromArray($array)
    {
        $class = get_called_class();
        $instance = new $class();

        if (!is_array($array)) {
            throw new \Exception("Given argument is not an array.");
        }

        foreach ($array as $name => $value) {
            if (property_exists($instance, $name)) {
                $instance->{$name} = $value;
            } else {
                throw new \Exception("Property [$name] does not exist in class [$class].");
            }
        }

        return $instance;
    }

    /** Creates a Model instance from data in the given array. */
    public static function fromJSON($json)
    {
        $array = json_decode($json);

        $error = json_last_error();
        if ($error !== JSON_ERROR_NONE) {
            throw new \Exception("Invalid JSON string. Error code [$error].");
        }

        return self::fromArray($array);
    }

    // ******************************************
    // *** Dynamics                           ***
    // ******************************************

    /**
     * Saves the current Model.
     * If it already exists, performs an UPDATE, otherwise an INSERT.
     */
    public function save()
    {
        $meta = self::getMeta();

        // If primary key value is not set, do an INSERT
        if (empty($this->{$meta->pk})) {
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
        return self::getQuery()->insert($this);
    }

    public function update()
    {
        return self::getQuery()->update($this);
    }

    public function delete()
    {
        return self::getQuery()->delete($this);
    }

    public function toJSON()
    {
        return json_encode($this);
    }

    public function toArray()
    {
        return (array) $this;
    }
}
