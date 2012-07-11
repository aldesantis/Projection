<?php

/*
 * This file is part of the Projection package.
 *
 * (c) Alessandro Desantis <desa.alessandro@gmail.com>
 *
 * For the full copyright and license information, view the
 * LICENSE file that was distributed with the source code.
 */

namespace Projection;

use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Yaml\Exception\ParseException;

/**
 * Project
 *
 * @author Alessandro Desantis <desa.alessandro@gmail.com>
 */
class Project
{
    /**
     * @var string Project path
     * @access private
     */
    private $path;

    /**
     * @var array Project configuration
     * @access private
     */
    private $config = array();

    /**
     * @var \Twig_Environment The Twig environment
     * @access private
     */
    private $twig;

    /**
     * @var string Templates path
     */
    static protected $templatePath;

    /**
     * Creates a project from an exisiting configuration file.
     *
     * @param string $path Path to the project
     *
     * @return Project
     *
     * @throws \InvalidArgumentException If the path isn't a project
     * @throws \RuntimeException         If the configuration isn't valid
     */
    static public function createFromConfig($path)
    {
        if (!is_file($configPath = "{$path}/.projection.yml")) {
            throw new \InvalidArgumentException(sprintf(
                '"%s" is not a project path ("%s" does not exist).',
                $path,
                $configPath
            ));
        }

        try {
            $config = Yaml::parse($configPath);
        } catch (ParseException $e) {
            throw new \RuntimeException(sprintf(
                'Cannot parse "%s": %s',
                $configPath,
                $e->getMessage()
            ), 0, $e);
        }

        return new static($path, $config);
    }

    /**
     * Initializes a new project.
     *
     * @param string $path   Path to the project
     * @param array  $config Project configuration
     *
     * @return Project
     */
    public function __construct($path, array $config = array())
    {
        self::$templatePath = __DIR__ . '/Resources';

        $this->path   = $path;
        $this->config = $config;
    }

    /**
     * Returns the project's path.
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Sets the project's configuration.
     *
     * @param array $config Configuration
     *
     * @return Project
     */
    public function setConfig(array $config)
    {
        $this->config = $config;
        return $this;
    }

    /**
     * Returns the project's configuration.
     *
     * @return array
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Sets the Twig environment to use for parsing templates.
     *
     * @param \Twig_Environment $twig Twig environment
     *
     * @return Project
     */
    public function setTwig(\Twig_Environment $twig)
    {
        $this->twig = $twig;
    }

    /**
     * Returns the Twig environment to use for parsing templates.
     *
     * If no Twig environment has been specified by the user, a default one
     * will be created.
     *
     * @return \Twig_Environment
     */
    public function getTwig()
    {
        if ($this->twig === null) {
            $loader = new \Twig_Loader_Filesystem(self::$templatePath);
            $this->twig = new \Twig_Environment($loader, array(
                'autoescape' => false,
            ));
        }

        return $this->twig;
    }

    /**
     * Returns the full path to the source directory.
     *
     * @return string
     */
    public function getSourceDirectory()
    {
        $namespaceDirectory = strtr($this->config['project']['namespace'], '\\', '/');

        return "{$this->config['src']['dir']}/{$namespaceDirectory}";
    }

    /**
     * Returns the full path to the tests directory.
     *
     * @return string
     */
    public function getTestsDirectory()
    {
        $namespaceDirectory = strtr($this->config['project']['namespace'], '\\', '/');

        return "{$this->config['tests']['dir']}/{$namespaceDirectory}/Tests";
    }

    /**
     * Writes the project configuration.
     *
     * @throws \RuntimeException If the configuration cannot be written
     */
    public function writeConfig()
    {
        $this->createFile('txt/void.twig', '/.projection.yml', array(
            'contents' => Yaml::dump($this->getConfig()),
        ));
    }

    /**
     * Creates a new file in the project.
     *
     * If the directory containing the file doesn't exists, it will be created.
     *
     * @param string $template  Template path
     * @param string $path      Project file path
     * @param array  $variables Template variables
     *
     * @throws \InvalidArgumentException If the stub doesn't exist
     * @throws \RuntimeException         If the file cannot be created
     */
    public function createFile($template, $path, array $variables = array())
    {
        try {
            $contents = $this->getTwig()->render($template, $variables);
        } catch (\Twig_Error_Loader $e) {
            throw new \InvalidArgumentException(sprintf(
                'Cannot load template "%s".',
                $template
            ), 0, $e);
        }

        $path = trim($path, '/');
        $fullPath = rtrim($this->getPath(), '/') . "/{$path}";

        if (!is_dir(dirname($fullPath))) {
            $this->createDirectory(dirname($path));
        }

        if (!@file_put_contents($fullPath, $contents)) {
            throw new \RuntimeException(sprintf(
                'Cannot create file "%s" in "%s".',
                $path,
                $this->getPath()
            ));
        }
    }

    /**
     * Creates a new directory in the project.
     *
     * If the directory already exists, it won't be re-created.
     *
     * @param string  $directory Directory path
     * @param integer $mode      Directory permissions
     *
     * @return boolean Whether the directory has been actually created
     *
     * @throws \RuntimeException If the directory cannot be created
     */
    public function createDirectory($directory = '/', $mode = 0775)
    {
        $directory = trim($directory, '/');
        $path = $this->getPath() . "/{$directory}";

        if (is_dir($path)) {
            return false;
        }

        if (!@mkdir($path, $mode, true)) {
            throw new \RuntimeException(sprintf(
                'Cannot create directory "%s" in "%s".',
                $directory,
                $this->getPath()
            ));
        }

        return true;
    }

    /**
     * Creates the given file/directory structure in the project.
     *
     * @param string $path      Base structure path
     * @param array  $structure Structure to create
     * @param array  $variables Template variables
     *
     * @throws \RuntimeException If the structure cannot be created
     */
    public function createStructure($path, array $structure, array $variables = array())
    {
        $path = trim($path, '/');

        foreach ($structure as $item => $template) {
            $item = trim($item, '/');
            $itemPath = "{$path}/{$item}";

            try {
                if ($template === null) {
                    $this->createDirectory($itemPath);
                } else {
                    $this->createFile($template, $itemPath, $variables);
                }
            } catch (\RuntimeException $e) {
                throw new \RuntimeException(sprintf(
                    'Cannot create item "%s" in "%s".',
                    $item,
                    $path
                ), 0, $e);
            }
        }
    }
}
