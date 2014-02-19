<?php

namespace TweedeGolf\GeneratorBundle\Generator\Builder;

use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\HttpFoundation\File\File;
use TweedeGolf\GeneratorBundle\Exception\GenerationAbortedException;
use TweedeGolf\GeneratorBundle\Generator\Builder\Modifier\FileModifier;

class Builder extends AbstractBuilder
{
    /**
     * {@inheritdoc}
     */
    public function mkdir($directory, $mode = 0755)
    {
        $directory = $this->joinPath($directory);
        if ($directory[strlen($directory) - 1] !== '/') {
            $directory .= '/';
        }
        $this->showAction('mkdir', $directory, decoct($mode));
        if ($this->filesystem->exists($directory)) {
            throw new GenerationAbortedException("Directory '{$directory}' already exists");
        }

        if ($this->isReal()) {
            $this->filesystem->mkdir($directory, $mode);
        }
    }

    private function getTemplateFile($template)
    {
        $class = get_class($this->generator);
        $root = $this->kernel->getContainer()->getParameter('kernel.root_dir');
        $skeletonFile = '/skeleton/' . $this->generator->getName() . '/' . $template;
        if (preg_match('/^(([a-zA-Z0-9_\\\\]+)Bundle)\\\\(.*)$/', $class, $match) === 1) {
            $resource = $root . '/Resources/' . $this->generator->getBundle() . $skeletonFile;
            if (!$this->filesystem->exists($resource)) {
                $bundle = '@' . $this->generator->getBundle();
                $resource = $this->kernel->locateResource($bundle . '/Resources' . $skeletonFile);
            }
        } else {
            $resource = $root . '/Resources' . $skeletonFile;
        }
        return $resource;
    }

    /**
     * {@inheritdoc}
     */
    public function template($template, $target, array $variables = array(), $mode = 0644, $directoryAutoCreate = true)
    {
        $fullTarget = $this->joinPath($target);
        $resource = $this->getTemplateFile($template);
        $output = $this->render($resource, array_merge($variables, ['input' => $this->vars]));

        $directory = dirname($fullTarget);
        if (!is_dir($directory)) {
            if ($directoryAutoCreate) {
                $this->mkdir($directory);
            }
        } else {
            throw new GenerationAbortedException("Directory '{$directory}' could not be found");
        }

        if (!$this->filesystem->exists($target) || $this->confirm("File '{$target}' already exists, overwrite?")) {
            try {
                $this->showAction('generate', $fullTarget, decoct($mode));
                if ($this->isReal()) {
                    $this->filesystem->dumpFile($target, $output, $mode);
                }
            } catch (IOException $e) {
                throw new GenerationAbortedException($e->getMessage(), $e->getCode(), $e);
            }
            return true;
        }
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function modify($file)
    {
        $file = $this->joinPath($file);
        $modifier = new FileModifier(new File($file), $this);
        return $modifier;
    }

    /**
     * {@inheritdoc}
     */
    public function in($directory, $callback)
    {
        $this->showAction('enter', $directory);
        $builder = clone $this;
        $builder->setBasePath($this->joinPath($directory));
        $builder->indentation = $this->indentation + 1;
        $callback($builder);
        $this->showAction('leave');
    }

    /**
     * {@inheritdoc}
     */
    public function touch($file, $mode = 0644)
    {
        $file = $this->joinPath($file);
        if (!$this->filesystem->exists($file)) {
            $this->showAction('create', $file, decoct($mode));
            if ($this->isReal()) {
                $this->filesystem->touch($file);
                $this->filesystem->chmod($file, $mode);
            }
        } else {
            $this->showAction('touch', $file);
            if ($this->isReal()) {
                $this->filesystem->touch($file);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function finish()
    {
        $this->showAction('done');
    }
}
