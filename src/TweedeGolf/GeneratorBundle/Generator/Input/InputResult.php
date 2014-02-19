<?php

namespace TweedeGolf\GeneratorBundle\Generator\Input;

class InputResult extends \ArrayObject
{
    private $interactive;

    public function __construct()
    {
        parent::__construct(array(), \ArrayObject::ARRAY_AS_PROPS);
        $this->interactive = false;
    }

    /**
     * Return a collected value from the input.
     * @param string $key
     * @return mixed
     */
    public function getValue($key)
    {
        return $this[$key];
    }

    /**
     * Set a value collected from the input.
     * @param string $key
     * @param mixed $value
     * @return $this
     */
    public function setValue($key, $value)
    {
        $this[$key] = $value;
        return $this;
    }

    /**
     * Returns true if a value was set and is not null.
     * @param string $key
     * @return bool
     */
    public function hasValue($key)
    {
        return isset($this[$key]) && $this[$key] !== null;
    }

    /**
     * This result was retrieved interactively.
     */
    public function enableInteractive()
    {
        $this->interactive = true;
    }

    /**
     * @return boolean
     */
    public function isInteractive()
    {
        return $this->interactive;
    }
}
