<?php

namespace Tg\GeneratorBundle\Command;

use Sensio\Bundle\GeneratorBundle\Command\GenerateBundleCommand as BaseGenerateBundleCommand;
use Sensio\Bundle\GeneratorBundle\Generator\BundleGenerator as BaseBundleGenerator;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Tg\GeneratorBundle\Generator\BundleGenerator;

class GenerateBundleCommand extends BaseGenerateBundleCommand
{
    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->setGenerator(new BundleGenerator($this->getContainer()->get('filesystem')));
        $this->getGenerator()->setSkeletonDirs($this->getSkeletonDirs());
        parent::execute($input, $output);
    }

    protected function getSkeletonDirs($bundle = null)
    {
        return [
            __DIR__ . '/../Resources/skeleton',
        ];
    }
}
