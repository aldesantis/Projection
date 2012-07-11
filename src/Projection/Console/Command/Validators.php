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

/**
 * Validators
 *
 * This class contains static methods that validate all the data entered by the
 * user.
 *
 * @author Alessandro Desantis <desa.alessandro@gmail.com>
 */
class Validators
{
    static public function validateProjectName($name)
    {
        if (!preg_match('/[a-zA-Z0-9\_\-]+/', $name)) {
            throw new \InvalidArgumentException(
                'A project\'s name can contain only alphanumeric characters, ' .
                'dashes and underscores.'
            );
        }

        return $name;
    }

    static public function validateNamespace($namespace)
    {
        $namespace = strtr($namespace, '/', '\\');

        if (!preg_match('/^[a-zA-Z0-9\\\\_]+$/', $namespace)) {
            throw new \InvalidArgumentException(
                'You must enter a valid PHP namespace.'
            );
        }

        return $namespace;
    }

    static public function validateName($name)
    {
        if ($name == '') {
            throw new \InvalidArgumentException(
                'You must enter the project author\'s name.'
            );
        }

        return $name;
    }

    static public function validateEmail($email)
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException(
                'You must enter a valid email address.'
            );
        }

        return $email;
    }

    static public function validateLicense($license)
    {
        $licenses = array('gpl', 'mit', 'lgpl');

        if (!in_array($license, $licenses)) {
            throw new \InvalidArgumentException(sprintf(
                'License "%s" is not valid. Valid licenses are: %s.',
                $license,
                implode(', ', $licenses)
            ));
        }

        return $license;
    }

    static public function validateLicenseYears($licenseYears)
    {
        if (!preg_match('/^([0-9]{4})(\-[0-9]{4})?$/', $licenseYears)) {
            throw new \InvalidArgumentException(
                'License years must be a single year or a range.'
            );
        }

        return $licenseYears;
    }

    static public function validateDirectory($directory)
    {
        if ($directory == '') {
            throw new \InvalidArgumentException(
                'You must enter a valid directory name.'
            );
        }

        return $directory;
    }

    static public function validateClassName($class)
    {
        if ($class == '') {
            throw new \InvalidArgumentException('You must enter a valid class name.');
        }

        return strtr($class, '/', '\\');
    }
}
