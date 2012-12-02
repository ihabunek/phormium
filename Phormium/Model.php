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
