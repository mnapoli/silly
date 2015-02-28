<?php

namespace Silly\Command;

use Silly\Input\InputArgument;
use Silly\Input\InputOption;
use Symfony\Component\Console\Input\InputDefinition;

class Command extends \Symfony\Component\Console\Command\Command
{
    /**
     * Define descriptions for the command and it's arguments/options.
     *
     * @param string $description                   Description of the command.
     * @param array  $argumentAndOptionDescriptions Descriptions of the arguments and options.
     *
     * @api
     */
    public function descriptions($description, array $argumentAndOptionDescriptions = [])
    {
        $definition = $this->getDefinition();

        $this->setDescription($description);

        foreach ($argumentAndOptionDescriptions as $name => $value) {
            if (strpos($name, '--') === 0) {
                $name = substr($name, 2);
                $this->setOptionDescription($definition, $name, $value);
            } else {
                $this->setArgumentDescription($definition, $name, $value);
            }
        }
    }

    /**
     * Define default values for the arguments of the command.
     *
     * @param array $argumentDefaults Default argument values.
     *
     * @return $this
     *
     * @api
     */
    public function defaults(array $argumentDefaults = [])
    {
        $definition = $this->getDefinition();

        foreach ($argumentDefaults as $name => $default) {
            $argument = $definition->getArgument($name);
            $argument->setDefault($default);
        }

        return $this;
    }

    private function setArgumentDescription(InputDefinition $definition, $name, $description)
    {
        $argument = $definition->getArgument($name);
        if ($argument instanceof InputArgument) {
            $argument->setDescription($description);
        }
    }

    private function setOptionDescription(InputDefinition $definition, $name, $description)
    {
        $argument = $definition->getOption($name);
        if ($argument instanceof InputOption) {
            $argument->setDescription($description);
        }
    }
}
