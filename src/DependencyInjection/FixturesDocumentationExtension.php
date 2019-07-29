<?php

namespace Adlarge\FixturesDocumentationBundle\DependencyInjection;

use Exception;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This class loads and manages the bundle configuration.
 * @codeCoverageIgnore
 */
class FixturesDocumentationExtension extends Extension
{
    /**
     * {@inheritDoc}
     *
     * @throws Exception
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter(
            'adlarge_fixtures_documentation.title',
            $config['title']
        );

        $container->setParameter(
            'adlarge_fixtures_documentation.listenedCommand',
            $config['listenedCommand']
        );

        $container->setParameter(
            'adlarge_fixtures_documentation.enableAutoDocumentation',
            $config['enableAutoDocumentation']
        );

        $container->setParameter(
            'adlarge_fixtures_documentation.reloadCommands',
            $config['reloadCommands']
        );

        $container->setParameter(
            'adlarge_fixtures_documentation.configEntities',
            $config['configEntities']
        );

        $loader = new Loader\YamlFileLoader(
            $container,
            new FileLocator(__DIR__ . '/../Resources/config')
        );
        $loader->load('services.yml');
    }

    public function getAlias(): string
    {
        return 'adlarge_fixtures_documentation';
    }
}
