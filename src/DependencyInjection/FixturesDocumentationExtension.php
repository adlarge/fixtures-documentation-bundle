<?php

namespace FixturesDocumentation\DependencyInjection;

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
            'fixtures_documentation.title',
            $config['title']
        );
        $container->setParameter(
            'fixtures_documentation.reloadCommands',
            $config['reloadCommands']
        );

        $loader = new Loader\YamlFileLoader(
            $container,
            new FileLocator(__DIR__ . '/../Resources/config')
        );
        $loader->load('services.yml');
    }
}
