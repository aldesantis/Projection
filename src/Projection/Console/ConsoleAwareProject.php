<?php

/*
 * This file is part of the Projection package.
 *
 * (c) Alessandro Desantis <desa.alessandro@gmail.com>
 *
 * For the full copyright and license information, view the
 * LICENSE file that was distributed with the source code.
 */

namespace Projection\Console;

use Projection\Project;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Console aware project
 *
 * This project reports all the progress (e.g. file creation) on the console.
 *
 * @author Alessandro Desantis <desa.alessandro@gmail.com>
 */
class ConsoleAwareProject extends Project
{
    /**
     * @var OutputInterface The output handler.
     * @access private
     */
    private $output;

    /**
     * Sets the output handler.
     *
     * @param OutputInterface $output Output handler
     *
     * @return ConsoleAwareProject
     */
    public function setOutput(OutputInterface $output)
    {
        $this->output = $output;
        return $this;
    }

    /**
     * Returns the output handler.
     *
     * @return OutputInterface
     */
    public function getOutput()
    {
        return $this->output;
    }

    /**
     * {@inheritDoc}
     */
    public function createFile($template, $path, array $variables = array())
    {
        parent::createFile($template, $path, $variables);

        $path = trim($path, '/');
        $readablePath = basename(rtrim($this->getPath(), '/')) . "/{$path}";

        $this->getOutput()->writeln("<info>+file</info> <comment>{$readablePath}</comment>");
    }

    /**
     * {@inheritDoc}
     */
    public function createDirectory($directory = '/', $mode = 0775)
    {
        if (parent::createDirectory($directory, $mode)) {
            $directory = trim($directory, '/');
            $readablePath = basename(rtrim($this->getPath(), '/')) . "/{$directory}";
            $this->getOutput()->writeln("<info>+dir</info> <comment>{$readablePath}</comment>");

            return true;
        }

        return false;
    }
}
