<?php

namespace Tg\GeneratorBundle\Generator\Password;

use Symfony\Component\Security\Core\Util\SecureRandomInterface;
use Tg\OkoaBundle\Util\PathUtil;
use Symfony\Component\HttpKernel\KernelInterface;

class DicewareGenerator extends AbstractPasswordGenerator
{
    private $resource;

    private $languages;

    private $separator;

    public function __construct(SecureRandomInterface $rng, KernelInterface $kernel, array $languages)
    {
        parent::__construct($rng);
        $this->resource = $kernel->locateResource('@TgGeneratorBundle/Resources/wordlists');
        $this->setLanguages($languages);
        $this->setSeparator(' ');
    }

    public function setLanguages(array $languages)
    {
        $this->languages = $languages;
    }

    public function setSeparator($sep)
    {
        $this->separator = $sep;
    }

    public function generate($length)
    {
        $phrase = [];
        for ($i = 0; $i < $length; $i += 1) {
            $lang = $this->chooseRandom($this->languages);
            $file = PathUtil::join($this->resource, $lang . '.list');
            if (is_file($file) && is_readable($file)) {
                $lines = file($file);
                $line = $this->chooseRandom($lines);
                $parts = explode(' ', $line);
                array_shift($parts);
                $phrase[] = $this->chooseRandom($parts);
            }
        }
        return implode($this->separator, $phrase);
    }
}
