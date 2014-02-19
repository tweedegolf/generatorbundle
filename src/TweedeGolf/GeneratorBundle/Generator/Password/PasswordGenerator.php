<?php

namespace TweedeGolf\GeneratorBundle\Generator\Password;

interface PasswordGenerator
{
    public function generate($length);
}
