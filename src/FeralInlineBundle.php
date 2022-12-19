<?php

namespace Feral\Inline;

use Feral\Core\Process\Catalog\Catalog;
use Feral\Core\Process\Catalog\CatalogSource\CatalogSource;
use Feral\Core\Process\NodeCode\NodeCodeFactory;
use Feral\Core\Process\NodeCode\NodeCodeSource\NodeCodeSource;
use Feral\Core\Process\ProcessFactory;
use Feral\Core\Process\ProcessSource;
use Feral\Core\Process\Reader\DirectoryProcessReader;
use Feral\Core\Process\Validator\ProcessValidator;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;
use function Symfony\Component\DependencyInjection\Loader\Configurator\tagged_iterator;

/**
 * The Nodez inline bundle provides the functionality to use Nodez
 * inside of code in a symfony application. A Nodez process can be
 * triggered from a controller, command, or any driver that runs
 * code with symfony.
 */
class FeralInlineBundle extends AbstractBundle
{
    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->rootNode()
            ->children()
                ->arrayNode('process')
                    ->children()
                        ->scalarNode('configuration_directory')->end()
                        ->arrayNode('included_sources')
                            ->info('Which default sources of node code and catalog nodes should be included.')
                            ->prototype('scalar')
                            ->end()
                    ->end()
                ->end()
            ->end()
        ;
    }

    /**
     * @param array $config
     * @param ContainerConfigurator $container
     * @param ContainerBuilder $builder
     * @return void
     */
    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $container->import('Resources/config/feral-inline-services.yaml');
        $services = $container->services();
        $services->defaults()
            ->autowire()
            ->autoconfigure();

        //'feral.nodecode'

        // INCLUDED SOURCES OF INFORMATION
        if (!empty($config['process']['included_sources'])) {
            if (in_array('tagged_nodecode_source', $config['process']['included_sources'])) {
                $services
                    ->set('nodecode.source.tagged', NodeCodeSource::class)
                    ->public()
                    ->args([tagged_iterator('feral.nodecode')])
                    ->tag('feral.nodecode_source');
            }

            if (in_array('tagged_catalog_source', $config['process']['included_sources'])) {
                $services
                    ->set('catalog.source.tagged', CatalogSource::class)
                    ->public()
                    ->args([tagged_iterator('feral.catalog_node')])
                    ->tag('feral.catalog_source');
            }

            if (in_array('tagged_process_source', $config['process']['included_sources'])) {
                $services
                    ->set('process.source.tagged', ProcessSource::class)
                    ->public()
                    ->args([tagged_iterator('feral.process')])
                    ->tag('feral.process_source');
            }
        }


        // NODE CODE FACTORY
        $services
            ->set(NodeCodeFactory::class, NodeCodeFactory::class)
            ->public()
            ->args([tagged_iterator('feral.nodecode_source')]);

        // CATALOG
        $services
            ->set(Catalog::class, Catalog::class)
            ->public()
            ->args([tagged_iterator('feral.catalog_source')]);

        // PROCESS FACTORY
        $services
            ->set(ProcessFactory::class, ProcessFactory::class)
            ->public()
            ->args([tagged_iterator('feral.process_source')]);

        // PROCESS VALIDATOR
        $services
            ->set(ProcessValidator::class, ProcessValidator::class)
            ->public()
            ->args([tagged_iterator('feral.process_validator')]);


        // IF THE FILE SOURCE IS CONFIGURED, ADD AS A SOURCE
        if (!empty($config['process']['configuration_directory'])) {
            $services
                ->set('process.source.directory', DirectoryProcessReader::class)
                ->public()
                ->args([$config['process']['configuration_directory']])
                ->tag('feral.process_source');
        }
    }
}