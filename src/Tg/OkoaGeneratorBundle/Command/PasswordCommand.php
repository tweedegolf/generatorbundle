<?php

namespace Tg\OkoaGeneratorBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class PasswordCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('generate:password')
            ->setDescription('Generate a random password')
            ->addOption(
               'pronouncable',
               'p',
               InputOption::VALUE_NONE,
               'If set, a pronouncable password will be generated'
            )
            ->addOption(
               'ask-salt',
               null,
               InputOption::VALUE_NONE,
               'If set, you will be asked to enter a salt manually'
            )
            ->addOption(
                'no-dashes',
                'd',
                InputOption::VALUE_NONE,
                'If set, does not add dashes to a pronouncable password'
            )
            ->addOption(
                'encode',
                'c',
                InputOption::VALUE_NONE,
                'If set, also generate an encoded hash'
            )
            ->addOption(
                'alphanumeric',
                'a',
                InputOption::VALUE_NONE,
                'If set, only use alphanumeric characters'
            )
            ->addArgument(
                'size',
                InputArgument::OPTIONAL,
                'Size of the password to generate'
            )
        ;
    }

    protected function getRandomGenerator()
    {
        return $this->getContainer()->get('security.secure_random');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $size = $input->getArgument('size');
        if ($size === null) {
            $size = 20;
        } else {
            $size = (int)$size;
        }

        if ($size < 1) {
            throw new RuntimeException("Password size must be at least 1");
        }
        if ($input->getOption('pronouncable')) {
            $binary = $this->getContainer()->getParameter('kernel.root_dir') . '/../bin/passogva.py';
            if ($input->getOption('no-dashes')) {
                $dashes = '-n';
            } else {
                $dashes = '-d';
            }
            $password = exec($binary . ' ' . $dashes . ' ' . $size);
            $this->showPassword($input, $output, $password);
        } else {
            if ($input->getOption('alphanumeric')) {
                $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
            } else {
                $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890!@#$%^&*()-+_=[]{};:\\|,.<>/?';
            }
            $nchars = strlen($chars);
            $bytes = str_split($this->getRandomGenerator()->nextBytes($size));

            $password = '';
            foreach ($bytes as $byte) {
                $n = ord($byte);
                $n = (int)floor($n * ($nchars / 256));
                $password .= $chars[$n];
            }
            $this->showPassword($input, $output, $password);
        }
    }

    protected function showPassword(InputInterface $input, OutputInterface $output, $password)
    {
        if ($input->getOption('encode')) {
            $hashCommand = $this->getApplication()->find('generate:password:hash');
            $inputArgs = [
                'password' => $password,
            ];
            if ($input->getOption('ask-salt')) {
                $inputArgs['--ask-salt'] = true;
            }
            $input = new ArrayInput($inputArgs);
            $hashCommand->run($input, $output);
            // $this->encodePassword($input, $output, $password);
        }
        $output->writeln("<info>Generated password:</info>");
        $output->writeln($password);
    }
}
