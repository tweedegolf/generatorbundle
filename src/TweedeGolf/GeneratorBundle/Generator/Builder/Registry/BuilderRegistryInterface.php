<?php

namespace TweedeGolf\GeneratorBundle\Generator\Builder\Registry;

use TweedeGolf\GeneratorBundle\Generator\Builder\BuilderInterface;
use TweedeGolf\GeneratorBundle\Generator\GeneratorInterface;
use TweedeGolf\GeneratorBundle\Generator\Input\InputResult;

interface BuilderRegistryInterface
{
    /**
     * Retrieve a builder from the registry.
     * @param string             $type      The name of the builder.
     * @param InputResult        $vars      The variables available for the builder.
     * @param GeneratorInterface $generator The generator that will use the builder.
     * @return BuilderInterface
     */
    public function getBuilder($type, InputResult $vars, GeneratorInterface $generator);

    /**
     * Add a builder to the registry.
     * @param string           $type    The name of the builder.
     * @param BuilderInterface $builder The builder itself.
     */
    public function addBuilder($type, BuilderInterface $builder);

    /**
     * Returns whether or not a builder of the given type exists.
     * @param string $type The name of a builder.
     * @return boolean
     */
    public function hasBuilder($type);
}
