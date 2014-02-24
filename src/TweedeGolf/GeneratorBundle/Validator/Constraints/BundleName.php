<?php

namespace TweedeGolf\GeneratorBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

class BundleName extends Constraint
{
    public $message = "The string %string% is not a valid bundle name.";
}
