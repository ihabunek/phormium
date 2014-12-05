<?php

namespace Phormium;

use Phormium\Model;

/**
 * Implements relation forming functions for Models.
 */
trait ModelRelationsTrait
{
    /**
     * Relates this model to multiple instances of another model.
     *
     * Use when the foreign key constraint is defined on the related model. That
     * means that this model is the Parent, and the related model is the Child.
     *
     * @param  string          $child     The related model class name.
     * @param  string|string[] $childKey  Foreign key column(s) in the child
     *                                    model (related class).
     * @param  string|string[] $parentKey Primary key column(s) in the parent
     *                                    model (this class).
     *
     * @return QuerySet
     */
    public function hasChildren($child, $childKey = null, $parentKey = null)
    {
        $parent = get_class($this);

        $this->checkClassIsModel($child);

        // Determine which keys to use
        list($parentKey, $childKey) = $this->determineKeys(
            $parent,
            $child,
            $parentKey,
            $childKey
        );

        // Create a query set
        $querySet = $child::objects();

        // Filter the query set
        $pairs = array_combine($parentKey, $childKey);
        foreach ($pairs as $pkCol => $fkCol) {
            $querySet = $querySet->filter($fkCol, '=', $this->$pkCol);
        }

        return $querySet;
    }

    /**
     * Relates this model to a parent model.
     *
     * Use when a foreign key constraint is defined on this model. That means
     * that this model is the Child, and the related model is the Parent.
     *
     * @param  string          $child     The related model class name.
     * @param  string|string[] $childKey  Foreign key column(s) in the child
     *                                    model (this class).
     * @param  string|string[] $parentKey Primary key column(s) in the parent
     *                                    model (related class).
     *
     * @return QuerySet
     */
    public function hasParent($parent, $childKey = null, $parentKey = null)
    {
        $child = get_class($this);

        $this->checkClassIsModel($parent);

        // Determine which keys to use
        list($parentKey, $childKey) = $this->determineKeys(
            $parent,
            $child,
            $parentKey,
            $childKey
        );

        // Create a query set
        $querySet = $parent::objects();

        // Filter the query set
        $pairs = array_combine($parentKey, $childKey);
        foreach ($pairs as $pkCol => $fkCol) {
            $querySet = $querySet->filter($pkCol, '=', $this->$fkCol);
        }

        return $querySet;
    }

    private function determineKeys($parent, $child, $parentKey = null, $childKey = null)
    {
        if (!isset($childKey)) {
            $childKey = $this->guessForeignKey($parent);
        } else {
            $childKey = $this->processKey($childKey);
        }

        if (!isset($parentKey)) {
            $parentKey = $parent::getMeta()->getPkColumns();
        } else {
            $parentKey = $this->processKey($parentKey);
        }

        if (count($parentKey) !== count($childKey)) {
            throw new \Exception("Primary and foreign key must have the same number of columns.");
        }

        $this->checkClassHasProperties($child, $childKey);
        $this->checkClassHasProperties($parent, $parentKey);

        return [$parentKey, $childKey];
    }

    /**
     * Throws an exception if the given class does not exist or is not a Model.
     */
    private function checkClassIsModel($class)
    {
        if (!class_exists($class)) {
            throw new \Exception("Model class \"$class\" does not exist.");
        }

        if (!is_subclass_of($class, "Phormium\\Model")) {
            throw new \Exception("Given class \"$class\" is not a subclass of Phormium\\Model");
        }
    }

    /**
     * Throws an exception any of the $properties don't exist in the $class.
     */
    private function checkClassHasProperties($class, array $properties)
    {
        foreach ($properties as $property) {
            if (!property_exists($class, $property)) {
                throw new \Exception("Property \"$property\" does not exist in class \"$class\".");
            }
        }
    }

    /**
     * Makes an educated guess at what columns are used by a foreign key.
     *
     * For example, if the referenced model is called MyModel, and has a primary
     * key called "id", then the resulting key will be: ["my_model_id"].
     *
     * Also works on composite keys.
     *
     * @param  string $parent The model which is referenced by the FK.
     *
     * @return string[]
     */
    private function guessForeignKey($parent)
    {
        $parentMeta = $parent::getMeta();

        $table = $parentMeta->getTable();
        $pkColumns = $parentMeta->getPkColumns();

        foreach ($pkColumns as &$column) {
            $column = $this->guessFkColumnName($table, $column);
        }

        return $pkColumns;
    }

    /**
     * TODO: Enable setting this function from outside.
     */
    private function guessFkColumnName($table, $column)
    {
        $table = preg_replace_callback("/[A-Z]/", function ($matches) {
            return "_" . strtolower($matches[0]);
        }, $table);

        return trim($table, "_") . "_" . $column;
    }

    private function processKey($key)
    {
        if (empty($key)) {
            throw new \Exception("Empty key given.");
        }

        if (is_string($key)) {
            return [$key];
        }

        if (is_array($key)) {
            return $key;
        }

        $type = gettype($key);
        throw new \Exception("Invalid key type: \"$type\". Expected string or array.");
    }
}
