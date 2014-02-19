<?php

namespace TweedeGolf\GeneratorBundle\Generator\Input;

use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Input\InputInterface as ConsoleInputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TweedeGolf\GeneratorBundle\Exception\InvalidValueException;

class BooleanType extends CallbackInputType
{
    public function __construct($name, $description, $enabledDefault = false)
    {
        parent::__construct(
            $name,
            $description,
            InputTypeInterface::OPTIONAL | InputTypeInterface::WITHOUT_VALUE
        );
        $this->setTransformer(array($this, 'transformInput'));
    }

    protected function transformInput($value)
    {
        if (is_bool($value)) {
            return $value;
        }

        $value = strtolower($value);
        if (in_array($value, array('yes', 'y', 'true'))) {
            return true;
        }
        if (in_array($value, array('no', 'n', 'false'))) {
            return false;
        }
        throw new InvalidValueException("Value is not a valid boolean");
    }

    public function getDefault()
    {
        return 'no';
    }


    public function requestValue(ConsoleInputInterface $input, OutputInterface $output, HelperSet $helperSet)
    {
        return parent::requestValue($input, $output, $helperSet);
    }
}
