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

use Symfony\Component\Console\Application as BaseApplication;

/**
 * Console application
 *
 * @author Alessandro Desantis <desa.alessandro@gmail.com>
 */
class Application extends BaseApplication
{
    /**
     * {@inheritDoc}
     */
    public function __construct()
    {
        parent::__construct();

        $this->setName('Projection');
        $this->setVersion('0.1');

        $this->getHelperSet()->set(new Command\Helper\DialogHelper());

        $this->add(new Command\GenerateProjectCommand());
        $this->add(new Command\GenerateClassCommand());
    }
}
