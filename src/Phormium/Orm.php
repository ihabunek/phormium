<?php

namespace Phormium;

use Evenement\EventEmitter;

use Phormium\Config\ArrayLoader;
use Phormium\Config\JsonLoader;
use Phormium\Config\YamlLoader;
use Phormium\Config\PostProcessor;
use Phormium\Config\Configuration;
use Phormium\Database\Database;
use Phormium\Database\Factory;

use Pimple\Container;

use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Config\Exception\FileLoaderLoadException;
use Symfony\Component\Config\Loader\DelegatingLoader;
use Symfony\Component\Config\Loader\LoaderResolver;

class Orm extends Container
{
    public function __construct()
    {
        // One or more configurations can be given as constructor arguments
        $this['config.input'] = func_get_args();

        $this['config.loader'] = function() {
            return new DelegatingLoader(new LoaderResolver([
                new ArrayLoader(),
                new JsonLoader(),
                new YamlLoader(),
            ]));
        };

        $this['config.processor'] = function () {
            return new Processor();
        };

        $this['config.tree'] = function () {
            $configuration = new Configuration();
            $builder = $configuration->getConfigTreeBuilder();
            return $builder->buildTree();
        };

        $this['config.postprocessor'] = function () {
            return new PostProcessor();
        };

        $this['config'] = function() {
            // Load all given configurations
            $configs = [];
            foreach ($this['config.input'] as $raw) {
                $configs[] = $this['config.loader']->load($raw);
            }

            // Combine them and validate
            $config = $this['config.processor']->process(
                $this['config.tree'],
                $configs
            );

            // Additional postprocessing to handle
            return $this['config.postprocessor']->processConfig($config);
        };

        // Event emitter
        $this['emitter'] = function () {
            return new EventEmitter();
        };

        // Parser for model metadata
        $this['meta.builder'] = function () {
            return new MetaBuilder();
        };

        // Model metadata cache
        $this['meta.cache'] = function () {
            return new \ArrayObject();
        };

        // Database connection factory
        $this['database.factory'] = function() {
            return new Factory(
                $this['config']['databases'],
                $this['emitter']
            );
        };

        // Database connection manager
        $this['database'] = function() {
            return new Database(
                $this['database.factory'],
                $this['emitter']
            );
        };
    }

    /**
     * Constructs a QuerySet for a given model.
     *
     * @param string $model The name of the model class.
     *
     * @return Phormium\QuerySet
     */
    public function objects($model)
    {
        $meta = $this->getMeta($model);
        $query = $this->getQuery($model);

        return new QuerySet($query, $meta);
    }

    /**
     * Fetches a single record by primary key, throwing an exception if not
     * found.
     *
     * @param string $model The name of the Model class.
     * @param mixed  $ids   The primary key value, either as one or several
     *                      arguments, or as an array of one or several values.
     *
     * @return Model
     */
    public function get($model, ...$pk)
    {
        $pk = $this->normalizePk($model, $pk);

        $instance = $this->qsForPK($model, $pk)->single(true);

        if ($instance === null) {
            $pk = implode(',', $pk);
            throw new \Exception("A record of \"$model\" with primary key [$pk] does not exist.");
        }

        return $instance;
    }

    /**
     * Fetches a single record by primary key, returning NULL if not found.
     *
     * @param string $model The name of the Model class.
     * @param mixed  $ids   The primary key value, either as one or several
     *                      arguments, or as an array of one or several values.
     *
     * @return Model
     */
    public function find($model, ...$pk)
    {
        $pk = $this->normalizePk($model, $pk);

        return $this->qsForPK($model, $pk)->single(true);
    }

    /**
     * Checks whether a record with the given primary key exists.
     *
     * @param string $model The name of the Model class.
     * @param mixed  $ids   The primary key value, either as one or several
     *                      arguments, or as an array of one or several values.
     *
     * @return boolean
     */
    public function exists($model, ...$pk)
    {
        $pk = $this->normalizePk($model, $pk);

        return $this->qsForPK($model, $pk)->exists();
    }

