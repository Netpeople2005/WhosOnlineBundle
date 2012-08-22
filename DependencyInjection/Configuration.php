<?php

namespace Netpeople\WhosOnlineBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('whos_online');

        $rootNode->children()
                    ->scalarNode('inactive_in')->defaultValue('5 min')->end()
                    ->scalarNode('offline_in')->defaultValue('30 min')->end()
                    ->scalarNode('clear_in')->defaultValue('2 days')->end()
                ->end();

        return $treeBuilder;
    }
}
