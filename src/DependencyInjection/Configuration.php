<?php

namespace Adlarge\FixturesDocumentationBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This class validates and merges configuration from the app/config files.
 * @codeCoverageIgnore
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('adlarge_fixtures_documentation');
        $treeBuilder->getRootNode()
            ->children()
                ->scalarNode('title')
                    ->defaultValue('Fixtures documentation')
                ->end()
                ->scalarNode('listenedCommand')
                    ->defaultValue('doctrine:fixtures:load')
                ->end()
                ->scalarNode('enableAutoDocumentation')
                    ->defaultValue(false)
                ->end()
                ->scalarNode('fileDest')
                    ->defaultValue(null)
                ->end()
                ->arrayNode('reloadCommands')
                    ->scalarPrototype()->end()
                ->end()
                ->arrayNode('configEntities')
                    ->arrayPrototype()
                        ->scalarPrototype()->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
