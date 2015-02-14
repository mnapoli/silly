<?php

namespace Silly;

use DI\Container;
use DI\ContainerBuilder;
use Silly\Command\ExpressionParser;
use Silly\Input\InputArgument;
use Silly\Input\InputOption;
use Symfony\Component\Console\Application as SymfonyApplication;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * CLI application.
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class Application extends SymfonyApplication
{
    /**
     * @var ExpressionParser
     */
    private $expressionParser;

    /**
     * @var Container
     */
    private $container;

    public function __construct($name = 'UNKNOWN', $version = 'UNKNOWN')
    {
        $this->expressionParser = new ExpressionParser();
        $this->container = ContainerBuilder::buildDevContainer();

        parent::__construct($name, $version);
    }

    /**
     * Define a CLI command using a string expression and a callable.
     *
     * @param string   $expression Defines the arguments and options of the command.
     * @param callable $callable   Called when the command is called.
     */
    public function command($expression, callable $callable)
    {
        $commandFunction = function (InputInterface $input, OutputInterface $output) use ($callable) {
            $parameters = array_merge(
                [
                    'input'  => $input,
                    'output' => $output,
                ],
                $input->getArguments(),
                $input->getOptions()
            );

            $this->container->call($callable, $parameters);
        };

        $command = $this->createCommand($expression, $commandFunction);

        $this->add($command);
    }

    /**
     * Define descriptions for the command and it's arguments/options.
     *
     * @param string $commandName                   Name of the command.
     * @param string $description                   Description of the command.
     * @param array  $argumentAndOptionDescriptions Descriptions of the arguments and options.
     *
     * @api
     */
    public function descriptions($commandName, $description, array $argumentAndOptionDescriptions = [])
    {
        $command = $this->get($commandName);
        $commandDefinition = $command->getDefinition();

        $command->setDescription($description);

        foreach ($argumentAndOptionDescriptions as $name => $value) {
            if (strpos($name, '--') === 0) {
                $name = substr($name, 2);
                $this->setOptionDescription($commandDefinition, $name, $value);
            } else {
                $this->setArgumentDescription($commandDefinition, $name, $value);
            }
        }
    }

    /**
     * Define default values for the arguments of the command.
     *
     * @param string $commandName      Name of the command.
     * @param array  $argumentDefaults Default argument values.
     *
     * @api
     */
    public function defaults($commandName, array $argumentDefaults = [])
    {
        $command = $this->get($commandName);
        $commandDefinition = $command->getDefinition();

        foreach ($argumentDefaults as $name => $default) {
            $argument = $commandDefinition->getArgument($name);
            $argument->setDefault($default);
        }
    }

    private function createCommand($expression, callable $callable)
    {
        $result = $this->expressionParser->parse($expression);

        $command = new Command($result['name']);
        $command->getDefinition()->addArguments($result['arguments']);
        $command->getDefinition()->addOptions($result['options']);

        $command->setCode($callable);

        return $command;
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
