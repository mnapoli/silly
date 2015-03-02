<?php

namespace Silly\Command;

use Silly\Input\InputArgument;
use Silly\Input\InputOption;

/**
 * Parses the expression that defines a command.
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class ExpressionParser
{
    public function parse($expression)
    {
        $tokens = explode(' ', $expression);
        $tokens = array_map('trim', $tokens);
        $tokens = array_values(array_filter($tokens));

        if (count($tokens) === 0) {
            throw new InvalidCommandExpression('The expression was empty');
        }

        $name = array_shift($tokens);

        $arguments = [];
        $options = [];

        foreach ($tokens as $token) {
            if ($this->startsWith($token, '--')) {
                throw new InvalidCommandExpression('An option must be enclosed by brackets: [--option]');
            }

            if ($this->isOption($token)) {
                $options[] = $this->parseOption($token);
            } else {
                $arguments[] = $this->parseArgument($token);
            }
        }

        return [
            'name' => $name,
            'arguments' => $arguments,
            'options' => $options,
        ];
    }

    private function isOption($token)
    {
        return $this->startsWith($token, '[-');
    }

    private function parseArgument($token)
    {
        if ($this->endsWith($token, ']*')) {
            $mode = InputArgument::IS_ARRAY;
            $name = trim($token, '[]*');
        } elseif ($this->endsWith($token, '*')) {
            $mode = InputArgument::IS_ARRAY | InputArgument::REQUIRED;
            $name = trim($token, '*');
        } elseif ($this->startsWith($token, '[')) {
            $mode = InputArgument::OPTIONAL;
            $name = trim($token, '[]');
        } else {
            $mode = InputArgument::REQUIRED;
            $name = $token;
        }

        return new InputArgument($name, $mode);
    }

    private function parseOption($token)
    {
        $token = trim($token, '[]');

        // Shortcut [-y|--yell]
        if (strpos($token, '|') !== false) {
            list($shortcut, $token) = explode('|', $token, 2);
            $shortcut = ltrim($shortcut, '-');
        } else {
            $shortcut = null;
        }

        $name = ltrim($token, '-');

        if ($this->endsWith($token, '=]*')) {
            $mode = InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY;
            $name = substr($name, 0, -3);
        } elseif ($this->endsWith($token, '=')) {
            $mode = InputOption::VALUE_REQUIRED;
            $name = rtrim($name, '=');
        } else {
            $mode = InputOption::VALUE_NONE;
        }

        return new InputOption($name, $shortcut, $mode);
    }

    private function startsWith($haystack, $needle)
    {
        return substr($haystack, 0, strlen($needle)) === $needle;
    }

    private function endsWith($haystack, $needle)
    {
        return substr($haystack, -strlen($needle)) === $needle;
    }
}
