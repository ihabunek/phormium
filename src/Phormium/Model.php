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

    public static function getQuery()
    {
        return new Query(self::getMeta());
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

    /**
     * Creates a Model instance from data in the given array or object.
     * @param array|stdClass $array The input array or stdClass object.
     */
    public static function fromArray($array)
    {
        $class = get_called_class();
        $instance = new $class();

        if ($array instanceof \stdClass) {
            $array = (array) $array;
        }

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

    /**
     * Fetches a single record by primary key.
     *
     * @param mixed The primary key, can be more than 1 param for composite keys.
     */
    public static function get()
    {
        $args = func_get_args();
        $meta = self::getMeta();

        if (!isset($meta->pk)) {
            $class = get_called_class();
            throw new \Exception("Primary key not defined for model [$class].");
        }

        // Check correct number of columns is given
        $countArgs = count($args);
        $countPK = count($meta->pk);
        if ($countArgs  !== $countPK) {
            $class = get_called_class();
            throw new \Exception("Model [$class] has $countPK primary key columns. $countArgs arguments given.");
        }

        // Create a queryset and filter by PK
        $qs = self::objects();
        foreach($meta->pk as $name) {
            $value = array_shift($args);
            $qs = $qs->filter($name, '=', $value);
        }

        return $qs->single();
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

    public function toJSON()
    {
        return json_encode($this);
    }

    public function toArray()
    {
        return (array) $this;
    }
}
