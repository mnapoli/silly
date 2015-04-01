<?php

namespace Silly;

use Interop\Container\ContainerInterface;
use Invoker\Invoker;
use Invoker\InvokerInterface;
use Silly\Command\Command;
use Silly\Command\ExpressionParser;
use Symfony\Component\Console\Application as SymfonyApplication;
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
     * @var InvokerInterface
     */
    private $invoker;

    /**
     * @var ContainerInterface|null
     */
    private $container;

    public function __construct($name = 'UNKNOWN', $version = 'UNKNOWN')
    {
        $this->expressionParser = new ExpressionParser();
        $this->invoker = new Invoker();

        parent::__construct($name, $version);
    }

    /**
     * Define a CLI command using a string expression and a callable.
     *
     * @param string                $expression Defines the arguments and options of the command.
     * @param callable|string|array $callable   Called when the command is called.
     *                                          When using a container, this can be a "pseudo-callable"
     *                                          i.e. the name of the container entry to invoke.
     *
     * @return Command
     */
    public function command($expression, $callable)
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

            $this->invoker->call($callable, $parameters);
        };

        $command = $this->createCommand($expression, $commandFunction);

        $this->add($command);

        return $command;
    }

    public function useContainer(ContainerInterface $container)
    {
        $this->container = $container;
        $this->invoker = new Invoker(null, $container);
    }

    /**
     * Returns the container that has been configured, or null.
     *
     * @return ContainerInterface|null
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * @return InvokerInterface
     */
    public function getInvoker()
    {
        return $this->invoker;
    }

    public function setInvoker(InvokerInterface $invoker)
    {
        $this->invoker = $invoker;
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
