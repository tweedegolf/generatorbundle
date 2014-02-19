<?php

namespace TweedeGolf\GeneratorBundle\DependencyInjection;

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
        if (!$container->hasDefinition('tweedegolf_generator.generator_registry')) {
            return;
        }

        $definition = $container->getDefinition('tweedegolf_generator.generator_registry');
        $taggedServices = $container->findTaggedServiceIds('tweedegolf_generator.generator');
        foreach ($taggedServices as $id => $tagAttributes) {
            foreach ($tagAttributes as $attributes) {
                if (isset($attributes['priority'])) {
                    $definition->addMethodCall('addGenerator', array(new Reference($id), $attributes['priority']));
                } else {
                    $definition->addMethodCall('addGenerator', array(new Reference($id)));
                }
            }
        }
    }
}
