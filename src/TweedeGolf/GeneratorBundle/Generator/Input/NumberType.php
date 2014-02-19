<?php

namespace TweedeGolf\GeneratorBundle\Generator\Input;

use Symfony\Component\Console\Input\InputInterface as ConsoleInputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TweedeGolf\GeneratorBundle\Exception\InvalidValueException;

class NumberType extends CallbackInputType
{
    public function __construct($name, $description, $mode = 0, $default = null)
    {
        parent::__construct($name, $description, $mode, $default);
        $this->setTransformer(array($this, 'transformInput'));
    }

    protected function transformInput($value)
    {
        if (is_string($value) && strlen($value) > 0) {
            if ($value[0] === '-' && ctype_digit(substr($value, 1)) || ctype_digit($value)) {
                $value = (int) $value;
            }
        }

        if (is_int($value)) {
            return $value;
        } else {
            throw new InvalidValueException("Input is not a number");
        }
    }

    public function requestValue(ConsoleInputInterface $input, OutputInterface $output)
    {
        return $this->prompt($this->getDescription(), ucfirst($this->getName()), $input, $output);
    }
}
