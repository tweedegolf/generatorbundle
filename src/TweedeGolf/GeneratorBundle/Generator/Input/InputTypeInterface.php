<?php

namespace TweedeGolf\GeneratorBundle\Generator\Input;

use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface as ConsoleInputInterface;

interface InputTypeInterface
{
    const MULTIPLE = 1;
    const OPTIONAL = 2;
    const WITHOUT_VALUE = 4;
    const REQUIRED = 8;

    public function getName();

    public function getArgumentName();

    public function getDescription();

    public function getDefinition();

    public function isOptional();

    public function isMultiple();

    public function isRequired();

    public function hasValue();

    public function getDefault();

    public function transform($value);

    public function requestValue(ConsoleInputInterface $input, OutputInterface $output, HelperSet $helperSet);
}
