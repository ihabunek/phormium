<?php

namespace Phormium\Config;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Symfony configuration class which defines the structure and validation of the
 * configuration array.
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();

        $rootNode = $treeBuilder->root('phormium');

        $rootNode
            ->children()
                ->arrayNode('databases')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('dsn')
                                ->isRequired()
                                ->cannotBeEmpty()
                            ->end()
                            ->scalarNode('username')
                                ->defaultNull()
                            ->end()
                            ->scalarNode('password')
                                ->defaultNull()
                            ->end()
                            ->arrayNode('attributes')
                                ->useAttributeAsKey('name')
                                ->prototype('scalar')
                                    ->isRequired()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
