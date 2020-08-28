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
     * @return $this
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

        return $this;
    }

    /**
     * Define default values for the arguments of the command.
     *
     * @param array $defaults Default argument values.
     *
     * @return $this
     *
     * @api
     */
    public function defaults(array $defaults = [])
    {
        $definition = $this->getDefinition();

        foreach ($defaults as $name => $default) {
            if ($definition->hasArgument($name)) {
                $input = $definition->getArgument($name);
            } elseif ($definition->hasOption($name)) {
                $input = $definition->getOption($name);
            } else {
                throw new \InvalidArgumentException("Unable to set default for [{$name}]. It does not exist as an argument or option.");
            }

            $input->setDefault($default);
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
