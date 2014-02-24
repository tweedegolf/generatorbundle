<?php

namespace TweedeGolf\GeneratorBundle\Generator;

use Symfony\Component\Validator\Constraint;
use TweedeGolf\Generator\AbstractGenerator;
use TweedeGolf\Generator\Builder\BuilderInterface;
use TweedeGolf\Generator\Console\Questioner;
use TweedeGolf\Generator\Dispatcher\GeneratorDispatcherInterface;
use TweedeGolf\Generator\Input\Arguments;

class EntityGenerator extends AbstractGenerator
{
    /**
     * {@inheritdoc}
     */
    public function configure()
    {
        $this
            ->setName('entity')
            ->setDescription('Generate a Doctrine ORM entity')
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function generate(Arguments $arguments, BuilderInterface $builder, GeneratorDispatcherInterface $dispatcher)
    {
        // TODO: Implement generate() method.
    }

    /**
     * Interact with the user to retrieve the rest of the arguments.
     * @param Arguments  $arguments Arguments already known.
     * @param Questioner $questioner
     */
    public function interact(Arguments $arguments, Questioner $questioner)
    {
        // TODO: Implement interact() method.
    }

    /**
     * Return the constraints for the arguments of the generator.
     * @return Constraint[]
     */
    public function getConstraints()
    {
        // TODO: Implement getConstraints() method.
    }
}
