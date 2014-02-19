<?php

namespace TweedeGolf\GeneratorBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Handle compiler
 */
class BuilderCompilerPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('tweedegolf_generator.builder_registry')) {
            return;
        }

        $definition = $container->getDefinition('tweedegolf_generator.builder_registry');
        $taggedServices = $container->findTaggedServiceIds('tweedegolf_generator.builder');
        foreach ($taggedServices as $id => $tagAttributes) {
            foreach ($tagAttributes as $attributes) {
                $definition->addMethodCall('addBuilder', array($attributes['type'], new Reference($id)));
            }
        }
    }
}
