<?php

namespace Tg\GeneratorBundle\Command;

use Sensio\Bundle\GeneratorBundle\Command\GenerateDoctrineEntityCommand as BaseGenerateDoctrineEntityCommand;
use Sensio\Bundle\GeneratorBundle\Generator\DoctrineEntityGenerator as BaseDoctrineEntityGenerator;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Tg\GeneratorBundle\Generator\DoctrineEntityGenerator;

class GenerateDoctrineEntityCommand extends BaseGenerateDoctrineEntityCommand
{
    /**
     * {@inheritDoc}
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->setGenerator(new DoctrineEntityGenerator(
            $this->getContainer()->get('filesystem'),
            $this->getContainer()->get('doctrine')
        ));
        parent::execute($input, $output);
    }
}
