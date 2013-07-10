<?php

namespace Tg\GeneratorBundle\Command;

use InvalidArgumentException;
use ReflectionClass;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class HashPasswordCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('generate:password:hash')
            ->setDescription('Generate the hash for a password')
            ->addOption(
               'ask-salt',
               'a',
               InputOption::VALUE_NONE,
               'If set, you will be asked to enter a salt manually'
            )
            ->addOption(
               'check-password',
               'c',
               InputOption::VALUE_NONE,
               'If set, the password will be asked twice for verification'
            )
            ->addArgument(
                'password',
                InputArgument::OPTIONAL,
                'The password to hash'
            )
        ;
    }

    protected function getEncoderFactory()
    {
        return $this->getContainer()->get('security.encoder_factory');
    }

    protected function getEncoders()
    {
        $factory = $this->getEncoderFactory();
        $reflector = new ReflectionClass($factory);
        $property = $reflector->getProperty('encoders');
        $property->setAccessible(true);
        $encoders = $property->getValue($factory);
        return $encoders;
    }

    protected function getSecureRandom()
    {
        return $this->getContainer()->get('security.secure_random');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $encoders = $this->getEncoders();
        $output->writeln("<info>Available encoders:</info>");
        $i = 1;
        foreach ($encoders as $class => $properties) {
            $encoder = explode('\\', $properties['class']);
            $encoder = $encoder[count($encoder) - 1];
            $encoder = str_replace('PasswordEncoder', '', $encoder);
            $output->writeln("<info>$i.</info> $encoder ($class)");
            $i += 1;
        }

        $dialog = $this->getHelperSet()->get('dialog');
        $encoder = $dialog->askAndValidate(
            $output,
            "<question>Which encoder would you like to use?</question> ",
            function ($answer) use ($i) {
                if (!ctype_digit($answer) || (int)$answer < 1 || (int)$answer >= $i) {
                    throw new RuntimeException("Pick an encoder between 1 and $i.");
                }
                return (int)$answer;
            }
        );

        $encoder = array_keys($encoders)[$encoder - 1];
        $this->askPassword($input, $output, $encoder, $encoders[$encoder]);
    }

    protected function createEncoder(array $config)
    {
        if (!isset($config['class'])) {
            throw new InvalidArgumentException(sprintf('"class" must be set in %s.', json_encode($config)));
        }
        if (!isset($config['arguments'])) {
            throw new InvalidArgumentException(sprintf('"arguments" must be set in %s.', json_encode($config)));
        }

        $reflection = new ReflectionClass($config['class']);

        return $reflection->newInstanceArgs($config['arguments']);
    }

    protected function askPassword(InputInterface $input, OutputInterface $output, $encoder, $properties)
    {
        $encoder = $this->createEncoder($properties);
        $dialog = $this->getHelperSet()->get('dialog');
        $verified = true;
        do {
            if ($verified === false) {
                $output->writeln("<error>Passwords are not the same.</error>");
            }

            if ($input->getArgument('password')) {
                $password = $input->getArgument('password');
            } else {
                $password = $dialog->askHiddenResponse(
                    $output,
                    "<question>Password:</question> "
                );

                if ($input->getOption('check-password')) {
                    $repeat = $dialog->askHiddenResponse(
                        $output,
                        "<question>Repeat password:</question> "
                    );
                    $verified = $repeat === $password;
                }
            }
        } while ($verified === false);

        if ($input->getOption('ask-salt')) {
            $salt = $dialog->ask(
                $output,
                "<question>Salt:</question> "
            );
        } else {
            $salt = hash('sha256', $this->getSecureRandom()->nextBytes(32));
            $output->writeln("<info>A salt was generated for you:</info>");
            $output->writeln($salt);
        }
        $output->writeln("<info>The hashed password:</info>");
        $output->writeln($encoder->encodePassword($password, 'blurp'));
    }
}
