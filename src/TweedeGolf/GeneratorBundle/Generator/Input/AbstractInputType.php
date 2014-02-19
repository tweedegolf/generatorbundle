<?php

namespace TweedeGolf\GeneratorBundle\Generator\Input;

use Symfony\Component\Console\Helper\DialogHelper;
use Symfony\Component\Console\Helper\FormatterHelper;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface as ConsoleInputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use TweedeGolf\GeneratorBundle\Exception\DefinitionException;

abstract class AbstractInputType implements InputTypeInterface
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $argumentName;

    /**
     * @var string
     */
    private $description;

    /**
     * @var integer
     */
    private $mode;

    /**
     * @var mixed
     */
    private $default;

    /**
     * @param string  $name
     * @param string  $description
     * @param integer $mode
     * @param mixed   $default
     * @throws \TweedeGolf\GeneratorBundle\Exception\DefinitionException
     */
    public function __construct($name, $description, $mode = 0, $default = null)
    {
        $this->setMode($mode);
        $this->setName($name);
        $this->setDescription($description);
        $this->setDefault($default);
    }

    public function setArgumentName($name)
    {
        $this->argumentName = $name;
    }

    /**
     * {@inheritdoc}
     */
    public function getArgumentName()
    {
        if ($this->argumentName) {
            return $this->argumentName;
        } else {
            return $this->getName();
        }
    }

    public function getDefinition()
    {
        if ($this->isOptional()) {
            $mode = 0;
            if ($this->isMultiple()) {
                $mode = $mode | InputOption::VALUE_IS_ARRAY;
            }

            if ($this->hasValue()) {
                $mode = $mode | InputOption::VALUE_REQUIRED;
                $definition = new InputOption(
                    $this->getArgumentName(),
                    null,
                    $mode,
                    $this->getDescription(),
                    $this->getDefault()
                );
            } else {
                $mode = $mode | InputOption::VALUE_NONE;
                $definition = new InputOption($this->getArgumentName(), null, $mode, $this->getDescription());
            }

        } else {
            $mode = InputArgument::OPTIONAL;
            if ($this->isMultiple()) {
                $mode = $mode | InputArgument::IS_ARRAY;
            }
            $definition = new InputArgument($this->getArgumentName(), $mode, $this->getDescription());
        }
        return $definition;
    }

    public function isOptional()
    {
        return InputTypeInterface::OPTIONAL === (InputTypeInterface::OPTIONAL & $this->mode);
    }

    public function isMultiple()
    {
        return InputTypeInterface::MULTIPLE === (InputTypeInterface::MULTIPLE & $this->mode);
    }

    public function isRequired()
    {
        return InputTypeInterface::REQUIRED === (InputTypeInterface::REQUIRED & $this->mode);
    }

    public function hasValue()
    {
        return InputTypeInterface::WITHOUT_VALUE !== (InputTypeInterface::WITHOUT_VALUE & $this->mode);
    }

    /**
     * @param string $name
     * @return $this The current instance.
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $description
     * @return $this The current instance.
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param int $mode
     * @return $this The current instance.
     */
    public function setMode($mode)
    {
        $oldMode = $this->mode;
        $this->mode = $mode;
        if (!$this->isOptional() && !$this->hasValue()) {
            throw new DefinitionException("Only parameters set as optional may have no value");
        }
        if ($this->isOptional() && $this->isMultiple() && !$this->hasValue()) {
            throw new DefinitionException("Repeatedly defined optional parameters must have values");
        }
        return $this;
    }

    /**
     * @return int
     */
    public function getMode()
    {
        return $this->mode;
    }

    /**
     * @param mixed $default
     * @return $this The current instance.
     * @throws \TweedeGolf\GeneratorBundle\Exception\DefinitionException
     */
    public function setDefault($default)
    {
        if (!$this->isOptional() && !$this->isMultiple() && $default !== null) {
            throw new DefinitionException("Only single optional items can have default values");
        } else {
            $this->default = $default;
        }
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDefault()
    {
        return $this->default;
    }

    protected function getReadableName()
    {
        return ucfirst(str_replace('-', ' ', $this->getName()));
    }

    public function requestValue(ConsoleInputInterface $input, OutputInterface $output, HelperSet $helperSet)
    {
        if ($this->isOptional()) {
            $prompt = $this->getReadableName() . ' (optional)';
        } else {
            $prompt = $this->getReadableName();
        }


        return $this->prompt($this->getDescription(), $prompt, $output, $helperSet, $this->getDefault());
    }


    /**
     * @param string|array|null     $description
     * @param string                $prompt
     * @param ConsoleInputInterface $input
     * @param OutputInterface       $output
     * @param array|callback        $options
     * @return string
     */
    protected function prompt(
        $description,
        $prompt,
        OutputInterface $output,
        HelperSet $helperSet,
        $default = null,
        array $options = null,
        $secret = false
    ) {
        if (is_string($description)) {
            $description = array($description);
        }

        /** @var FormatterHelper $formatter */
        $formatter = $helperSet->get('formatter');

        /** @var DialogHelper $dialog */
        $dialog = $helperSet->get('dialog');

        $output->writeln($formatter->formatBlock($description, 'fg=green', false));
        $defaultPrompt = '';
        if ($default !== null && is_string($default)) {
            $defaultPrompt = sprintf(' <%1$s>[%2$s]</%1$s>', 'fg=cyan', $default);
        }

        $prompt = sprintf('<%1$s>%2$s%3$s:</%1$s> ', 'fg=yellow', $prompt, $defaultPrompt);
        if ($secret) {
            return $dialog->askHiddenResponse($output, $prompt);
        } else {
            if (is_array($options) && count($options) > 0) {
                return $dialog->ask($output, $prompt, $default, $options);
            }
            return $dialog->ask($output, $prompt, $default);
        }
    }
}
