<?php

namespace TweedeGolf\GeneratorBundle\Generator\Input;

use TweedeGolf\GeneratorBundle\Exception\InvalidValueException;

class PhpNamespaceType extends StringType
{
    public function __construct($name, $description, $mode = 0, $default = null)
    {
        parent::__construct($name, $description, $mode, $default);
        $this->addTransformer(function ($value) {
            $value = str_replace('/', '\\', $value);
            $value = trim($value);

            if (preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*(\\\\[a-zA-Z_][a-zA-Z0-9_]*)*$/', $value) !== 1) {
                throw new InvalidValueException(sprintf('Value %s is not a valid namespace.', $value));
            }
            return $value;
        });
    }
}
