<?php

namespace TweedeGolf\GeneratorBundle\Generator;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Validator\Constraints;
use TweedeGolf\Generator\AbstractGenerator;
use TweedeGolf\Generator\Builder\BuilderInterface;
use TweedeGolf\Generator\Console\Questioner;
use TweedeGolf\Generator\Dispatcher\GeneratorDispatcherInterface;
use TweedeGolf\Generator\Exception\InteractionNotSupportedException;
use TweedeGolf\Generator\Input\Arguments;
use TweedeGolf\GeneratorBundle\Validator\Constraints as GeneratorConstraints;

class ControllerGenerator extends AbstractGenerator implements ContainerAwareInterface
{
    const ACTION_POSTFIX = 'Action';

    const CONTROLLER_POSTFIX = 'Controller';

    private $container;

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
    public function configure()
    {
        $this
            ->setName('controller')
            ->setDescription('Generate a Symfony2 controller with annotations')
            ->addOption(
                'crud',
                null,
                InputOption::VALUE_NONE,
                'Create crud (index, edit, update, new, create, delete) actions'
            )
            ->addArgument('bundle-controller', InputArgument::REQUIRED, 'Bundle and controller name')
            ->addArgument('actions', InputArgument::IS_ARRAY, 'Actions to be generated')
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function before(Arguments $arguments)
    {
        $bundleController = $arguments->get('bundle-controller', null);
        $bundle = null;
        $controller = null;

        if (is_string($bundleController) && strpos($bundleController, ':') !== false) {
            list($bundle, $controller) = explode(':', $bundleController, 2);
            $controller = $this->getNameForNamespace($controller);
        }

        $arguments['bundle'] = $bundle;
        $arguments['controller'] = $controller;
    }

    /**
     * {@inheritdoc}
     */
    public function getConstraints()
    {
        return [
            'crud' => [
                new Constraints\Type(['type' => 'bool']),
            ],
            'bundle' => [
                new Constraints\NotBlank(),
                new Constraints\Type(['type' => 'string']),
                new GeneratorConstraints\BundleName(),
            ],
            'controller' => [
                new Constraints\NotBlank(),
                new Constraints\Type(['type' => 'string']),
                new GeneratorConstraints\IsNamespace(),
            ]
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function interact(Arguments $arguments, Questioner $questioner)
    {
        throw new InteractionNotSupportedException();
    }

    /**
     * {@inheritdoc}
     */
    public function beforeValidate(Arguments $arguments)
    {
        $controller = $arguments['controller'];
        $cLen = strlen(self::CONTROLLER_POSTFIX);
        if (strlen($controller) < $cLen || substr($controller, -$cLen) !== self::CONTROLLER_POSTFIX) {
            $controller .= self::CONTROLLER_POSTFIX;
        }

        if ($arguments['crud']) {
            $arguments['actions'] = array_merge(
                $arguments['actions'],
                ['index', 'new', 'create', 'edit', 'update', 'delete']
            );
        }

        $methodPostfix = self::ACTION_POSTFIX;
        $mLen = strlen($methodPostfix);

        // add suffix to actions
        $arguments['actions'] = array_map(function ($elem) use ($methodPostfix, $mLen) {
            if (strlen($elem) <= $mLen || substr($elem, -$mLen) !== $methodPostfix) {
                $elem .= $methodPostfix;
            }
            return $elem;
        }, $arguments['actions']);

        // remove duplicates
        $arguments['actions'] = array_unique($arguments['actions']);
    }

    /**
     * {@inheritdoc}
     */
    public function generate(Arguments $arguments, BuilderInterface $builder, GeneratorDispatcherInterface $dispatcher)
    {

        // TODO: Implement generate() method.
        var_dump($arguments);
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
}
