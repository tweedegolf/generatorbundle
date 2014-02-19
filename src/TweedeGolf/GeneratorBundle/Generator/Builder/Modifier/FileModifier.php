<?php

namespace TweedeGolf\GeneratorBundle\Generator\Builder\Modifier;

class FileModifier extends AbstractFileModifier
{
    /**
     * Append a string to all currently matched parts of the file.
     * @param string $string
     * @return $this
     */
    public function append($string)
    {
        // TODO: Implement append() method.
        $this->done();
        return $this;
    }

    /**
     * Prepend a string to all currently matched parts of the file.
     * @param string $string
     * @return $this
     */
    public function prepend($string)
    {
        // TODO: Implement prepend() method.

        $this->done();
        return $this;
    }
}
