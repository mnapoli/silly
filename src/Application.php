<?php

namespace Silly;

use Interop\Container\ContainerInterface;
use Invoker\Exception\InvocationException;
use Invoker\Invoker;
use Invoker\InvokerInterface;
use Invoker\ParameterResolver\AssociativeArrayResolver;
use Invoker\ParameterResolver\Container\ParameterNameContainerResolver;
use Invoker\ParameterResolver\Container\TypeHintContainerResolver;
use Invoker\ParameterResolver\DefaultValueResolver;
use Invoker\ParameterResolver\NumericArrayResolver;
use Invoker\ParameterResolver\ResolverChain;
use Invoker\Reflection\CallableReflection;
use Silly\Command\Command;
use Silly\Command\ExpressionParser;
use Symfony\Component\Console\Application as SymfonyApplication;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\NullOutput;
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
        $this->invoker = new Invoker($this->createParameterResolver());

        parent::__construct($name, $version);
    }

    /**
     * Define a CLI command using a string expression and a callable.
     *
     * @param string $expression Defines the arguments and options of the command.
     * @param callable|string|array $callable Called when the command is called.
     *                                          When using a container, this can be a "pseudo-callable"
     *                                          i.e. the name of the container entry to invoke.
     *
     * @param array $aliases An array of aliases for the command.
     *
     * @return Command
     */
    public function command($expression, $callable, array $aliases = [])
    {
        $this->assertCallableIsValid($callable);

        $commandFunction = function (InputInterface $input, OutputInterface $output) use ($callable) {
            $parameters = array_merge(
                [
                    'input'  => $input,
                    'output' => $output,
                ],
                $input->getArguments(),
                $input->getOptions()
            );

            if ($callable instanceof \Closure) {
                $callable = $callable->bindTo($this, $this);
            }

            try {
                return $this->invoker->call($callable, $parameters);
            } catch (InvocationException $e) {
                throw new \RuntimeException(sprintf(
                    "Impossible to call the '%s' command: %s",
                    $input->getFirstArgument(),
                    $e->getMessage()
                ), 0, $e);
            }
        };

        $command = $this->createCommand($expression, $commandFunction);
        $command->setAliases($aliases);

        $command->defaults($this->defaultsViaReflection($command, $callable));

        $this->add($command);

        return $command;
    }

    /**
     * Set up the application to use a container to resolve callables.
     *
     * Only commands that are *not* PHP callables will be fetched from the container.
     * Commands that are PHP callables are not affected (which is what we want).
     *
     * *Optionally*, you can also enable dependency injection in the callable parameters:
     *
     *     $application->command('greet', function (Psr\Log\LoggerInterface $logger) {
     *         $logger->info('I am greeting');
     *     });
     *
     * Set `$injectByTypeHint` to `true` to make Silly fetch container entries by their
     * type-hint, i.e. call `$container->get('Psr\Log\LoggerInterface')`.
     *
     * Set `$injectByParameterName` to `true` to make Silly fetch container entries by
     * the parameter name, i.e. call `$container->get('logger')`.
     *
     * If you set both to `true`, it will first look using the type-hint, then using
     * the parameter name.
     *
     * In case of conflict with a command parameters, the command parameter is injected
     * in priority over dependency injection.
     *
     * @param ContainerInterface $container Container implementing container-interop
     * @param bool               $injectByTypeHint
     * @param bool               $injectByParameterName
     */
    public function useContainer(
        ContainerInterface $container,
        $injectByTypeHint = false,
        $injectByParameterName = false
    ) {
        $this->container = $container;

        $resolvers = [
            new AssociativeArrayResolver,
            new HyphenatedInputResolver,
        ];
        if ($injectByTypeHint) {
            $resolvers[] = new TypeHintContainerResolver($container);
        }
        if ($injectByParameterName) {
            $resolvers[] = new ParameterNameContainerResolver($container);
        }

        $this->invoker = new Invoker(new ResolverChain($resolvers), $container);
    }

    /**
     * Helper to run a sub-command from a command.
     *
     * @param string $command Command that should be run.
     * @param OutputInterface|null $output The output to use. If not provided, the output will be silenced.
     *
     * @return int 0 if everything went fine, or an error code
     */
    public function runCommand($command, OutputInterface $output = null)
    {
        $input = new StringInput($command);

        $command = $this->find($this->getCommandName($input));

        return $command->run($input, $output ?: new NullOutput());
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

    private function assertCallableIsValid($callable)
    {
        if ($this->container) {
            return;
        }

        if ($this->isStaticCallToNonStaticMethod($callable)) {
            list($class, $method) = $callable;

            $message = "['{$class}', '{$method}'] is not a callable because '{$method}' is a static method.";
            $message .= " Either use [new {$class}(), '{$method}'] or configure a dependency injection container that supports autowiring like PHP-DI.";

            throw new \InvalidArgumentException($message);
        }
    }

    private function defaultsViaReflection($command, $callable)
    {
        if (! is_callable($callable)) {
            return [];
        }

        $function = CallableReflection::create($callable);

        $definition = $command->getDefinition();

        $defaults = [];

        foreach ($function->getParameters() as $parameter) {
            if (! $parameter->isDefaultValueAvailable()) {
                continue;
            }

            if (! $definition->hasArgument($parameter->name) && ! $definition->hasOption($parameter->name)) {
                continue;
            }

            $defaults[$parameter->name] = $parameter->getDefaultValue();
        }

        return $defaults;
    }

    /**
     * Create the default parameter resolver.
     *
     * @return ParameterResolver
     */
    private function createParameterResolver()
    {
        return new ResolverChain([
            new NumericArrayResolver,
            new AssociativeArrayResolver,
            new HyphenatedInputResolver,
            new DefaultValueResolver,
        ]);
    }

    /**
     * Check if the callable represents a static call to a non-static method.
     *
     * @param mixed $callable
     * @return bool
     */
    private function isStaticCallToNonStaticMethod($callable)
    {
        if (is_array($callable) && is_string($callable[0])) {
            list($class, $method) = $callable;
            $reflection = new \ReflectionMethod($class, $method);

            return ! $reflection->isStatic();
        }

        return false;
    }
}
