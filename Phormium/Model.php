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

    protected static $query;

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

    public static function getQuery()
    {
        if (!isset(self::$query)) {
            self::$query = new Query(self::getMeta());
        }
        return self::$query;
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
        $query = self::getQuery();
        return $query->insert($this);
    }

    public function update()
    {
        $query = self::getQuery();
        return $query->update($this);
    }

    public function toJSON()
    {
        return json_encode($this);
    }
}
