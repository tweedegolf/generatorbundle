<?php

namespace TweedeGolf\GeneratorBundle\Generator;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Validator\Constraints;
use TweedeGolf\Generator\AbstractGenerator;
use TweedeGolf\Generator\Builder\BuilderInterface;
use TweedeGolf\Generator\Console\Questioner;
use TweedeGolf\Generator\Dispatcher\GeneratorDispatcherInterface;
use TweedeGolf\Generator\Input\Arguments;
use TweedeGolf\GeneratorBundle\Validator\Constraints as GeneratorConstraints;

class BundleGenerator extends AbstractGenerator
{
    /**
     * {@inheritdoc}
     */
    public function configure()
    {
        $this
            ->setName('bundle')
            ->setDescription('Generate a bundle')
            ->addOption('no-update-kernel', null, InputOption::VALUE_NONE, 'Do not update the kernel file')
            ->addOption('no-update-routing', null, InputOption::VALUE_NONE, 'Do not update the routing file')
            ->addOption('structure', null, InputOption::VALUE_NONE, 'Generate some default directories')
            ->addOption('name', null, InputOption::VALUE_REQUIRED, 'Name of the bundle')
            ->addOption('folder', null, InputOption::VALUE_REQUIRED, 'Folder in which to generate the bundle', 'src/')
            ->addArgument('namespace', InputArgument::REQUIRED)
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getConstraints()
    {
        return [
            'namespace' => [
                new Constraints\NotBlank(),
                new Constraints\Type(['type' => 'string']),
                new GeneratorConstraints\BundleNamespace(),
            ],
            'name' => [
                new Constraints\NotBlank(),
                new Constraints\Type(['type' => 'string']),
                new GeneratorConstraints\BundleName(),
            ],
            'folder' => [
                new Constraints\NotBlank(),
                new Constraints\Type(['type' => 'string']),
                new GeneratorConstraints\Path(),
            ],
            'update-kernel' => [
                new Constraints\Type(['type' => 'bool']),
            ],
            'update-routing' => [
                new Constraints\Type(['type' => 'bool']),
            ],
            'structure' => [
                new Constraints\Type(['type' => 'bool']),
            ],
        ];
    }

    /**
     * Retrieve a standard namespace name.
     * @param string $namespace
     * @return string
     */
    private function getNameForNamespace($namespace)
    {
        return str_replace(['/', '\\'], '', $namespace);
    }

    /**
     * {@inheritdoc}
     */
    public function before(Arguments $arguments)
    {
        if (!isset($arguments['update-kernel'])) {
            $arguments['update-kernel'] = !$arguments->get('no-update-kernel', false);
        }

        if (!isset($arguments['update-routing'])) {
            $arguments['update-routing'] = !$arguments->get('no-update-routing', false);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function interact(Arguments $arguments, Questioner $questioner)
    {
        $questioner->update($arguments, 'namespace', 'namespace');

        $questioner->update($arguments, 'name', 'string', [
            'default' => $this->getNameForNamespace($arguments['namespace']),
            'description' => ''
        ]);

        $questioner->update($arguments, 'update-kernel', 'boolean', [
            'force' => true,
        ]);
        $questioner->update($arguments, 'update-routing', 'boolean', [
            'force' => true,
        ]);

        $questioner->update($arguments, 'structure', 'boolean', [
            'force' => true,
            'prompt' => 'Generate bundle structure?'
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function beforeValidate(Arguments $arguments)
    {
        $arguments['namespace'] = str_replace('/', '\\', $arguments['namespace']);

        if (!isset($arguments['name']) && isset($arguments['namespace'])) {
            $arguments['name'] = $this->getNameForNamespace($arguments['namespace']);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function beforeGenerate(Arguments $arguments)
    {
        $folder = $arguments['folder'];
        $last = strlen($folder) - 1;
        if ($folder[$last] !== '/' && $folder[$last] !== '\\') {
            $folder .= '/';
        }

        $arguments['dir'] = $folder . str_replace('\\', '/', $arguments['namespace']) . '/';
        $arguments['basename'] = substr($arguments['name'], 0, -6);
    }

    /**
     * {@inheritdoc}
     */
    public function generate(Arguments $arguments, BuilderInterface $builder, GeneratorDispatcherInterface $dispatcher)
    {
        $builder->mkdir($arguments['dir']);
        $builder->in($arguments['dir'], function (BuilderInterface $builder) use ($arguments) {
            $builder->template('Bundle.php.twig', "{$arguments->name}.php");
            $builder->in('DependencyInjection', function (BuilderInterface $builder) use ($arguments) {
                $builder->template('Configuration.php.twig', 'Configuration.php');
                $builder->template('Extension.php.twig', "{$arguments['basename']}Extension.php");
            });

            $builder->in('Resources/config', function (BuilderInterface $builder) use ($arguments) {
                $builder->template('services.yml.twig', 'services.yml');
            });

            $builder->mkdir('Controller');
            $builder->mkdir('Resources/views');

            if ($arguments['structure']) {
                $builder->mkdir('Resources/public');
                $builder->mkdir('Resources/doc');
                $builder->mkdir('Resources/js');
                $builder->mkdir('Resources/translations');
                $builder->touch('Resources/translations/messages.nl.po');
                $builder->mkdir('Tests');
                $builder->mkdir('Entity');
                $builder->mkdir('Form');
            }
        });

        if ($arguments['update-kernel']) {
            // TODO: update app/AppKernel.php
        }

        if ($arguments['update-routing']) {
            // TODO: update app/config/routing.yml
        }
    }
}
