<?php

namespace TweedeGolf\GeneratorBundle\Generator\Builder;

use TweedeGolf\GeneratorBundle\Generator\Builder\Modifier\FileModifierInterface;

class RevertingBuilder extends AbstractBuilder
{
    /**
     * {@inheritdoc}
     */
    public function mkdir($directory, $mode = 0755)
    {
        // TODO: Implement mkdir() method.
    }

    /**
     * {@inheritdoc}
     */
    public function template($template, $target, array $variables = array(), $mode = 0644, $directoryAutoCreate = true)
    {
        // TODO: Implement template() method.
    }

    /**
     * {@inheritdoc}
     */
    public function modify($file)
    {
        // TODO: Implement modify() method.
    }

    /**
     * {@inheritdoc}
     */
    public function in($directory, $callback)
    {
        // TODO: Implement in() method.
    }

    /**
     * {@inheritdoc}
     */
    public function touch($file, $mode = 0644)
    {
        // TODO: Implement touch() method.
    }

    /**
     * {@inheritdoc}
     */
    public function finish()
    {
        // TODO: Implement finish() method.
    }
}
