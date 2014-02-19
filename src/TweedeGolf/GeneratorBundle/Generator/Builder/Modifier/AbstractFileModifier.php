<?php

namespace TweedeGolf\GeneratorBundle\Generator\Builder\Modifier;

use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\HttpFoundation\File\File;
use TweedeGolf\GeneratorBundle\Generator\Builder\BuilderInterface;

abstract class AbstractFileModifier implements FileModifierInterface
{
    /**
     * @var File
     */
    private $file;

    /**
     * @var \SplFileObject
     */
    private $resource;

    /**
     * Array of currently matched content parts
     * @var array
     */
    protected $content;

    /**
     * @var BuilderInterface
     */
    protected $builder;

    private $data;

    public function __construct(File $file, BuilderInterface $builder)
    {
        $this->file = $file;
        $this->builder = $builder;
        $this->resource = $this->file->openFile('r+');
        $this->data = $this->getFileContent();
        $this->content = [new FilePart($this->data)];
    }

    private function getFileContent()
    {
        $content = "";
        while (!$this->resource->eof()) {
            $content .= $this->resource->fgets();
        }
        $this->resource->rewind();
        return $content;
    }

    protected function sync()
    {
        usort($this->content, function (FilePart $first, FilePart $second) {
            return $second->offset - $first->offset;
        });
        $data = $this->data;

        /** @var FilePart $part */
        foreach ($this->content as $part) {
            if ($part->append) {
                $data = substr_replace($data, $part->append, $part->offset + strlen($part->content), 0);
            }

            if ($part->prepend) {
                $data = substr_replace($data, $part->prepend, $part->offset, 0);
            }
        }
        var_dump($data);
    }

    public function valid()
    {
        return $this->file !== null;
    }

    public function done()
    {
        $this->sync();
        $this->resource = null;
        $this->content = null;
        $this->file = null;
    }

    /**
     * @param string $content
     * @param string $match
     * @param bool   $regex
     * @return array
     */
    protected function findMatches($content, $match, $regex = false)
    {
        $positions = array();
        if ($regex) {
            preg_match_all($match, $content, $matches, PREG_OFFSET_CAPTURE);
            $matches = $matches[0];
            foreach ($matches as $match) {
                $positions[] = array($match[1], $match[1] + strlen($match[0]));
            }
        } else {
            $last = 0;
            $length = strlen($match);
            while ($last = strpos($content, $match, $last)) {
                var_dump($last);
                $positions[] = array($last, $last + $length);
            }
        }
        return $positions;
    }

    /**
     * {@inheritdoc}
     */
    public function after($match, $regex = false, $offset = -1)
    {
        $parts = array();
        /** @var FilePart $part */
        foreach ($this->content as $part) {
            $subparts = array();
            $content = $part->content;
            $matches = array_reverse($this->findMatches($content, $match, $regex));
            $previous = strlen($content);
            foreach ($matches as $match) {
                $subparts[] = new FilePart(substr($content, $match[1], $previous), $part->offset + $match[1]);
                $previous = $match[1];
            }

            // we got the parts in reverse up until now
            $subparts = array_reverse($subparts);

            // if offset is given, limit the results from this part
            if ($offset >= 0) {
                if ($offset >= count($subparts)) {
                    throw new IOException("Cannot modify file, no matching '{$match}' for offset {$offset}");
                } else {
                    $subparts = array($subparts[$offset]);
                }
            }
            $parts = array_merge($parts, $subparts);
        }
        $this->content = $parts;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function before($match, $regex = false, $offset = -1)
    {
        $parts = array();
        /** @var FilePart $part */
        foreach ($this->content as $part) {
            $subparts = array();
            $content = $part->content;
            $matches = $this->findMatches($content, $match, $regex);
            $previous = 0;
            foreach ($matches as $match) {
                $subparts[] = new FilePart(substr($content, $previous, $match[0]), $part->offset + $previous);
                $previous = $match[1];
            }

            // if offset is given, limit the results from this part
            if ($offset >= 0) {
                if ($offset >= count($subparts)) {
                    throw new IOException("Cannot modify file, no matching '{$match}' for offset {$offset}");
                } else {
                    $subparts = array($subparts[$offset]);
                }
            }
            $parts = array_merge($parts, $subparts);
        }
        $this->content = $parts;
        return $this;
    }
}
