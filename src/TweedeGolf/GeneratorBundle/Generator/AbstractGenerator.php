<?php

namespace TweedeGolf\GeneratorBundle\Generator;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\DialogHelper;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use TweedeGolf\GeneratorBundle\Generator\Builder\BuilderInterface;
use TweedeGolf\GeneratorBundle\Generator\Input\InputResult;
use TweedeGolf\GeneratorBundle\Generator\Input\InputTypeInterface;

abstract class AbstractGenerator implements GeneratorInterface, ContainerAwareInterface
{
    const CONFIRMATION_NONE = 0;
    const CONFIRMATION_INTERACTIVE = 1;
    const CONFIRMATION_ALL = 3;

    /**
     * @var BuilderInterface
     */
    protected $builder;

    /**
     * @var HelperSet
     */
    protected $helperSet;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var string
     */
    private $description;

    /**
     * @var array
     */
    private $longDescription;

    /**
     * @var string
     */
    private $name;

    /**
     * @var array
     */
    private $inputs;

    /**
     * @var boolean
     */
    private $confirmation;

    /**
     * @var string
     */
    private $bundle;

    public function __construct()
    {
        $this->inputs = array();
        $this->confirmation = self::CONFIRMATION_NONE;
        $this->configure();
    }

    /**
     * Add confirmation message before generation.
     * @return $this The current instance.
     */
    public function withConfirmation($interactiveOnly = true)
    {
        if ($interactiveOnly) {
            $this->confirmation = self::CONFIRMATION_INTERACTIVE;
        } else {
            $this->confirmation = self::CONFIRMATION_ALL;
        }
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function askConfirmation(InputResult $input, OutputInterface $output)
    {
        if ($this->confirmation === self::CONFIRMATION_NONE) {
            return true;
        }

        $confirmI = self::CONFIRMATION_INTERACTIVE === ($this->confirmation & self::CONFIRMATION_INTERACTIVE);
        $confirmA = self::CONFIRMATION_ALL === ($this->confirmation & self::CONFIRMATION_ALL);

        if (!($input->isInteractive() && $confirmI) && !$confirmA) {
            return true;
        }

        /** @var DialogHelper $dialog */
        $dialog = $this->helperSet->get('dialog');
        return $dialog->askConfirmation(
            $output,
            "<fg=yellow>Ready to generate, continue <fg=cyan>[yes]</fg=cyan>?</fg=yellow> ",
            true
        );
    }


    /**
     * {@inheritdoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function prepareValues(InputResult $result)
    {

    }

    /**
     * Configure the generator inputs.
     */
    abstract public function configure();

    /**
     * Set the name of the generator
     * @param string $name
     * @return $this The current instance
     */
    protected function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Set the description
     * @param string $description
     * @return $this The current instance
     */
    protected function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param array $longDescription
     * @return $this
     */
    protected function setLongDescription(array $longDescription)
    {
        $this->longDescription = $longDescription;
        return $this;
    }

    /**
     * @return array
     */
    public function getLongDescription()
    {
        if ($this->hasLongDescription()) {
            return $this->longDescription;
        }
        return [$this->getDescription()];
    }

    /**
     * {@inheritdoc}
     */
    public function hasLongDescription()
    {
        return is_array($this->longDescription) && count($this->longDescription) > 0;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function add(InputTypeInterface $input)
    {
        $this->inputs[] = $input;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getInputs()
    {
        return $this->inputs;
    }

    /**
     * {@inheritdoc}
     */
    public function getDefinition()
    {
        $definition = array();
        /** @var InputTypeInterface $input */
        foreach ($this->inputs as $input) {
            $definition[] = $input->getDefinition();
        }
        return $definition;
    }

    /**
     * {@inheritdoc}
     */
    public function setBuilder(BuilderInterface $builder)
    {
        $this->builder = $builder;
    }

    /**
     * {@inheritdoc}
     */
    public function setHelperSet(HelperSet $helperSet)
    {
        $this->helperSet = $helperSet;
    }

    /**
     * {@inheritdoc}
     */
    public function getHelperSet()
    {
        return $this->helperSet;
    }

    /**
     * {@inheritdoc}
     */
    public function getBundle()
    {
        return $this->bundle;
    }

    /**
     * Set the bundle for the generator.
     * @param string $bundle
     * @return $this
     */
    public function setBundle($bundle)
    {
        $this->bundle = $bundle;
        return $this;
    }
}
