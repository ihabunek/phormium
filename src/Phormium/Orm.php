<?php

namespace Phormium;

use Phormium\Config\ArrayLoader;
use Phormium\Config\JsonLoader;
use Phormium\Config\YamlLoader;
use Phormium\Config\PostProcessor;
use Phormium\Config\Configuration;

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
            return new Evenement\EventEmitter();
        };

        // Parser for model metadata
        $this['meta.builder'] = function () {
            return new MetaParser();
        };

        // Model metadata cache
        $this['meta.cache'] = function () {
            return new \ArrayObject();
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
        $meta = $this->getModelMeta($model);
        $query = new Query($meta);

        return new QuerySet($query, $meta);
    }

    /**
     * Returns the Meta object for a given Model.
     *
     * Results are cached to avoid multiple parsing.
     *
     * @param  string $model Model class name.
     * @return Meta          Model's meta
     */
    public function getModelMeta($model)
    {
        if (!isset($this['meta.cache'][$model])) {
            $this['meta.cache'][$model] = $this['meta.builder']->buildMeta($model);
        }

        return $this['meta.cache'][$model];
    }
}
