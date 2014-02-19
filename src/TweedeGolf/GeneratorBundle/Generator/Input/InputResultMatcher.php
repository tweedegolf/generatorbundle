<?php

namespace TweedeGolf\GeneratorBundle\Generator\Input;

use Symfony\Component\Console\Helper\FormatterHelper;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TweedeGolf\GeneratorBundle\Generator\GeneratorInterface;
use TweedeGolf\GeneratorBundle\Exception\InvalidValueException;

class InputResultMatcher
{
    /**
     * Handle the input matching and generate the set of resulting parameters.
     * @param GeneratorInterface $generator
     * @param InputInterface     $input
     * @param OutputInterface    $output
     * @param HelperSet          $helperSet
     * @return InputResult
     */
    public static function handle(
        GeneratorInterface $generator,
        InputInterface $input,
        OutputInterface $output,
        HelperSet $helperSet
    ) {
        $result = new InputResult();
        $foundAll = self::checkArgumentCount($generator, $input);
        if ($foundAll) {
            self::handleArguments($generator, $input, $result);
        } else {
            $result->enableInteractive();
            self::handleInteractive($generator, $input, $output, $helperSet, $result);
        }
        return $result;
    }

    protected static function checkArgumentCount(GeneratorInterface $generator, InputInterface $input)
    {
        $vars = $generator->getInputs();
        $positionals = array_filter($vars, function (InputTypeInterface $var) {
            return !$var->isOptional();
        });
        $found = false;

        if (count($positionals) === 0) {
            return false;
        }

        /** @var InputTypeInterface $var */
        foreach ($positionals as $var) {
            $name = $var->getArgumentName();
            if ($var->isMultiple() && !$var->isRequired() && !$input->hasArgument($name)) {
                $input->setArgument($name, array());
            }

            if (!$input->hasArgument($name) || $input->getArgument($name) === null) {
                if ($found) {
                    throw new \RuntimeException("Not enough arguments");
                } else {
                    break;
                }
            }
            $found = true;
        }
        return $found;
    }

    /**
     * Handle parsing of parameters.
     * @param GeneratorInterface $generator
     * @param InputInterface     $input
     * @param InputResult        $result
     */
    protected static function handleArguments(
        GeneratorInterface $generator,
        InputInterface $input,
        InputResult $result
    ) {
        $vars = $generator->getInputs();

        /** @var InputTypeInterface $var */
        foreach ($vars as $var) {
            if ($var->isOptional()) {
                $values = $input->getOption($var->getArgumentName());
            } else {
                $values = $input->getArgument($var->getArgumentName());
            }

            if (!is_array($values)) {
                if ($var->isOptional() && $values === null) {
                    $result->setValue($var->getName(), null);
                } else {
                    $result->setValue($var->getName(), $var->transform($values));
                }
            } else {
                $transformed = array();
                foreach ($values as $value) {
                    $transformed[] = $var->transform($value);
                }
                $result->setValue($var->getName(), $transformed);
            }
        }
    }

    /**
     * Handle parameters as if they were interactive.
     * @param GeneratorInterface $generator
     * @param InputInterface     $input
     * @param OutputInterface    $output
     * @param InputResult        $result
     */
    protected static function handleInteractive(
        GeneratorInterface $generator,
        InputInterface $input,
        OutputInterface $output,
        HelperSet $helperSet,
        InputResult $result
    ) {
        $vars = $generator->getInputs();

        /** @var FormatterHelper $formatter */
        $formatter = $helperSet->get('formatter');
        $output->writeln($formatter->formatBlock(sprintf(
            "%s generator",
            ucfirst($generator->getName())
        ), 'fg=black;bg=green'));
        $output->writeln($formatter->formatBlock($generator->getLongDescription(), 'fg=green', true));

        /** @var InputTypeInterface $var */
        foreach ($vars as $var) {
            $values = self::getValue($var, $input, $output, $helperSet);
            if ($var->isOptional()) {
                $input->setOption($var->getArgumentName(), $values);
            } else {
                $input->setArgument($var->getArgumentName(), $values);
            }
        }
        $output->writeln("");
        self::handleArguments($generator, $input, $result);
    }

    /**
     * Retrieve value(s) for an input interactively.
     * @param InputTypeInterface $var
     * @param InputInterface     $input
     * @param OutputInterface    $output
     * @param HelperSet          $helperSet
     * @return string|array
     */
    protected static function getValue(
        InputTypeInterface $var,
        InputInterface $input,
        OutputInterface $output,
        HelperSet $helperSet
    ) {
        $output->writeln("");

        /** @var FormatterHelper $formatter */
        $formatter = $helperSet->get('formatter');
        if ($var->isMultiple()) {
            $values = [];
            $last = null;
            while (true) {
                $last = $var->requestValue($input, $output, $helperSet);
                if ($last === null || $last === '') {
                    break;
                }

                try {
                    $var->transform($last);
                    $values[] = $last;
                } catch (InvalidValueException $e) {
                    self::showInvalidError($e, $output, $helperSet);
                }
            }
        } else {
            $values = null;
            while (true) {
                $values = $var->requestValue($input, $output, $helperSet);
                if ($var->isOptional() && ($values === null || $values === '')) {
                    return null;
                }

                try {
                    $var->transform($values);
                    break;
                } catch (InvalidValueException $e) {
                    self::showInvalidError($e, $output, $helperSet);
                }
            }
        }
        return $values;
    }

    public static function showInvalidError(InvalidValueException $e, OutputInterface $output, HelperSet $helperSet)
    {
        /** @var FormatterHelper $formatter */
        $formatter = $helperSet->get('formatter');
        $error = $formatter->formatBlock(array('[Invalid value]', $e->getMessage()), 'error', true);
        $output->writeln($error);
    }
}
