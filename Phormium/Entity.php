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
        // TODO
    }

    public function toJSON()
    {
        return json_encode($this);
    }
}
