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

        $meta = new Meta();
        $meta->class = $class;
        $meta->database = $_meta['database'];
        $meta->table = $_meta['table'];
        $meta->pk = self::parsePK($_meta);
        $meta->columns = array();

        // Fetch class' public properties which correspond to column names
        $rc = new \ReflectionClass($class);
        $props = $rc->getProperties(\ReflectionProperty::IS_PUBLIC);
        foreach ($props as $prop) {
            $meta->columns[] = $prop->name;
        }

        if (isset($meta->pk)) {

            // Verify primary key columns exist
            foreach ($meta->pk as $column) {
                if (!in_array($column, $meta->columns)) {
                    throw new \Exception(
                        "Invalid $class::\$_meta. Given primary key column [{$column}] does not exist."
                    );
                }
            }

            // Compile non-pk columns
            $meta->nonPK = array();
            foreach ($meta->columns as $column) {
                if (!in_array($column, $meta->pk)) {
                    $meta->nonPK[] = $column;
                }
            }
        }

        return $meta;
    }

    private static function parsePK($meta)
    {
        if (!isset($meta['pk'])) {
            return null;
        }

        if (is_string($meta['pk'])) {
            return array($meta['pk']);
        }

        if (is_array($meta['pk'])) {
            return $meta['pk'];
        }

        throw new \Exception("Invalid {$meta['class']}::\$_meta['pk']. Not a string or array.");
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

        if (is_object($array)) {
            $array = (array) $array;
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

        if (!isset($meta->pk)) {
            throw new \Exception("Model not writable because primary key is not defined in _meta.");
        }

        // Check if all primary key columns are populated
        $pkSet = true;
        foreach ($meta->pk as $col) {
            if (empty($this->{$col})) {
                $pkSet = false;
                break;
            }
        }

        // If primary key value is not set, do an INSERT
        if (!$pkSet) {
            $this->insert();
        } else {
            // Otherwise, try to UPDATE, and if nothing is updated then INSERT
            $count = $this->update();
            if ($count == 0) {
                $this->insert();
            }
        }
    }

    /**
     * Returns the PK columns with their values as an associative array.
     */
    public function getPK()
    {
        $meta = self::getMeta();
        $pk = array();
        foreach ($meta->pk as $column) {
            $pk[$column] = $this->{$column};
        }
        return $pk;
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
