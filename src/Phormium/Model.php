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
     * Returns the Model's Meta object.
     * @return Meta
     */
    public static function getMeta()
    {
        return Parser::getMeta(
            get_called_class(),
            static::$_meta
        );
    }

    /**
     * Returns the Query object used to run queries for this model.
     * @return Query
     */
    public static function getQuery()
    {
        return new Query(self::getMeta());
    }

    /**
     * Constructs a QuerySet for the model.
     * @return QuerySet
     */
    public static function objects()
    {
        $meta = self::getMeta();
        $query = self::getQuery();
        return new QuerySet($query, $meta);
    }

    /**
     * Creates a Model instance from data in the given array or object.
     * @param array|stdClass $array The input array or stdClass object.
     * @param boolean $strict If set to TRUE, will throw an exception if the
     *      array contains a property which does not exist in the Model. Default
     *      value is FALSE.
     * @return Model
     */
    public static function fromArray($array, $strict = false)
    {
        $class = get_called_class();

        $instance = new $class();
        $instance->merge($array, $strict);
        return $instance;
    }


    /**
     * Fetches a single record by primary key, throws an exception if the model
     * is not found. This method requires the model to have a PK defined.
     *
     * @param mixed The primary key value, either as one or several arguments,
     *      or as an array of one or several values.
     * @return Model
     */
    public static function get()
    {
        $argv = func_get_args();
        $argc = func_num_args();

        $qs = self::getQuerySetForPK($argv, $argc);
        $model = $qs->single(true);

        if ($model === null) {
            $class = get_called_class();
            $pk = implode(',', $argv);
            throw new \Exception("[$class] record with primary key [$pk] does not exist.");
        }

        return $model;
    }

    /**
     * Fetches a single record by primary key, returns NULL if the model is not
     * found. This method requires the model to have a PK defined.
     *
     * @param mixed The primary key value, either as one or several arguments,
     *      or as an array of one or several values.
     * @return Model|null The Model instance or NULL if not found.
     */
    public static function find()
    {
        $argv = func_get_args();
        $argc = func_num_args();

        $qs = self::getQuerySetForPK($argv, $argc);
        return $qs->single(true);
    }

    /**
     * Checks whether a record with the given Primary Key exists in the
     * database. This method requires the model to have a PK defined.
     *
     * @param mixed The primary key value, either as one or several arguments,
     *      or as an array of one or several values.
     * @return boolean
     */
    public static function exists()
    {
        $argv = func_get_args();
        $argc = func_num_args();

        $qs = self::getQuerySetForPK($argv, $argc);
        return $qs->exists();
    }

    /** Inner method used by get(), search() and exists(). */
    private static function getQuerySetForPK($argv, $argc)
    {
        // Allow passing the PK as an array
        if ($argc == 1 && is_array($argv[0])) {
            $argv = $argv[0];
            $argc = count($argv);
        }

        // Model must have PK defined
        $meta = self::getMeta();
        if (!isset($meta->pk)) {
            $class = get_called_class();
            throw new \Exception("Primary key not defined for model [$class].");
        }

        // Check correct number of columns is given
        $countPK = count($meta->pk);
        if ($argc !== $countPK) {
            $class = get_called_class();
            throw new \Exception("Model [$class] has $countPK primary key columns. $argc arguments given.");
        }

        // Create a queryset and filter by PK
        $qs = self::objects();
        foreach ($meta->pk as $name) {
            $value = array_shift($argv);
            $qs = $qs->filter($name, '=', $value);
        }

        return $qs;
    }

    /**
     * Creates a Model instance from data in the given array.
     * @return Model
     */
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
     * @return array The PK columns.
     */
    public function getPK()
    {
        $meta = self::getMeta();

        if (!isset($meta->pk)) {
            return array();
        }

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

    /**
     * Merges values from an associative array into the model.
     *
     * @param array|stdClass $values Associative array (or stdClass object)
     *      where keys are names of properties of the model, and values are
     *      desired values for those properties.
     */
    public function merge($values, $strict = false)
    {
        if ($values instanceof \stdClass) {
            $values = (array) $values;
        }

        if (!is_array($values)) {
            throw new \Exception("Given argument is not an array.");
        }

        foreach ($values as $name => $value) {
            if (property_exists($this, $name)) {
                $this->{$name} = $value;
            } else {
                if ($strict) {
                    $class = get_class($this);
                    throw new \Exception("Property [$name] does not exist in class [$class].");
                }
            }
        }
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
