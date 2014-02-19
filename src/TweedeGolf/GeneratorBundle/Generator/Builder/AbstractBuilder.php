<?php

namespace TweedeGolf\GeneratorBundle\Generator\Builder;

use Symfony\Component\Console\Helper\DialogHelper;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Templating\EngineInterface;
use TweedeGolf\GeneratorBundle\Generator\GeneratorInterface;
use TweedeGolf\GeneratorBundle\Generator\Input\InputResult;

abstract class AbstractBuilder implements BuilderInterface
{
    /**
     * @var string
     */
    protected $path;

    /**
     * @var InputResult
     */
    protected $vars;

    /**
     * @var OutputInterface
     */
    protected $output;

    /**
     * @var int
     */
    protected $indentation;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var GeneratorInterface
     */
    protected $generator;

    /**
     * @var KernelInterface
     */
    protected $kernel;

    /**
     * @var boolean
     */
    private $pretendAction;

    public function __construct()
    {
        $this->pretendAction = false;
    }

    /**
     * {@inheritdoc}
     */
    public function setInput(InputResult $vars)
    {
        $this->vars = $vars;
    }

    /**
     * {@inheritdoc}
     */
    public function setBasePath($path)
    {
        $this->path = str_replace('\\', '/', $path);
        if ($this->path[strlen($this->path) - 1] !== '/') {
            $this->path .= '/';
        }
    }

    /**
     * Add the base path for the given location if it is not an absolute path.
     * @param string $location
     * @return string
     */
    protected function joinPath($location)
    {
        $location = str_replace('\\', '/', $location);
        if ($location[0] === '/') {
            return $location;
        } else {
            return $this->path . $location;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setOutput(OutputInterface $output)
    {
        $this->output = $output;
    }

    /**
     * Show that a particular action is being executed.
     * @param string $action
     * @param string $file
     * @param int $indentation
     */
    public function showAction($action, $file = null, $argument = null)
    {
        if ($this->output) {
            $indent = str_repeat(' ', $this->indentation ? $this->indentation * 2 : 0);
            if ($file !== null) {
                if (strlen($file) >= strlen($this->path) && substr($file, 0, strlen($this->path)) === $this->path) {
                    $file = substr($file, strlen($this->path));
                }
                if ($file === "" || $file === false) {
                    $file = '.';
                }
                $file = " <comment>{$file}</comment>";
            } else {
                $file = "";
            }

            if ($argument !== null) {
                $argument = " <fg=cyan>{$argument}</fg=cyan>";
            } else {
                $argument = "";
            }

            $this->output->writeln("{$indent}<info>{$action}<info>{$argument}{$file}");
        }
    }

    /**
     * Set the filesystem to be used for filesystem operations.
     * @param Filesystem $filesystem
     */
    public function setFilesystem(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    /**
     * {@inheritdoc}
     */
    public function setGenerator(GeneratorInterface $generator)
    {
        $this->generator = $generator;
    }

    /**
     * @param KernelInterface $kernel
     */
    public function setKernel(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }

    /**
     * Try to render a template and return the resulting string.
     * @param string $template
     * @param array  $variables
     * @return string
     */
    protected function render($template, array $variables)
    {
        $dir = dirname($template);
        $file = basename($template);
        $twig = new \Twig_Environment(new \Twig_Loader_Filesystem(array($dir)), array(
            'debug'            => true,
            'cache'            => false,
            'strict_variables' => true,
            'autoescape'       => false,
        ));
        return $twig->render($file, $variables);
    }

    /**
     * {@inheritdoc}
     */
    public function pretend($pretend = true)
    {
        $this->pretendAction = $pretend;
    }

    /**
     * Returns true if actions should be executed.
     * @return bool
     */
    public function isReal()
    {
        return !$this->pretendAction;
    }

    /**
     * Returns true if actions should not actually be executed.
     * @return bool
     */
    public function isPretend()
    {
        return $this->pretendAction;
    }

    public function confirm($question, $default = false)
    {
        /** @var DialogHelper $dialog */
        $dialog = $this->generator->getHelperSet()->get('dialog');
        $value = $default ? 'yes' : 'no';
        $question = sprintf('<%1$s>%2$s <%3$s>[%4$s]</%3$s></%1$s> ', 'fg=yellow', $question, 'fg=cyan', $value);
        return $dialog->askConfirmation($this->output, $question, $default);
    }
}
