<?php

namespace BackupManager\Config;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class AppConfiguration implements ConfigurationInterface
{
    /**
     * @return TreeBuilder
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('root');

        $rootNode
            ->children()
                ->arrayNode('sites')
                    ->prototype('array')
                        ->children()
                            ->arrayNode('database')
                                ->children()
                                    ->scalarNode('host')
                                        ->defaultValue('localhost')
                                    ->end()
                                    ->scalarNode('dbname')->end()
                                    ->scalarNode('user')->end()
                                    ->scalarNode('password')->end()
                                ->end()
                            ->end()
                            ->arrayNode('encryption')
                                ->children()
                                    ->scalarNode('key')->end()
                                    ->scalarNode('cipher')
                                        ->defaultValue('bf')
                                    ->end()
                                ->end()
                            ->end()
                            ->arrayNode('dropbox')
                                ->children()
                                    ->scalarNode('key')
                                        ->isRequired()
                                        ->cannotBeEmpty()
                                    ->end()
                                    ->scalarNode('secret')
                                        ->isRequired()
                                        ->cannotBeEmpty()
                                    ->end()
                                ->end()
                            ->end()
                            ->scalarNode('base_path')->end()
                            ->arrayNode('directories')
                                ->prototype('scalar')->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
