<?php

namespace Tg\GeneratorBundle\Generator\Password;

interface PasswordGenerator
{
    public function generate($length);
}
