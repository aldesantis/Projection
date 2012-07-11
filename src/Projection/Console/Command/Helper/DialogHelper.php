<?php

/*
 * This file is part of the Projection package.
 *
 * (c) Alessandro Desantis <desa.alessandro@gmail.com>
 *
 * For the full copyright and license information, view the
 * LICENSE file that was distributed with the source code.
 */

namespace Projection\Console\Command\Helper;

use Symfony\Component\Console\Helper\DialogHelper as BaseDialogHelper;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Helps generating questions
 *
 * @author Alessandro Desantis <desa.alessandro@gmail.com>
 */
class DialogHelper extends BaseDialogHelper
{
    /**
     * Writes a section.
     *
     * @param OutputInterface $output The output handler
     * @param string|array    $text   The section's text
     * @param string          $style  The style to use
     */
    public function writeSection(OutputInterface $output, $text, $style = 'bg=blue;fg=white')
    {
        $output->writeln(array(
            '',
            $this->getHelperSet()->get('formatter')->formatBlock($text, $style, true),
            '',
        ));
    }

    /**
     * Returns a question.
     *
     * @param string $question Question text
     * @param mixed  $default  Default value
     * @param string $sep      Separator
     *
     * @return string
     */
    public function getQuestion($question, $default, $sep = ':')
    {
        if ($default) {
            return sprintf(
                '<info>%s</info> [<comment>%s</comment>]%s ',
                $question,
                $default,
                $sep
            );
        }

        return sprintf('<info>%s</info>%s ', $question, $sep);
    }
}
