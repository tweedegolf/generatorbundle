<?php

namespace TweedeGolf\GeneratorBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Handle compiler
 */
class GeneratorCompilerPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $this->processGeneratorTags($container);
        $this->processActionTags($container);
        $this->processInputTypeTags($container);
    }

    /**
     * @param ContainerBuilder $container
     */
    private function processGeneratorTags(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('tweedegolf_generator.generator_registry')) {
            return;
        }

        $definition = $container->getDefinition('tweedegolf_generator.generator_registry');
        $taggedServices = $container->findTaggedServiceIds('tweedegolf_generator.generator');
        foreach ($taggedServices as $id => $tagAttributes) {
            foreach ($tagAttributes as $attributes) {
                if (isset($attributes['priority'])) {
                    $definition->addMethodCall('addGenerator', [new Reference($id), $attributes['priority']]);
                } else {
                    $definition->addMethodCall('addGenerator', [new Reference($id)]);
                }
            }
        }
    }

    /**
     * @param ContainerBuilder $container
     */
    private function processActionTags(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('tweedegolf_generator.builder')) {
            return;
        }

        $definition = $container->getDefinition('tweedegolf_generator.builder');
        $taggedServices = $container->findTaggedServiceIds('tweedegolf_generator.action');
        foreach ($taggedServices as $id => $tagAttributes) {
            $definition->addMethodCall('addAction', [new Reference($id)]);
        }
    }

    /**
     * @param ContainerBuilder $container
     */
    private function processInputTypeTags(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('tweedegolf_generator.input_type_registry')) {
            return;
        }

        $definition = $container->getDefinition('tweedegolf_generator.input_type_registry');
        $taggedServices = $container->findTaggedServiceIds('tweedegolf_generator.input_type');
        foreach ($taggedServices as $id => $tagAttributes) {
            foreach ($tagAttributes as $attributes) {
                if (!isset($attributes['alias'])) {
                    throw new InvalidConfigurationException(
                        "Attribute alias was not set for tag 'tweedegolf_generator.input_type'"
                    );
                }

                if (isset($attributes['priority'])) {
                    $definition->addMethodCall('addType', [
                        new Reference($id),
                        $attributes['alias'],
                        $attributes['priority']
                    ]);
                } else {
                    $definition->addMethodCall('addType', [
                        new Reference($id),
                        $attributes['alias']
                    ]);
                }
            }
        }
    }
}
