<?php

namespace TweedeGolf\GeneratorBundle\Generator\Builder\Modifier;

interface FileModifierInterface
{
    /**
     * Append a string to all currently matched parts of the file.
     * @param string $string
     * @return $this
     */
    public function append($string);

    /**
     * Prepend a string to all currently matched parts of the file.
     * @param string $string
     * @return $this
     */
    public function prepend($string);

    /**
     * Match a part of the file after the match with the given offset, or match all if it is less than zero.
     * @param string $match
     * @param bool   $regex Whether or not to match on regular expressions.
     * @param int    $offset
     * @return $this
     */
    public function after($match, $regex = false, $offset = -1);

    /**
     * Match a part of the file before the match with the given offset, or match all if it is less than zero.
     * @param string $match
     * @param bool   $regex Whether or not to match on regular expressions.
     * @param int    $offset
     * @return $this
     */
    public function before($match, $regex = false, $offset = -1);
}
