<?php

namespace TweedeGolf\GeneratorBundle\Generator\Input;

use TweedeGolf\GeneratorBundle\Exception\DefinitionException;

abstract class CallbackInputType extends AbstractInputType
{
    /**
     * @var array
     */
    private $transformers;

    public function __construct($name, $description, $mode = 0, $default = null)
    {
        parent::__construct($name, $description, $mode, $default);
        $this->resetTransformers();
    }

    /**
     * Set the transformer function to be called.
     * @param callable $callback
     * @return $this
     */
    public function setTransformer($callback)
    {
        $this->resetTransformers();
        $this->addTransformer($callback);
        return $this;
    }

    /**
     * Reset the list of transformers to no transformers
     * @return $this
     */
    public function resetTransformers()
    {
        $this->transformers = array();
        return $this;
    }

    /**
     * Add a transformer to the list of transformers.
     * @param callback $callback The callback function to be called.
     * @param integer $position The position to insert the transformer at, or -1 to insert it at the end of the list.
     * @return $this
     * @throws DefinitionException
     */
    public function addTransformer($callback, $position = -1)
    {
        if (!is_callable($callback)) {
            throw new DefinitionException("Transformer is not a valid callback function");
        }
        if ($position < 0 || count($this->transformers) < $position) {
            $position = count($this->transformers);
        }
        array_splice($this->transformers, $position, 0, array($callback));
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function transform($value)
    {
        foreach ($this->transformers as $transformer) {
            $value = $transformer($value);
        }
        return $value;
    }
}
