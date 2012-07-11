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
 * Project generator
 *
 * This command creates a new project handled by Projection.
 *
 * @author Alessandro Desantis <desa.alessandro@gmail.com>
 */
class GenerateProjectCommand extends Command
{
    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this
            ->setName('generate:project')
            ->setDescription('Generates a new project.')
            ->addOption('name', null, InputOption::VALUE_OPTIONAL, 'Your new project\'s name')
            ->addOption('dir', null, InputOption::VALUE_OPTIONAL, 'The project\'s directory', null)
            ->addOption('namespace', null, InputOption::VALUE_OPTIONAL, 'The project\'s namespace', null)
            ->addOption('author-name', null, InputOption::VALUE_OPTIONAL, 'The author\'s name', null)
            ->addOption('author-email', null, InputOption::VALUE_OPTIONAL, 'The author\'s email', null)
            ->addOption('license', null, InputOption::VALUE_OPTIONAL, 'The license to use (mit, gpl, lesser-gpl)', 'mit')
            ->addOption('license-years', null, InputOption::VALUE_OPTIONAL, 'The year(s) the license applies to', date('Y'))
            ->addOption('src-dir', null, InputOption::VALUE_OPTIONAL, 'The source directory', 'src')
            ->addOption('docs-dir', null, InputOption::VALUE_OPTIONAL, 'The documentation directory', 'doc')
            ->addOption('no-tests', null, InputOption::VALUE_NONE, 'If set, no tests will be ever created')
            ->addOption('tests-dir', null, InputOption::VALUE_OPTIONAL, 'The tests directory (ignored if --no-tests is set)', 'tests')
        ;
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        foreach(array('name', 'author-name', 'author-email') as $option) {
            if ($input->getOption($option) === null) {
                throw new \RuntimeException(sprintf(
                    'The "%s" option must be provided.',
                    $option
                ));
            }
        }

        $name            = Validators::validateProjectName($input->getOption('name'));
        $directory       = Validators::validateDirectory($input->getOption('dir'));
        $namespace       = Validators::validateNamespace($input->getOption('namespace'));
        $authorName      = Validators::validateName($input->getOption('author-name'));
        $authorEmail     = Validators::validateEmail($input->getOption('author-email'));
        $license         = Validators::validateLicense($input->getOption('license'));
        $licenseYears    = Validators::validateLicenseYears($input->getOption('license-years'));
        $sourceDirectory = Validators::validateDirectory($input->getOption('src-dir'));
        $docsDirectory   = Validators::validateDirectory($input->getOption('docs-dir'));

        if (!$input->getOption('no-tests')) {
            $testsDirectory = Validators::validateDirectory($input->getOption('tests-dir'));
        }

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

        $project = new ConsoleAwareProject(getcwd() . "/{$directory}");
        $project->setOutput($output);
        $project->setConfig(array(
            'project' => array(
                'name'      => $name,
                'namespace' => $namespace,
            ),

            'author' => array(
                'name'  => $authorName,
                'email' => $authorEmail,
            ),

            'license' => array(
                'type'  => $license,
                'years' => $licenseYears,
            ),

            'src'   => array('dir' => $sourceDirectory),
            'docs'  => array('dir' => $docsDirectory),
            'tests' => $input->getOption('no-tests') ? false : array('dir' => $testsDirectory),
        ));

        $output->writeln('');

        $project->createStructure('/', array(
            '/'                            => null,
            $sourceDirectory               => null,
            $project->getSourceDirectory() => null,
            $docsDirectory                 => null,
        ));

        if (!$input->getOption('no-tests')) {
            $project->createStructure('/', array(
                $testsDirectory               => null,
                $project->getTestsDirectory() => null,
            ));
        }

        $project->writeConfig();

        $structure = array(
            '/README.md' => 'txt/README.md.twig',
            '/LICENSE'   => "txt/licenses/{$license}.txt.twig",
        );

        if (!$input->getOption('no-tests')) {
            $casePath = $project->getTestsDirectory() . '/TestCase.php';

            $structure += array(
                '/phpunit.xml.dist' => 'phpunit/phpunit.xml.dist.twig',
                $casePath           => 'phpunit/TestCase.php.twig',
            );
        }

        $project->createStructure('/', $structure, array(
            'config' => $project->getConfig(),
        ));