    /**
     * Saves a Model to the database.
     *
     * If it already exists, performs an UPDATE, otherwise an INSERT.
     *
     * This method can be sub-optimal since it may do an additional query to
     * determine if the model exists in the database. If performance is
     * important, use update() and insert() explicitely.
     */
    public function save(Model $model)
    {
        $meta = $this->getMeta($model);

        if (!isset($meta->pk)) {
            throw new \Exception("Model not writable because primary key is not defined in _meta.");
        }

        // Check if all primary key columns are populated
        $pkSet = true;
        foreach ($meta->pk as $col) {
            if (empty($model->{$col})) {
                $pkSet = false;
                break;
            }
        }

        // If primary key is populated, check whether the record with given
        // primary key exists, and update it if it does. Otherwise insert.
        if ($pkSet) {
            $exists = $this->exists(get_class($model), $this->getPK($model));
            if ($exists) {
                $this->update($model);
            } else {
                $this->insert($model);
            }
        } else {
            $this->insert($model);
        }
    }

    /**
     * Performs an INSERT query with the data from the model.
     */
    public function insert(Model $model)
    {
        return $this->getQuery($model)->insert($model);
    }

    /**
     * Performs an UPDATE query with the data from the model.
     *
     * @returns integer The number of affected rows.
     */
    public function update(Model $model)
    {
        return $this->getQuery($model)->update($model);
    }

    /**
     * Performs an DELETE query filtering by model's primary key.
     *
     * @returns integer The number of affected rows.
     */
    public function delete(Model $model)
    {
        return $this->getQuery($model)->delete($model);
    }

    /**
     * Returns the model's primary key value as an associative array.
     *
     * @param Model $model
     *
     * @return array The primary key.
     */
    public function getPK(Model $model)
    {
        $meta = $this->getMeta($model);

        if (!isset($meta->pk)) {
            return [];
        }

        $pk = [];
        foreach ($meta->pk as $column) {
            $pk[$column] = $model->{$column};
        }
        return $pk;
    }


    // -- Helpers --------------------------------------------------------------

    protected function qsForPK($model, array $pk)
    {
        // Create a QuerySet
        $qs = $this->objects($model);

        // Filter it by primary key
        $meta = $this->getMeta($model);
        foreach ($meta->pk as $name) {
            $value = array_shift($pk);
            $qs = $qs->filter($name, '=', $value);
        }

        return $qs;
    }

    protected function normalizePk($model, array $pk)
    {
        $meta = $this->getMeta($model);
        $count = count($pk);

        // Allow passing the PK as an array
        if ($count == 1 && is_array($pk[0])) {
            $pk = $pk[0];
            $count = count($pk);
        }

        // Model must have PK defined
        if (!isset($meta->pk)) {
            throw new \Exception("Primary key not defined for model \"$model\".");
        }

        // Check correct number of columns is given
        $expected = count($meta->pk);
        if ($count !== $expected) {
            throw new \Exception("Model \"$meta->class\" has $expected primary key column(s). $count values given.");
        }

        // Check all PK values are scalars
        foreach ($pk as $value) {
            if (!is_scalar($value)) {
                throw new \Exception("Nonscalar value given for primary key value.");
            }
        }

        return $pk;
    }

    /**
     * Returns the Meta object for a given Model.
     *
     * Results are cached to avoid multiple parsing.
     *
     * @param  mixed $model An instance or class name of a Model .
     * @return Meta         Model's meta
     */
    public function getMeta($model)
    {
        if ($model instanceof Model) {
            $model = get_class($model);
        }

        if (!isset($this['meta.cache'][$model])) {
            $this['meta.cache'][$model] = $this['meta.builder']->build($model);
        }

        return $this['meta.cache'][$model];
    }

    public function getQuery($model)
    {
        $meta = $this->getMeta($model);

        $driver = $this['config']['databases'][$meta->database]['driver'];

        return new Query($meta, $this['database'], $driver);
    }

    public function getEmitter()
    {
        return $this['emitter'];
    }
}
