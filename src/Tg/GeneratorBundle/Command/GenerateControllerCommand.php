<?php

namespace Tg\GeneratorBundle\Command;

use Sensio\Bundle\GeneratorBundle\Command\GenerateControllerCommand as BaseGenerateControllerCommand;
use Sensio\Bundle\GeneratorBundle\Generator\ControllerGenerator;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateControllerCommand extends BaseGenerateControllerCommand
{
    /**
     * {@inheritDoc}
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->setGenerator(new ControllerGenerator(
            $this->getContainer()->get('filesystem'),
            __DIR__ . '/../Resources/skeleton/controller'
        ));
        parent::execute($input, $output);
    }
}
