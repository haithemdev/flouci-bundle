<?php

namespace Flouci\SymfonyBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('flouci_symfony');

        $treeBuilder->getRootNode()
            ->children()
                ->scalarNode('app_token')->defaultNull()->end()
                ->scalarNode('app_secret')->defaultNull()->end()
                ->scalarNode('api_base_url')->defaultValue('https://developers.flouci.com/api/v2')->end()
                ->arrayNode('accounts')
                    ->useAttributeAsKey('name')
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode('app_token')->isRequired()->cannotBeEmpty()->end()
                            ->scalarNode('app_secret')->isRequired()->cannotBeEmpty()->end()
                            ->scalarNode('api_base_url')->defaultNull()->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
