<?php

/*
 * This file is part of the Projection package.
 *
 * (c) Alessandro Desantis <desa.alessandro@gmail.com>
 *
 * For the full copyright and license information, view the
 * LICENSE file that was distributed with the source code.
 */

namespace Projection\Console\Command;

use Symfony\Component\Console\Command\Command as BaseCommand;
use Projection\Console\ConsoleAwareProject;

/**
 * Console command
 *
 * @author Alessandro Desantis <desa.alessandro@gmail.com>
 */
class Command extends BaseCommand
{
    /**
     * @var string Validator class.
     */
    static protected $validators = 'Projection\Console\Command\Validators';

    /**
     * @var Project The current project.
     * @access private
     */
    private $project;

    /**
     * Initializes and returns the current project.
     *
     * @return Project
     */
    protected function getProject()
    {
        if ($this->project !== null) {
            return $this->project;
        }

        return $this->project = ConsoleAwareProject::createFromConfig(getcwd());
    }
}
