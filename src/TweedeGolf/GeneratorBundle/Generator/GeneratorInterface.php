<?php

namespace TweedeGolf\GeneratorBundle\Generator;

use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Output\OutputInterface;
use TweedeGolf\GeneratorBundle\Generator\Builder\BuilderInterface;
use TweedeGolf\GeneratorBundle\Generator\Input\InputResult;
use TweedeGolf\GeneratorBundle\Generator\Input\InputTypeInterface;

interface GeneratorInterface
{
    /**
     * Retrieve the name of the generator.
     * @return string
     */
    public function getName();

    /**
     * Retrieve a description of the generator.
     * @return string
     */
    public function getDescription();

    /**
     * Retrieve an array of message lines for the long description.
     * @return array
     */
    public function getLongDescription();

    /**
     * Returns whether or not there is a long description available for the generator.
     * @return boolean
     */
    public function hasLongDescription();

    /**
     * Retrieve the console component definition arguments and options.
     * @return array
     */
    public function getDefinition();

    /**
     * Retrieve an array of InputTypeInterface elements describing the input variables required.
     * @return array
     */
    public function getInputs();

    /**
     * Add an input variable to the generator.
     * @param InputTypeInterface $input
     * @return $this
     */
    public function add(InputTypeInterface $input);

    /**
     * Called before the generator is ran, allows adjusting the values in the input.
     * @param InputResult $result
     */
    public function prepareValues(InputResult $result);

    /**
     * Generate the requested items given the input variables.
     * @param InputResult $input
     */
    public function generate(InputResult $input, OutputInterface $output);

    /**
     * Set the helper set from the command.
     * @param HelperSet $helperSet
     */
    public function setHelperSet(HelperSet $helperSet);

    /**
     * Retrieve the HelperSet.
     * @return HelperSet
     */
    public function getHelperSet();

    /**
     * Set the builder that should be used during generation.
     * @param BuilderInterface $builder
     */
    public function setBuilder(BuilderInterface $builder);

    /**
     * Optionally ask for confirmation, based on the input result.
     * @param InputResult     $input
     * @param OutputInterface $output
     * @return boolean
     */
    public function askConfirmation(InputResult $input, OutputInterface $output);

    /**
     * Retrieve the name of the bundle for this generator.
     * @return string
     */
    public function getBundle();
}