        $output->writeln(array(
            '',
            "Project <comment>{$name}</comment> created in <comment>{$directory}</comment>.",
        ));
    }

    /**
     * {@inheritDoc}
     */
    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $dialog = $this->getHelperSet()->get('dialog');
        $dialog->writeSection($output, 'Welcome to the Projection project generator!');

        $output->writeln(array(
            '',
            'All your work as a PHP developers is split into <comment>projects</comment>.',
            'Each project is a separate application with its own author,',
            'license, source code, unit tests and so on and so forth.',
            '',
            'This command will allow you to generate a new project.',
            '',
        ));

        $name = $dialog->askAndValidate(
            $output,
            $dialog->getQuestion('Project name', $input->getOption('name')),
            array(self::$validators, 'validateProjectName'),
            false,
            $input->getOption('name')
        );
        $input->setOption('name', $name);

        $defaultDirectory = $input->getOption('dir') ?: $name;
        $directory = $dialog->askAndValidate(
            $output,
            $dialog->getQuestion('Project directory', $defaultDirectory),
            array(self::$validators, 'validateDirectory'),
            false,
            $defaultDirectory
        );
        $input->setOption('dir', $directory);

        $defaultNamespace = $input->getOption('namespace') ?: str_replace(' ', '', $name);
        $namespace = $dialog->askAndValidate(
            $output,
            $dialog->getQuestion('Project namespace', $defaultNamespace),
            array(self::$validators, 'validateNamespace'),
            false,
            $defaultNamespace
        );
        $input->setOption('namespace', $namespace);

        $output->writeln(array(
            '',
            'Would you mind telling us about yourself? We will use this',
            'info to write license information and API documentation',
            'for your project.',
            '',
        ));

        $authorName = $dialog->askAndValidate(
            $output,
            $dialog->getQuestion('Author name', $input->getOption('author-name')),
            array(self::$validators, 'validateName'),
            false,
            $input->getOption('author-name')
        );
        $input->setOption('author-name', $authorName);

        $authorEmail = $dialog->askAndValidate(
            $output,
            $dialog->getQuestion('Author email', $input->getOption('author-email')),
            array(self::$validators, 'validateEmail'),
            false,
            $input->getOption('author-email')
        );
        $input->setOption('author-email', $authorEmail);

        $output->writeln(array(
            '',
            'We will know decide which kind of <comment>license</comment> your project will',
            'be released under. Please, take this step VERY SERIOUSLY: it',
            'is important to protect your project!',
            '',
            'We strongly recommend using the <comment>MIT</comment> license.',
            '',
        ));

        $license = $dialog->askAndValidate(
            $output,
            $dialog->getQuestion('License type', $input->getOption('license')),
            array(self::$validators, 'validateLicense'),
            false,
            $input->getOption('license')
        );
        $input->setOption('license', $license);

        $licenseYears = $dialog->askAndValidate(
            $output,
            $dialog->getQuestion('License year(s)', $input->getOption('license-years')),
            array(self::$validators, 'validateLicenseYears'),
            false,
            $input->getOption('license-years')
        );
        $input->setOption('license-years', $licenseYears);

        $output->writeln(array(
            '',
            'It is time to decide where your project files will be stored.',
            'We recommend that you keep the default settings unless you',
            'have some special requirements.',
            '',
        ));

        $sourceDirectory = $dialog->askAndValidate(
            $output,
            $dialog->getQuestion('Source directory', $input->getOption('src-dir')),
            array(self::$validators, 'validateDirectory'),
            false,
            $input->getOption('src-dir')
        );
        $input->setOption('src-dir', $sourceDirectory);

        $docsDirectory = $dialog->askAndValidate(
            $output,
            $dialog->getQuestion('Documentation directory', $input->getOption('docs-dir')),
            array(self::$validators, 'validateDirectory'),
            false,
            $input->getOption('docs-dir')
        );

        $output->writeln(array(
            '',
            'Okay, we\'re almost done! Now you must choose whether you',
            'will use <comment>unit tests</comment> in your project.',
            '',
            'Unit tests are a wonderful way to test that your project',
            'works correctly after you change something in the API, but',
            'sometimes it can be better to use another type of testing',
            'like integration testing or acceptance testing.',
            '',
        ));

        $defaultOption = !$input->getOption('no-tests') ? 'yes' : 'no';
        $unitTests = $dialog->askConfirmation(
            $output,
            $dialog->getQuestion('Do you want to use unit tests', $defaultOption, '?'),
            $defaultOption
        );
        $input->setOption('no-tests', !$unitTests);

        if ($unitTests) {
            $testsDirectory = $dialog->askAndValidate(
                $output,
                $dialog->getQuestion('Tests directory', $input->getOption('tests-dir')),
                array(self::$validators, 'validateDirectory'),
                false,
                $input->getOption('tests-dir')
            );
            $input->setOption('tests-dir', $testsDirectory);
        }
    }
}
