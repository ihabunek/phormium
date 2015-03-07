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
     *
     * @var array
     */
    protected static $_meta;

    /**
     * Returns the Model's Meta object.
     *
     * @return Meta
     */
    public static function getMeta()
    {
        return static::$_meta;
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

    // ******************************************
    // *** Dynamics                           ***
    // ******************************************

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
