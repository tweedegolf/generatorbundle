<?php

namespace TweedeGolf\GeneratorBundle\Generator\Registry;

use TweedeGolf\GeneratorBundle\Generator\GeneratorInterface;

interface GeneratorRegistryInterface
{
    /**
     * Retrieve a list of generators.
     * @return array
     */
    public function getGenerators();

    /**
     * Retrieve the generator with the specified name.
     * @param string $name
     * @return GeneratorInterface
     */
    public function getGenerator($name);

    /**
     * Return true if a generator with the given name exists.
     * @param string $name
     * @return boolean
     */
    public function hasGenerator($name);

    /**
     * Add the given generator to the list of available generators.
     * @param GeneratorInterface $generator
     * @param int                $priority
     */
    public function addGenerator(GeneratorInterface $generator, $priority = 1);
}
