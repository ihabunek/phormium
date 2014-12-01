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
     * Use when the foreign key constraint is defined on the related model.
     *
     * @param  string          $model  The related model class name.
     * @param  string|string[] $foreignKey Foreign key column(s) in related model.
     * @param  string|string[] $primaryKey Primary key column(s) in this model.
     *
     * @return QuerySet
     */
    public function hasMany($relatedClass, $foreignKey = null, $primaryKey = null)
    {
        $this->checkClassIsModel($relatedClass);

        if (!isset($foreignKey)) {
            $foreignKey = $this->guessForeignKey(get_class($this));
        } else {
            $foreignKey = $this->processKey($foreignKey);
        }

        if (!isset($primaryKey)) {
            $primaryKey = static::getMeta()->getPkColumns();
        } else {
            $primaryKey = $this->processKey($primaryKey);
        }

        if (count($primaryKey) !== count($foreignKey)) {
            throw new \Exception("Primary and foreign key must have the same number of columns.");
        }

        $this->checkClassHasProperties($relatedClass, $foreignKey);
        $this->checkClassHasProperties(get_class($this), $primaryKey);

        // Create a query set
        $querySet = $relatedClass::objects();

        // Filter the query set
        $pairs = array_combine($primaryKey, $foreignKey);
        foreach ($pairs as $pkCol => $fkCol) {
            $querySet = $querySet->filter($fkCol, '=', $this->$pkCol);
        }

        return $querySet;
    }

    public function belongsTo($relatedClass, $foreignKey = null, $primaryKey = null)
    {
        if (!isset($foreignKey)) {
            $foreignKey = $this->guessForeignKey($relatedClass);
        } else {
            $foreignKey = $this->processKey($foreignKey);
        }

        if (!isset($primaryKey)) {
            $primaryKey = $relatedClass::getMeta()->getPkColumns();
        } else {
            $primaryKey = $this->processKey($primaryKey);
        }

        $this->checkClassHasProperties($relatedClass, $primaryKey);
        $this->checkClassHasProperties(get_class($this), $foreignKey);

        // Create a query set
        $querySet = $relatedClass::objects();

        // Filter the query set
        $pairs = array_combine($primaryKey, $foreignKey);
        foreach ($pairs as $pkCol => $fkCol) {
            $querySet = $querySet->filter($pkCol, '=', $this->$fkCol);
        }

        return $querySet;
    }

    /**
     * Throws an exception if the given class does not exist or is not a Model.
     */
    private function checkClassIsModel($class)
    {
        if (!class_exists($class)) {
            throw new \Exception("Model class \"$class\" does not exist.");
        }

        if (!is_subclass_of($class, Model::class)) {
            throw new \Exception("Given class \"$class\" is not a subclass of " . Model::class);
        }
    }

    /**
     * Throws an exception any of the $properties don't exist in the $class.
     */
    private function checkClassHasProperties($class, array $properties)
    {
        foreach ($properties as $property) {
            if(!property_exists($class, $property)) {
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
     * @param  string $referencedClass The model which is referenced by the FK.
     *
     * @return string[]
     */
    private function guessForeignKey($referencedClass)
    {
        $referencedMeta = $referencedClass::getMeta();

        $table = $referencedMeta->getTable();
        $key = $referencedMeta->getPkColumns();

        foreach ($key as &$column) {
            $column = $this->guessFkColumnName($table, $column);
        }

        return $key;
    }

    /**
     * TODO: Enable setting this function from outside.
     */
    private function guessFkColumnName($table, $column)
    {
        $table = preg_replace_callback("/[A-Z]/", function($matches) {
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

        throw new \Exception("Invalid key given for relation.");
    }
}
