<?php

namespace TweedeGolf\GeneratorBundle\Generator\Input;

use TweedeGolf\GeneratorBundle\Exception\InvalidValueException;

class NegatedBooleanType extends BooleanType
{
    public function __construct($name, $description, $enabledDefault = true)
    {
        parent::__construct($name, $description, InputTypeInterface::OPTIONAL, $enabledDefault ? 'yes' : 'no');
        $this->setTransformer(array($this, 'transformInput'));
    }

    public function getArgumentName()
    {
        return 'no-' . parent::getName();
    }

    public function getDefault()
    {
        return 'yes';
    }

    public function transformInput($value)
    {
        if ($value === false) {
            return true;
        }

        if ($value === true) {
            return false;
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
}
