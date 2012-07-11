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

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Projection\Console\ConsoleAwareProject;

/**
 * Class generator
 *
 * This command generates a new class in a project.
 *
 * @author Alessandro Desantis <desa.alessandro@gmail.com>
 */
class GenerateClassCommand extends Command
{
    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this
            ->setName('generate:class')
            ->setDescription('Generates a new project class.')
            ->addOption('name', null, InputOption::VALUE_OPTIONAL, 'The class name')
        ;
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $project = $this->getProject();
        } catch (\InvalidArgumentException $e) {
            throw new \LogicException('This is not a project directory.', 0, $e);
        }

        $project->setOutput($output);
        $config = $project->getConfig();

        foreach(array('name') as $option) {
            if ($input->getOption($option) === null) {
                throw new \RuntimeException(sprintf(
                    'The "%s" option must be provided.',
                    $option
                ));
            }
        }

        $name = Validators::validateClassName($input->getOption('name'));

        if ($input->isInteractive()) {
            $dialog = $this->getHelperSet()->get('dialog');
            $output->writeln('');

            $confirm = $dialog->askConfirmation(
                $output,
                $dialog->getQuestion('Do you confirm generation', 'yes', '?'),
                true
            );

            if (!$confirm) {
                $output->writeln('<error>Operation aborted.</error>');
                return 1;
            }
        }

        $output->writeln('');

        $classPath = strtr($name, '\\', '/');
        $path = $project->getSourceDirectory() . "/{$classPath}.php";

        $structure = array(
            $path => 'php/class.php.twig',
        );

        if ($config['tests']) {
            $testPath = $project->getTestsDirectory() . "/{$classPath}Test.php";

            $structure += array(
                $testPath => 'phpunit/test.php.twig',
            );
        }

        $parts = explode('\\', $name);
        $class = array_pop($parts);
        $namespace = implode('\\', $parts);

        $varName = $class;
        $varName[0] = strtolower($varName[0]);

        $project->createStructure('/', $structure, array(
            'config'    => $project->getConfig(),
            'class' => array(
                'name'      => $class,
                'namespace' => $namespace,
                'varName'   => $varName,
            ),
        ));

        $readablePath = $project->getSourceDirectory() . "/{$classPath}.php";
        $output->writeln(array('', "Class <comment>{$name}</comment> has been created in <comment>{$readablePath}</comment>."));
    }

    /**
     * {@inheritDoc}
     */
    protected function interact(InputInterface $input, OutputInterface $output)
    {
        try {
            $project = $this->getProject();
        } catch (\InvalidArgumentException $e) {
            throw new \LogicException('This is not a project directory.', 0, $e);
        }

        $project->setOutput($output);
        $config = $project->getConfig();

        $dialog = $this->getHelperSet()->get('dialog');
        $dialog->writeSection($output, 'Welcome to the Projection class generator!');

        $output->writeln(array(
            '',
            "This task will generate a new <comment>class</comment> in the <comment>{$config['project']['name']}</comment>",
            'project. It will also generate the needed unit tests if',
            'you want to (and if the project supports them).',
            '',
        ));

        $name = $dialog->askAndValidate(
            $output,
            $dialog->getQuestion('Class name', $input->getOption('name')),
            array(self::$validators, 'validateClassName'),
            false,
            $input->getOption('name')
        );
        $input->setOption('name', $name);
    }
}
