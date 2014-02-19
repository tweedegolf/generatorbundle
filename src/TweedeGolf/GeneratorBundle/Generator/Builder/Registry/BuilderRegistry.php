<?php

namespace TweedeGolf\GeneratorBundle\Generator\Builder\Registry;

use TweedeGolf\GeneratorBundle\Exception\BuilderNotFoundException;
use TweedeGolf\GeneratorBundle\Generator\Builder\BuilderInterface;
use TweedeGolf\GeneratorBundle\Generator\GeneratorInterface;
use TweedeGolf\GeneratorBundle\Generator\Input\InputResult;

class BuilderRegistry implements BuilderRegistryInterface
{
    private $builders;

    public function __construct()
    {
        $this->builders = array();
    }

    /**
     * {@inheritdoc}
     */
    public function getBuilder($type, InputResult $vars, GeneratorInterface $generator)
    {
        if ($this->hasBuilder($type)) {
            /** @var BuilderInterface $builder */
            $builder = clone $this->builders[$type];
            $builder->setInput($vars);
            $builder->setGenerator($generator);
            return $builder;
        }
        throw new BuilderNotFoundException("Could not find builder with name '{$type}'.");
    }

    /**
     * {@inheritdoc}
     */
    public function addBuilder($type, BuilderInterface $builder)
    {
        $this->builders[$type] = $builder;
    }

    /**
     * {@inheritdoc}
     */
    public function hasBuilder($type)
    {
        return isset($this->builders[$type]);
    }
}
