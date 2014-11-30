<?php

namespace Phormium;

trait ModelRelationsTrait
{
    public function hasOne($model, $key)
    {
        if (!property_exists($this, $key)) {
            $class = static::class;
            throw new \Exception("Property \"$key\" does not exist in $class.");
        }

        if (!is_subclass_of($model, Model::class)) {
            throw new \Exception("Given class $model is not a subclass of " . Model::class);
        }

        $pk = $this->getPK();

        foreach ($pk as $col => $value) {
            if (is_null($value)) {

            }
        }

        if (empty($pk)) {
            throw new \Exception("Cannot fetch related $model, property \"$key\" is empty." . Model::class);
        }

        return call_user_func_array([$model, 'find'], [$this->{$key}]);
    }

    public function hasMany($model, $key)
    {
        if (!property_exists($model, $key)) {
            throw new \Exception("Property \"$key\" does not exist in $model");
        }

        if (!is_subclass_of($model, self::class)) {
            throw new \Exception("Given class $model is not a subclass of " . self::class);
        }

        $pk = $this->getPK()->id;

        return call_user_func_array([$model, 'find'], [$pk]);
    }
}