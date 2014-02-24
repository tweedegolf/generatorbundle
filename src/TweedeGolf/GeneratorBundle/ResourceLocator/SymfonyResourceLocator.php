<?php

namespace TweedeGolf\GeneratorBundle\ResourceLocator;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\HttpKernel\Kernel;
use TweedeGolf\Generator\Exception\ResourceNotFoundException;
use TweedeGolf\Generator\GeneratorInterface;
use TweedeGolf\Generator\ResourceLocator\ResourceLocatorInterface;

class SymfonyResourceLocator implements ResourceLocatorInterface
{
    /**
     * @var Kernel
     */
    private $kernel;

    public function __construct(Kernel $kernel)
    {
        $this->kernel = $kernel;
    }

    /**
     * {@inheritdoc}
     */
    public function locate($resource, GeneratorInterface $generator)
    {
        $bundles = $this->kernel->getBundles();
        $class = get_class($generator);

        $appResources = $this->kernel->getContainer()->getParameter('kernel.root_dir') . '/Resources/';
        $location = "skeleton/{$generator->getName()}/{$resource}";

        /** @var Bundle $bundle */
        foreach ($bundles as $bundle) {
            if (strpos($class, $bundle->getNamespace()) !== false) {
                $global = "{$appResources}/{$bundle->getName()}/{$location}";
                if (file_exists($global) && is_readable($global)) {
                    return $global;
                } else {
                    $local = "{$bundle->getPath()}/Resources/{$location}";
                    if (file_exists($local) && is_readable($local)) {
                        return $local;
                    } else {
                        throw new ResourceNotFoundException(
                            "Resource {$resource} could not be located for generator {$generator->getName()}"
                        );
                    }
                }
            }
        }

        $nonBundle = "{$appResources}{$location}";
        if (file_exists($nonBundle) && is_readable($nonBundle)) {
            return $nonBundle;
        }

        throw new ResourceNotFoundException(
            "Resource {$resource} could not be located for generator {$generator->getName()}"
        );
    }
}
