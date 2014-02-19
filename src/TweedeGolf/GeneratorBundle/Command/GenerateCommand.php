<?php

namespace TweedeGolf\GeneratorBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\DescriptorHelper;
use Symfony\Component\Console\Helper\TableHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use TweedeGolf\GeneratorBundle\Exception\GenerationAbortedException;
use TweedeGolf\GeneratorBundle\Generator\Builder\Registry\BuilderRegistryInterface;
use TweedeGolf\GeneratorBundle\Generator\GeneratorInterface;
use TweedeGolf\GeneratorBundle\Generator\Input\InputResultMatcher;
use TweedeGolf\GeneratorBundle\Generator\Registry\GeneratorRegistryInterface;

class GenerateCommand extends ContainerAwareCommand
{
    /**
     * @var GeneratorInterface
     */
    private $generator;

    /**
     * @var string
     */
    private $synopsis;

    public function __construct($name = null)
    {
        parent::__construct($name);
        $this->generator = null;
        $this->synopsis = null;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('tg:generate')
            ->setDescription('Generate code for your project')
        ;
        $this->setDefinition($this->getGeneratorArgumentDefinitions());
    }

    /**
     * Retrieve the generator registry.
     * @return GeneratorRegistryInterface
     */
    protected function getGeneratorRegistry()
    {
        return $this->getContainer()->get('tweedegolf_generator.generator_registry');
    }

    /**
     * Retrieve the builder registry.
     * @return BuilderRegistryInterface
     */
    protected function getBuilderRegistry()
    {
        return $this->getContainer()->get('tweedegolf_generator.builder_registry');
    }

    /**
     * {@inheritdoc}
     */
    public function getSynopsis()
    {
        if ($this->synopsis === null) {
            $this->updateSynopsis();
        }
        return $this->synopsis;
    }

    /**
     * Update the synopsis text.
     */
    public function updateSynopsis()
    {
        if ($this->generator) {
            $definition = new InputDefinition();
            $definition->setDefinition($this->generator->getDefinition());
            $synopsis = $definition->getSynopsis();
            $this->synopsis = trim(sprintf('%s %s %s', $this->getName(), $this->generator->getName(), $synopsis));
        } else {
            $this->synopsis = trim(sprintf(
                '%1$s %2$s OR %1$s help %2$s',
                $this->getName(), $this->getDefinition()->getSynopsis()
            ));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function run(InputInterface $input, OutputInterface $output)
    {
        try {
            $this->updateSynopsis();
            return parent::run($input, $output);
        } catch (\RuntimeException $e) {
            if ($e->getMessage() !== 'Too many arguments.') {
                throw $e;
            }
            $generator = $input->getArgument('generator');
            if (!is_string($generator)) {
                throw $e;
            }
            return $this->restartWithGenerator($generator, $input, $output);
        }
    }

    /**
     * Restart the command with a generator specified. If the generator is 'help', try to display a help message
     * about a generator.
     * @param string          $generator
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @return int
     */
    private function restartWithGenerator($generator, InputInterface $input, OutputInterface $output)
    {
        if ($generator === 'help') {
            $this->ignoreValidationErrors();
            $this->setDefinition(array_merge(
                $this->getGeneratorArgumentDefinitions(true),
                array(new InputArgument('help', InputArgument::REQUIRED, 'Retrieve help'))
            ));
        } else {
            $registry = $this->getGeneratorRegistry();
            $generator = $registry->getGenerator($generator);
            $definition = $generator->getDefinition();
            $this->addGenerateDefinitions($definition);
            $this->generator = $generator;
            $this->updateSynopsis();
        }
        return parent::run($input, $output);
    }

    /**
     * Retrieve the InputArgument for the generator parameter of the command.
     * @param bool $required
     * @return InputArgument
     */
    protected function getGeneratorArgumentDefinitions($required = false)
    {
        return array(
            new InputArgument(
                'generator',
                $required ? InputArgument::REQUIRED : InputArgument::OPTIONAL,
                'The generator to use'
            ),
            new InputOption('pretend', null, InputOption::VALUE_NONE, "Do not actually execute the generator")
        );
    }

    /**
     * Update the definition of the command and prepend the generator argument definition.
     * @param array $definition
     */
    protected function addGenerateDefinitions(array $definition)
    {
        $definition = array_merge($this->getGeneratorArgumentDefinitions(true), $definition);
        $this->setDefinition($definition);
    }

    /**
     * Show the help message for an individual generator.
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @return int
     */
    protected function showGeneratorHelp(InputInterface $input, OutputInterface $output)
    {
        $generator = $input->getArgument('help');
        $registry = $this->getGeneratorRegistry();
        $generator = $registry->getGenerator($generator);
        $definition = new InputDefinition($generator->getDefinition());

        $output->writeln("<comment>Generator:</comment> <info>{$generator->getName()}</info>");
        $output->writeln(" {$generator->getDescription()}");
        $output->writeln("");

        $output->writeln("<comment>Usage:</comment>");
        $output->writeln(" {$this->getName()} {$generator->getName()} {$definition->getSynopsis()}");
        $output->writeln("");

        $descriptor = new DescriptorHelper();
        $descriptor->describe($output, $definition);

        if ($generator->hasLongDescription()) {
            $output->writeln("");
            foreach ($generator->getLongDescription() as $line) {
                $output->writeln($line);
            }
        }
        return -2;
    }

    /**
     * {@inheritdoc}
     * @throws GenerationAbortedException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $generator = $input->getArgument('generator');
        if ($generator === 'help') {
            return $this->showGeneratorHelp($input, $output);
        }

        if ($generator === null) {
            return $this->showHelp($output);
        }

        if (!$this->generator) {
            return $this->restartWithGenerator($generator, $input, $output);
        }

        $registry = $this->getGeneratorRegistry();
        $generator = $registry->getGenerator($generator);
        $generator->setHelperSet($this->getHelperSet());

        $result = InputResultMatcher::handle($generator, $input, $output, $this->getHelperSet());
        $generator->prepareValues($result);

        $doGeneration = true;
        if ($input->getOption('no-interaction') === false) {
            $doGeneration = $generator->askConfirmation($result, $output);
        }
        if ($doGeneration) {
            $builder = $this->getBuilderRegistry()->getBuilder('create', $result, $generator);
            if ($input->getOption('pretend')) {
                $builder->pretend();
            }
            $builder->setOutput($output);
            $generator->setBuilder($builder);
            $result = $generator->generate($result, $output);
            $builder->finish();
            if ($result === null || $result) {
                return 0;
            } else {
                return 1;
            }
        } else {
            throw new GenerationAbortedException("Manual abort of generator.");
        }
    }

    /**
     * Help message for showing all available generators.
     * @param OutputInterface $output
     */
    protected function showHelp(OutputInterface $output)
    {
        $output->writeln("<comment>Available generators:</comment>");
        $output->writeln(
            "Use <info>{$this->getName()} help [generator]</info> for more information on each generator."
        );
        $registry = $this->getGeneratorRegistry();
        $rows = [];

        /** @var GeneratorInterface $generator */
        foreach ($registry->getGenerators() as $generator) {
            $rows[] = array("<info>{$generator->getName()}</info>", $generator->getDescription());
        }

        /** @var TableHelper $table */
        $table = $this->getHelper('table');
        $table->setLayout(TableHelper::LAYOUT_BORDERLESS);
        $table->setHorizontalBorderChar('');
        $table->setRows($rows);
        $table->render($output);
        return -1;
    }
}
