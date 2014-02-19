<?php

namespace TweedeGolf\GeneratorBundle\Generator\Input;

use TweedeGolf\GeneratorBundle\Exception\DefinitionException;
use TweedeGolf\GeneratorBundle\Exception\InvalidValueException;

class StringType extends CallbackInputType
{
    public function __construct($name, $description, $mode = 0, $default = null)
    {
        parent::__construct($name, $description, $mode, $default);
        $this->setTransformer(function ($value) use ($name) {
            if (strlen($value) === 0) {
                throw new InvalidValueException(sprintf("Empty string for %s", $name));
            }
            return $value;
        });
    }
}
