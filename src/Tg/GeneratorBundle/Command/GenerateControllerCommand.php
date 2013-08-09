<?php

namespace Tg\GeneratorBundle\Command;

use Sensio\Bundle\GeneratorBundle\Command\GenerateControllerCommand as BaseGenerateControllerCommand;
use Sensio\Bundle\GeneratorBundle\Generator\ControllerGenerator;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;

class GenerateControllerCommand extends BaseGenerateControllerCommand
{
    /**
     * {@inheritDoc}
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);
    }

    protected function getSkeletonDirs(BundleInterface $bundle = null)
    {
        return [
            __DIR__ . '/../Resources/skeleton',
        ];
    }
}
