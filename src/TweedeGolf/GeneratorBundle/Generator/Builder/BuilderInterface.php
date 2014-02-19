<?php

namespace TweedeGolf\GeneratorBundle\Generator\Builder;

use Symfony\Component\Console\Output\OutputInterface;
use TweedeGolf\GeneratorBundle\Generator\Builder\Modifier\FileModifierInterface;
use TweedeGolf\GeneratorBundle\Generator\GeneratorInterface;
use TweedeGolf\GeneratorBundle\Generator\Input\InputResult;

interface BuilderInterface
{
    /**
     * Create a directory on the filesystem.
     * @param string  $directory The directory to create.
     * @param int     $mode      Permissions for the new directory.
     */
    public function mkdir($directory, $mode = 0755);

    /**
     * @param string $template            The original template name.
     * @param string $target              Target file name.
     * @param array  $variables           Array of variables for applying in the template.
     * @param int    $mode                Permissions for the new file.
     * @param bool   $directoryAutoCreate Whether or not to automatically create the directory that contains the file.
     */
    public function template($template, $target, array $variables = array(), $mode = 0644, $directoryAutoCreate = true);

    /**
     * Open a file for modification.
     * @param string $file
     * @return FileModifierInterface
     */
    public function modify($file);

    /**
     * Call the callback with a builder in which all files are created relative to the directory given.
     * @param string   $directory The directory in which the child builder should be located.
     * @param callback $callback  The callback which should be called. The first argument of the callback
     *                            will be an instance of BuilderInterface.
     */
    public function in($directory, $callback);

    /**
     * Update the last modified date of a file to the current time. If a file does not yet exist, create it with
     * the given permissions.
     * @param string $file The name of the file to update.
     * @param int    $mode Permissions of the file if it needs to be created.
     */
    public function touch($file, $mode = 0644);

    /**
     * Set the input variables for usage in templates.
     * @param InputResult $vars
     */
    public function setInput(InputResult $vars);

    /**
     * Set the generator that will use the builder.
     * @param GeneratorInterface $generator
     */
    public function setGenerator(GeneratorInterface $generator);

    /**
     * Set the output interface on which any generate messages should be displayed.
     * @param OutputInterface $output
     */
    public function setOutput(OutputInterface $output);

    /**
     * Set the base path for file locations.
     * @param string $path
     */
    public function setBasePath($path);

    /**
     * Set the builder to pretend the building process.
     * @param bool $pretend
     */
    public function pretend($pretend = true);

    /**
     * Finish any remaining operations.
     */
    public function finish();
}
