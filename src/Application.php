<?php

namespace Silly;

use DI\Container;
use DI\ContainerBuilder;
use Silly\Command\ExpressionParser;
use Symfony\Component\Console\Application as SymfonyApplication;
use Symfony\Component\Console\Command\Command;
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

    private function createCommand($expression, callable $callable)
    {
        $result = $this->expressionParser->parse($expression);

        $command = new Command($result['name']);
        $command->getDefinition()->addArguments($result['arguments']);
        $command->getDefinition()->addOptions($result['options']);

        $command->setCode($callable);

        return $command;
    }
}
