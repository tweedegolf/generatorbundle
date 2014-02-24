<?php

namespace TweedeGolf\GeneratorBundle\Registry;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\HttpKernel\Kernel;
use TweedeGolf\Generator\Registry\GeneratorRegistry;

class SymfonyGeneratorRegistry extends GeneratorRegistry implements ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * {@inheritdoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * Load the generators confirming to default naming rules in all bundles in the given Kernel.
     * @param Kernel $kernel
     */
    public function loadBundleGenerators(Kernel $kernel)
    {
        /** @var Bundle $bundle */
        foreach ($kernel->getBundles() as $bundle) {
            $this->loadGeneratorsForBundle($bundle);
        }
    }

    /**
     * Load the generators confirming to default naming rules in the given bundle.
     * @param Bundle $bundle
     */
    private function loadGeneratorsForBundle(Bundle $bundle)
    {
        $dir = "{$bundle->getPath()}/Generator";
        if (is_dir($dir)) {
            $finder = new Finder();
            $finder->files()->name('*Generator.php')->in($dir);

            $prefix = $bundle->getNamespace() . '\\Generator';

            /** @var SplFileInfo $file */
            foreach ($finder as $file) {
                $this->loadGeneratorInBundle($file, $prefix);
            }
        }
    }

    /**
     * Load a single generator into the registry.
     * @param SplFileInfo $file
     * @param string      $ns
     */
    private function loadGeneratorInBundle(SplFileInfo $file, $ns)
    {
        if ($relativePath = $file->getRelativePath()) {
            $ns .= '\\'.strtr($relativePath, '/', '\\');
        }
        $class = $ns.'\\'.$file->getBasename('.php');

        // if an alias of the generator exists, skip this one
        if ($this->container) {
            $alias = 'tweedegolf_generator.generator.'.strtolower(str_replace('\\', '_', $class));
            if ($this->container->has($alias)) {
                return;
            }
        }

        // add the generator through reflection
        $r = new \ReflectionClass($class);
        if ($r->isSubclassOf('TweedeGolf\\Generator\\GeneratorInterface') &&
            !$r->isAbstract() &&
            !$r->getConstructor()->getNumberOfRequiredParameters()
        ) {
            $this->addGenerator($r->newInstance());
        }
    }
}
