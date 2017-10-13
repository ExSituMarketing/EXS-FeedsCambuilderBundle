<?php

namespace EXS\FeedsCambuilderBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/configuration.html}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('exs_feeds_cambuilder');

        $rootNode
            ->children()
                ->scalarNode('cache_ttl')
                    ->defaultValue(300)
                ->end()
                ->scalarNode('memcached_host')
                    ->defaultValue('localhost')
                ->end()
                ->scalarNode('memcached_port')
                    ->defaultValue(11211)
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
