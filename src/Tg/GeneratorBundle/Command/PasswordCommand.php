<?php

namespace Tg\GeneratorBundle\Command;

use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Tg\GeneratorBundle\Generator\Password;

class PasswordCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('generate:password')
            ->setDescription('Generate a random password')
            ->addOption(
               'ask-salt',
               null,
               InputOption::VALUE_NONE,
               'If set, you will be asked to enter a salt manually'
            )
            ->addOption(
                'method',
                'm',
                InputOption::VALUE_REQUIRED,
                'Method to be used for password generation: random, alphanumeric, pronouncable, diceware'
            )
            ->addOption(
                'divider',
                'd',
                InputOption::VALUE_REQUIRED,
                'Divider character for some password generation schemes',
                '-'
            )
            ->addOption(
                'lang',
                'l',
                InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                'Language to be used for diceware scheme',
                ['english']
            )
            ->addOption(
                'encode',
                'c',
                InputOption::VALUE_NONE,
                'If set, also generate an encoded hash'
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
        $dialog = $this->getHelperSet()->get('dialog');
        $method = $input->getOption('method');
        if ($method === null) {
            $available = [
                'random' => "Random string of characters",
                'alphanumeric' => "Random string consisting only of alphanumeric characters",
                'pronouncable' => "A pronouncable string (slower)",
                'diceware' => "Randomly selects words from a wordlist"
            ];
            $method = $dialog->select(
                $output,
                '<question>Please pick a valid method:</question>',
                $available,
                0,
                false,
                'Please pick a valid method'
            );
        }
        switch ($method) {
            case 'random':
                $generator = new Password\GarbageGenerator($this->getRandomGenerator(), Password\GarbageGenerator::ALL);
                break;
            case 'alphanumeric':
                $generator = new Password\GarbageGenerator($this->getRandomGenerator(), Password\GarbageGenerator::ALNUM);
                break;
            case 'pronouncable':
                $generator = new Password\Fips181Generator($this->getRandomGenerator());
                $generator->setSeparator($input->getOption('divider'));
                break;
            case 'diceware':
                $generator = new Password\DicewareGenerator($this->getRandomGenerator(), $this->getContainer()->get('kernel'), $input->getOption('lang'));
                $generator->setSeparator($input->getOption('divider'));
                break;
        }
        $size = $input->getArgument('size');
        if ($size === null) {
            $size = 20;
        } else {
            $size = (int)$size;
        }

        $password = $generator->generate($size);
        $this->showPassword($input, $output, $password);
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
        }
        $output->writeln("<info>Generated password:</info>");
        $output->writeln($password);
    }
}
