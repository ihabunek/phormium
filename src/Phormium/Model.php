<?php

namespace Phormium;

use Phormium\Exception\InvalidQueryException;
use Phormium\Exception\ModelNotFoundException;
use Phormium\Exception\OrmException;
use Phormium\Filter\CompositeFilter;
use Phormium\Filter\Filter;
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
     * @return Meta
     */
    public static function getMeta()
    {
        return Orm::getMeta(get_called_class());
    }

    /**
     * Returns the raw $_meta array.
     *
     * @return array
     */
    public static function getRawMeta()
    {
        return static::$_meta;
    }

    /**
     * Returns the Query object used to run queries for this model.
     * @return Query
     */
    public static function getQuery()
    {
        return Orm::getQuery(get_called_class());
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
     *      or as an array of one or several values.
     * @return Model
     */
    public static function get(...$pk)
    {
        $qs = self::getQuerySetForPK(...$pk);
        $model = $qs->single(true);

        if ($model === null) {
            $class = get_called_class();
            $pk = implode(',', $pk);
            throw new ModelNotFoundException("[$class] record with primary key [$pk] does not exist.");
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
    public static function find(...$pk)
    {
        $qs = self::getQuerySetForPK(...$pk);

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
    public static function exists(...$pk)
    {
        $qs = self::getQuerySetForPK(...$pk);

        return $qs->exists();
    }

    /**
     * Creates a Model instance from data in the given array or object.
     *
     * @param array|stdClass $array The input array or stdClass object.
     * @param boolean $strict If set to TRUE, will throw an exception if the
     *      array contains a property which does not exist in the Model. Default
     *      value is FALSE which means these will be ignored.
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
     * Creates a Model instance from data in JSON.
     *
     * @param string $json The input data in JSON.
     * @param boolean $strict If set to TRUE, will throw an exception if the
     *      json contains a property which does not exist in the Model. Default
     *      value is FALSE which means these will be ignored.
     *
     * @return Model
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
     * @param string $yaml The input data in YAML.
     * @param boolean $strict If set to TRUE, will throw an exception if the
     *      json contains a property which does not exist in the Model. Default
     *      value is FALSE which means these will be ignored.
     *
     * @return Model
     */
    public static function fromYAML($yaml, $strict = false)
    {
        $array = Yaml::parse($yaml);
        return self::fromArray($array, $strict);
    }

    /** Inner method used by get(), search() and exists(). */
    private static function getQuerySetForPK(...$pk)
    {
        // Allow passing the PK as an array
        if (count($pk) == 1 && is_array($pk[0])) {
            $pk = array_shift($pk);
        }

        $filter = self::getPkFilter($pk);

        return self::objects()->filter($filter);
    }

    /**
     * Creates a filter by given primary key values.
     *
     * @param  array $values Values to filter by. Same number as PK columns.
     *
     * @return Filter
     */
    public static function getPkFilter(array $values)
    {
        $columns = self::getMeta()->getPkColumns();

        if (empty($columns)) {
            $class = get_called_class();
            throw new OrmException("Primary key not defined for model [$class].");
        }

        if (count($columns) !== count($values)) {
            $format = "Model [%s] has %d primary key columns. %d arguments given.";
            $msg = sprintf($format, get_called_class(), count($columns), count($values));
            throw new OrmException($msg);
        }

        if (count($columns) === 1) {
            return Filter::col(array_shift($columns), '=', array_shift($values));
        }

        $filters = [];
        foreach (array_combine($columns, $values) as $column => $value) {
            $filters[] = Filter::col($column, "=", $value);
        }

        return new CompositeFilter(CompositeFilter::OP_AND, $filters);
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
        $pkColumns = self::getMeta()->getPkColumns();

        if ($pkColumns === null) {
            throw new OrmException("Model not writable because primary key is not defined in _meta.");
        }

        // Check if all primary key columns are populated
        $pkSet = true;
        foreach ($pkColumns as $col) {
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
        $pkColumns = self::getMeta()->getPkColumns();

        if ($pkColumns === null) {
            return [];
        }

        $pk = [];
        foreach ($pkColumns as $column) {
            $pk[$column] = $this->{$column};
        }

        return $pk;
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
            throw new OrmException("Given argument is not an array.");
        }

        foreach ($values as $name => $value) {
            if (property_exists($this, $name)) {
                $this->{$name} = $value;
            } else {
                if ($strict) {
                    $class = get_class($this);
                    throw new OrmException("Property [$name] does not exist in class [$class].");
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
        return Yaml::dump(self::toArray());
    }

    /**
     * Prints the model and it's data in a human readable format.
     */
    public function dump()
    {
        $meta = self::getMeta();
        $database = $meta->getDatabase();
        $table = $meta->getTable();
        $columns = $meta->getColumns();
        $pkColumns = $meta->getPkColumns();

        $name = get_class($this) . " ($database.$table)";

        echo "$name\n";
        echo str_repeat("=", strlen($name)) . "\n";

        foreach ($columns as $column) {
            $value = $this->$column;
            if ($value === null) {
                $value = 'NULL';
            } elseif (is_string($value)) {
                $value = '"' . $value . '"';
            } else {
                $value = (string) $value;
            }

            if (in_array($column, $pkColumns)) {
                $value .= ' (PK)';
            }

            echo "$column: $value\n";
        }
        echo "\n";
    }
}
