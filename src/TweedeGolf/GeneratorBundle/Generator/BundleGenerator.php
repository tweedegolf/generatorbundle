<?php

namespace TweedeGolf\GeneratorBundle\Generator;

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Container;
use TweedeGolf\GeneratorBundle\Exception\InvalidValueException;
use TweedeGolf\GeneratorBundle\Generator\Builder\BuilderInterface;
use TweedeGolf\GeneratorBundle\Generator\Input;
use TweedeGolf\GeneratorBundle\Generator\Input\InputResult;

class BundleGenerator extends AbstractGenerator
{
    private function getNamespaceDefinition()
    {
        $bundle = new Input\PhpNamespaceType('namespace', 'The namespace of the bundle.');
        $bundle->addTransformer(function ($value) {
            if (substr_count($value, '\\') < 1) {
                throw new InvalidValueException(
                    "Namespace '{$value}' does not comply with 'Vendor\\NameBundle' format."
                );
            }

            if (substr($value, -6) !== 'Bundle') {
                throw new InvalidValueException("Namespace '{$value}' must end with 'Bundle'.");
            }
            return $value;
        });
        return $bundle;
    }

    private function getBundleNameDefinition()
    {
        $name = new Input\StringType('name', 'Bundle class name', Input\InputTypeInterface::OPTIONAL);
        $name->addTransformer(function ($value) {
            if (substr($value, -6) !== 'Bundle') {
                throw new InvalidValueException("Bundle name '{$value}' must end with 'Bundle'.");
            }
        });
        return $name;
    }

    private function getFolderDefinition()
    {
        $folder = new Input\StringType('folder', 'Folder for code generation', Input\InputTypeInterface::OPTIONAL, 'src/');
        $folder->addTransformer(function ($value) {
            $value = str_replace('\\', '/', $value);
            if ($value[0] === '/') {
                $value = substr($value, 1);
            }

            if ($value[strlen($value) - 1] !== '/') {
                $value .= '/';
            }
            return $value;
        });
        return $folder;
    }

    public function configure()
    {
        $this
            ->setName('bundle')
            ->setDescription('Generate a bundle')
            ->withConfirmation()
            ->add($this->getNamespaceDefinition())
            ->add($this->getBundleNameDefinition())
            ->add($this->getFolderDefinition())
            ->add(new Input\NegatedBooleanType('update-kernel', 'Whether or not to update the kernel file.'))
            ->add(new Input\NegatedBooleanType('update-routing', 'Whether or not to update the routing file.'))
            ->add(new Input\BooleanType('structure', 'Whether or not to generate a complete bundle structure.'))
        ;
    }

    public function prepareValues(InputResult $input)
    {
        if (!$input->hasValue('name')) {
            $input->name = str_replace('\\', '', $input->namespace);
        }

        $input->root = $this->container->getParameter('kernel.root_dir') . '/../';
        $input->basedir = $input->root . $input->folder;
        $input['kernel-file'] = $this->container->getParameter('kernel.root_dir') . '/AppKernel.php';
        $input['routing-file'] = $this->container->getParameter('kernel.root_dir') . '/config/routing.yml';

        $input->basename = substr($input->name, 0, -6);
        $input['alias'] = Container::underscore($input->basename);

        $input->dir = $input->basedir . str_replace('\\', '/', $input->namespace) . '/';
    }

    public function generate(InputResult $input, OutputInterface $output)
    {
        $this->builder->mkdir($input->dir);
        $this->builder->in($input->dir, function (BuilderInterface $builder) use ($input) {
            $builder->template('Bundle.php.twig', "{$input->name}.php");
            $builder->in('DependencyInjection', function (BuilderInterface $builder) use ($input) {
                $builder->template('Configuration.php.twig', 'Configuration.php');
                $builder->template('Extension.php.twig', "{$input->basename}Extension.php");
            });

            $builder->in('Resources/config', function (BuilderInterface $builder) use ($input) {
                 $builder->template('services.yml.twig', 'services.yml');
            });

            $builder->mkdir('Controller');
            $builder->mkdir('Resources/views');

            if ($input->structure) {
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

        if ($input['update-kernel']) {
            $this->builder->modify('app/AppKernel.php')
                ->after('Symfony\\')
                ->prepend('Blaat\\');
        }
    }
}
