<?php

namespace Tg\OkoaGeneratorBundle\Command;

use Sensio\Bundle\GeneratorBundle\Command\GenerateBundleCommand as BaseGenerateBundleCommand;
use Sensio\Bundle\GeneratorBundle\Generator\BundleGenerator as BaseBundleGenerator;
use Tg\OkoaGeneratorBundle\Generator\BundleGenerator;

class GenerateBundleCommand extends BaseGenerateBundleCommand
{
    private $gen;

    public function getGenerator()
    {
        if (null === $this->gen) {
            $this->gen = new BundleGenerator($this->getContainer()->get('filesystem'), __DIR__.'/../Resources/skeleton/bundle');
        }
        return $this->gen;
    }

    public function setGenerator(BaseBundleGenerator $generator)
    {
        $this->gen = $generator;
    }
}
