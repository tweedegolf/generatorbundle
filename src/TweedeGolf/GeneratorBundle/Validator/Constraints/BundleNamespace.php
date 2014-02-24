<?php

namespace TweedeGolf\GeneratorBundle\Validator\Constraints;

class BundleNamespace extends IsNamespace
{
    public $message = "The string %string% is not a valid bundle namespace.";
}
