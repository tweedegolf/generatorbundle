<?php

namespace TweedeGolf\GeneratorBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use TweedeGolf\GeneratorBundle\DependencyInjection\BuilderCompilerPass;
use TweedeGolf\GeneratorBundle\DependencyInjection\GeneratorCompilerPass;

/**
 * Okoa generator bundle
 */
class TweedeGolfGeneratorBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new GeneratorCompilerPass());
        $container->addCompilerPass(new BuilderCompilerPass());
    }
}
