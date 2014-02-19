<?php

namespace TweedeGolf\GeneratorBundle\Generator\Registry;

use TweedeGolf\GeneratorBundle\Exception\GeneratorNotFoundException;
use TweedeGolf\GeneratorBundle\Generator\GeneratorInterface;

class GeneratorRegistry implements GeneratorRegistryInterface
{
    /**
     * List of generators registered
     * @var array
     */
    private $generators;

    private $priorities;

    public function __construct()
    {
        $this->generators = array();
        $this->priorities = array();
    }

    /**
     * {@inheritdoc}
     */
    public function getGenerators()
    {
        return $this->generators;
    }

    /**
     * {@inheritdoc}
     */
    public function getGenerator($name)
    {
        if ($this->hasGenerator($name)) {
            return $this->generators[$name];
        }
        throw new GeneratorNotFoundException("The generator '{$name}' could not be found.");
    }

    /**
     * {@inheritdoc}
     */
    public function hasGenerator($name)
    {
        return isset($this->generators[$name]);
    }

    /**
     * {@inheritdoc}
     */
    public function addGenerator(GeneratorInterface $generator, $priority = 1)
    {
        $name = $generator->getName();
        if (!$this->hasGenerator($name) || $this->priorities[$name] < $priority) {
            $this->generators[$name] = $generator;
            $this->priorities[$name] = $priority;
        }
    }
}
