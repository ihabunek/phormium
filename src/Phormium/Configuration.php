<?php

namespace Phormium;

use PDO;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Phormium configuration definition.
 *
 * @see http://symfony.com/doc/current/components/config/index.html
 */
class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();

        $rootNode = $treeBuilder->root('')
            ->children()
                ->arrayNode('databases')
                    ->isRequired()
                    ->requiresAtLeastOneElement()
                    ->prototype('array')
                        ->children()
                            ->scalarNode('dsn')
                                ->isRequired()
                                ->cannotBeEmpty()
                            ->end()
                            ->scalarNode('username')
                                ->cannotBeEmpty()
                                ->defaultNull()
                            ->end()
                            ->scalarNode('password')
                                ->defaultNull()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
