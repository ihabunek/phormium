<?php

namespace Phormium;

use Phormium\Helper\Json;

use Symfony\Component\Yaml\Yaml;

/**
 * Parent class for database-mapped classes.
 */
abstract class Model
{
    use ModelRelationsTrait;

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
     *
     * @return \Phormium\Meta
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
     *
     * @return \Phormium\Query
     */
    public static function getQuery()
    {
        return new Query(self::getMeta());
    }

    /**
     * Constructs a QuerySet for the model.
     *
     * @return \Phormium\QuerySet
     */
    public static function objects()
    {
        $meta = self::getMeta();
        $query = self::getQuery();
        return new QuerySet($query, $meta);
    }

    /**
     * Fetches all records from the table.
     *
     * @return array An array of models.
     */
    public static function all()
    {
        return self::objects()->fetch();
    }

    /**
     * Fetches a single record by primary key, throws an exception if the model
     * is not found. This method requires the model to have a PK defined.
     *
     * @param mixed The primary key value, either as one or several arguments,
     *              or as an array of one or several values.
     *
     * @return Model
     * @throws \Exception thrown when passed values do not exist as primary key
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
     * @param mixed         The primary key value, either as one or several arguments,
     *                      or as an array of one or several values.
     *
     * @return Model|null   The Model instance or NULL if not found.
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
     * @param mixed     The primary key value, either as one or several arguments,
     *                  or as an array of one or several values.
     * @return boolean
     */
    public static function exists()
    {
        $argv = func_get_args();
        $argc = func_num_args();

        $qs = self::getQuerySetForPK($argv, $argc);
        return $qs->exists();
    }

    /**
     * Creates a Model instance from data in the given array or object.
     *
     * @param array|\stdClass  $array     The input array or stdClass object.
     * @param boolean          $strict    If set to TRUE, will throw an exception if the
     *                                    array contains a property which does not exist in the Model.
     *                                    Default value is FALSE which means these will be ignored.
     *
     * @return \Phormium\Model
     */
    public static function fromArray($array, $strict = false)
    {
        $class = get_called_class();

        $instance = new $class();
        $instance->merge($array, $strict);
        return $instance;
    }

    /**
     * Creates a Model instance from data in JSON.
     *
     * @param string    $json      The input data in JSON.
     * @param boolean   $strict    If set to TRUE, will throw an exception if the
     *                             json contains a property which does not exist in the Model.
     *                             Default value is FALSE which means these will be ignored.
     *
     * @return \Phormium\Model
     */
    public static function fromJSON($json, $strict = false)
    {
        $array = Json::parse($json);

        if (is_object($array)) {
            $array = (array) $array;
        }

        return self::fromArray($array, $strict);
    }

    /**
     * Creates a Model instance from data in YAML.
     *
     * @param string    $yaml   The input data in YAML.
     * @param boolean   $strict If set to TRUE, will throw an exception if the
     *                          json contains a property which does not exist in the Model.
     *                          Default value is FALSE which means these will be ignored.
     *
     * @return \Phormium\Model
     */
    public static function fromYAML($yaml, $strict = false)
    {
        $array = Yaml::parse($yaml);
        return self::fromArray($array, $strict);
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

            if (!is_scalar($value)) {
                throw new \Exception("Nonscalar value given for primary key value.");
            }

            $qs = $qs->filter($name, '=', $value);
        }

        return $qs;
    }

    // ******************************************
    // *** Dynamics                           ***
    // ******************************************

    /**
     * Saves the current Model to the database. If it already exists, performs
     * an UPDATE, otherwise an INSERT.
     *
     * This method can be sub-optimal since it may do an additional query to
     * determine if the model exists in the database. If performance is
     * important, use update() and insert() explicitely.
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

        // If primary key is populated, check whether the record with given
        // primary key exists, and update it if it does. Otherwise insert.
        if ($pkSet) {
            $exists = static::exists($this->getPK());
            if ($exists) {
                $this->update();
            } else {
                $this->insert();
            }
        } else {
            $this->insert();
        }
    }

    /**
     * Performs an INSERT query with the data from the model.
     */
    public function insert()
    {
        return self::getQuery()->insert($this);
    }

    /**
     * Performs an UPDATE query with the data from the model.
     *
     * @returns integer The number of affected rows.
     */
    public function update()
    {
        return self::getQuery()->update($this);
    }

    /**
     * Performs an DELETE query filtering by model's primary key.
     *
     * @returns integer The number of affected rows.
     */
    public function delete()
    {
        return self::getQuery()->delete($this);
    }

    /**
     * Returns the model's primary key value as an associative array.
     *
     * @return array The primary key.
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

    /**
     * Merges values from an associative array into the model.
     *
     * @param array|\stdClass $values Associative array (or stdClass object)
     *      where keys are names of properties of the model, and values are
     *      desired values for those properties.
     * @param boolean $strict If set to TRUE, will throw an exception if the
     *      json contains a property which does not exist in the Model. Default
     *      value is FALSE which means these will be ignored.
     * @throws \Exception
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

    /**
     * Returns the model's Array representation.
     *
     * @return array
     */
    public function toArray()
    {
        return (array) $this;
    }

    /**
     * Returns the model's JSON representation.
     *
     * @return string
     */
    public function toJSON()
    {
        return Json::dump($this);
    }

    /**
     * Returns the model's YAML representation.
     *
     * @return string
     */
    public function toYAML()
    {
        return Yaml::dump(self::toArray($this));
    }

    /**
     * Prints the model and it's data in a human readable format.
     */
    public function dump()
    {
        $meta = self::getMeta();
        $name = get_class($this) . " ($meta->database.$meta->table)";

        echo "$name\n";
        echo str_repeat("=", strlen($name)) . "\n";

        foreach ($meta->columns as $column) {
            $value = $this->$column;
            if ($value === null) {
                $value = 'NULL';
            } elseif (is_string($value)) {
                $value = '"' . $value . '"';
            } else {
                $value = (string) $value;
            }

            if (in_array($column, $meta->pk)) {
                $value .= ' (PK)';
            }

            echo "$column: $value\n";
        }
        echo "\n";
    }
}
