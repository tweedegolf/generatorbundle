<?php

namespace TweedeGolf\GeneratorBundle\Generator\Builder\Modifier;

class FilePart
{
    /**
     * @var int
     */
    public $offset;

    /**
     * @var string
     */
    public $content;

    /**
     * @var string
     */
    public $append;

    /**
     * @var string
     */
    public $prepend;

    /**
     * @param string $content
     * @param int    $offset
     */
    public function __construct($content, $offset = 0)
    {
        $this->offset = $offset;
        $this->content = $content;
    }
}
