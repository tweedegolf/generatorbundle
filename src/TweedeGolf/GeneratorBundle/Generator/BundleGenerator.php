<?php

namespace TweedeGolf\GeneratorBundle\Generator;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\DependencyInjection\Container;
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
            ->addArgument('namespace', InputArgument::REQUIRED, 'Namespace of the bundle')
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
        $questioner->messageBlock(
            'This is the interactive version of the bundle generator. This generator will generate the basic ' .
            'scaffolding for your bundle, containing a Bundle class with related Extension and Configuration ' .
            'classes. An empty service configuration will also be generated. If indicated, some of the basic ' .
            'structure of the bundle can be generated.',
            null,
            'bg=green;fg=black',
            true
        );
        $questioner->writeln();

        // lets start interacting
        $questioner->update($arguments, 'namespace', 'string', [
            'modify' => function ($value) {
                return str_replace('/', '\\', $value);
            },
            'description' =>
                'The namespace of the bundle that is about to be created. Bundle namespaces should have at least ' .
                'a vendor prefix. The namespace must end with `Bundle` in order to be considered valid.' . "\n\n" .
                'An example correct namespace is: `Acme\\ExampleBundle`. You may optionally use the slash `/` ' .
                'character instead of the backslash `\\` as a namespace separator.'
        ]);

        $questioner->update($arguments, 'name', 'string', [
            'default' => $this->getNameForNamespace($arguments['namespace']),
            'description' =>
                'The name of the bundle, by default this is the namespace with the separator symbols removed. ' .
                'Note that the bundle name has to end with `Bundle`.'
        ]);

        $questioner->update($arguments, 'folder', 'string', [
            'description' =>
                'The source folder in which the bundle should be generated. This path is relative to the root of ' .
                'the application.'
        ]);

        $questioner->update($arguments, 'update-kernel', 'boolean', [
            'force' => true,
            'description' =>
                'This generator can update app/AppKernel.php automatically to include the newly generated bundle. ' .
                'Note that for this function to work, the AppKernel needs to look relatively similar to the default ' .
                'kernel coding style.'
        ]);
        $questioner->update($arguments, 'update-routing', 'boolean', [
            'force' => true,
            'description' =>
                'This generator can automatically add the routing of this bundle to the application routing file. ' .
                'The new bundle will be prepended to the routing file.'
        ]);

        $questioner->update($arguments, 'structure', 'boolean', [
            'force' => true,
            'description' =>
                'Some additional directories and standard files can be generated. These are some default folders for ' .
                'often used components in a bundle. Mainly this generates some resource folders, ' .
                'entity and controller directories.',
            'prompt' => 'Generate bundle structure'
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
        $arguments['alias'] = Container::underscore($arguments['basename']);
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
                $builder->mkdir('Resources/doc');
                $builder->mkdir('Resources/translations');
                $builder->touch('Resources/translations/messages.nl.po');
                $builder->mkdir('Entity');
                $builder->mkdir('Form');
            }
        });

        if ($arguments['update-kernel']) {
            $bundleClass = "{$arguments['namespace']}\\{$arguments['name']}";
            $builder->modify('app/AppKernel.php', function ($content) use ($bundleClass) {
                $count = preg_match("#([\t ]+)// project bundles\n#", $content, $matches, PREG_OFFSET_CAPTURE);
                if ($count === 1) {
                    $match = $matches[0];
                    $at = strlen($match[0]) + $match[1];

                    $spaces = $matches[1][0];
                    $content = substr($content, 0, $at) . "{$spaces}new {$bundleClass}(),\n" . substr($content, $at);
                }
                return $content;
            });
        }

        if ($arguments['update-routing']) {
            $builder->prepend('app/config/routing.yml', "{$arguments['alias']}:
    resource: \"@{$arguments['name']}/Controller/\"
    type:     annotation
    prefix:   /\n\n"
            );
        }
    }
}
