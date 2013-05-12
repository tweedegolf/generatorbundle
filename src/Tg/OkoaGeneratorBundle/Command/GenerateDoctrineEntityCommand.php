<?php

namespace Tg\OkoaGeneratorBundle\Command;

use Sensio\Bundle\GeneratorBundle\Command\GenerateDoctrineEntityCommand as BaseGenerateDoctrineEntityCommand;
use Sensio\Bundle\GeneratorBundle\Generator\DoctrineEntityGenerator as BaseDoctrineEntityGenerator;
use Tg\OkoaGeneratorBundle\Generator\DoctrineEntityGenerator;

class GenerateDoctrineEntityCommand extends BaseGenerateDoctrineEntityCommand
{
    private $gen;

    public function getGenerator()
    {
        if (null === $this->gen) {
            $this->gen = new DoctrineEntityGenerator($this->getContainer()->get('filesystem'), $this->getContainer()->get('doctrine'));
        }
        return $this->gen;
    }

    public function setGenerator(BaseDoctrineEntityGenerator $generator)
    {
        $this->gen = $generator;
    }
}
