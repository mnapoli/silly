<?php

namespace Silly\Test;

use PHPUnit\Framework\TestCase;
use Silly\Application;
use Silly\Test\Fixture\SpyOutput;
use Silly\Test\Mock\ArrayContainer;
use stdClass;
use Symfony\Component\Console\Input\Input;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\Console\Output\OutputInterface as Out;
use Symfony\Component\Console\Style\SymfonyStyle;
use RuntimeException;
use InvalidArgumentException;

class FunctionalTest extends TestCase
{
    /**
     * @var Application
     */
    private $application;

    public function setUp(): void
    {
        $this->application = new Application();
        $this->application->setAutoExit(false);
        $this->application->setCatchExceptions(false);
    }

    /**
     * @test
     */
    public function it_should_run_a_simple_command()
    {
        $this->application->command('greet', function (Out $output) {
            $output->write('hello');
        });
        $this->assertOutputIs('greet', 'hello');
    }

    /**
     * @test
     */
    public function it_should_return_the_exit_code()
    {
        $this->application->command('greet', function () {
            return 1;
        });
        $code = $this->application->run(new StringInput('greet'), new SpyOutput());
        $this->assertSame(1, $code);
    }

    /**
     * @test
     */
    public function it_should_inject_the_output_and_input_by_name()
    {
        $this->application->command('greet name', function ($output, $input) {
            $output->write('hello ' . $input->getArgument('name'));
        });
        $this->assertOutputIs('greet john', 'hello john');
    }

    /**
     * @test
     */
    public function it_should_inject_the_output_and_input_by_name_even_if_a_service_has_the_same_name()
    {
        $container = new ArrayContainer([
            'input' => 'foo',
            'output' => 'bar',
        ]);
        $this->application->useContainer($container, false, true);
        $this->application->command('greet name', function ($output, $input) {
            $output->write('hello ' . $input->getArgument('name'));
        });
        $this->assertOutputIs('greet john', 'hello john');
    }

    /**
     * @test
     */
    public function it_should_inject_the_output_and_input_by_type_hint_on_interfaces()
    {
        $this->application->command('greet name', function (Out $out, InputInterface $in) {
            $out->write('hello ' . $in->getArgument('name'));
        });
        $this->assertOutputIs('greet john', 'hello john');
    }

    /**
     * @test
     */
    public function it_should_inject_the_output_and_input_by_type_hint_on_classes()
    {
        $this->application->command('greet name', function (Output $out, Input $in) {
            $out->write('hello ' . $in->getArgument('name'));
        });
        $this->assertOutputIs('greet john', 'hello john');
    }

    /**
     * @test
     */
    public function it_should_inject_the_output_and_input_by_type_hint_even_if_a_service_has_the_same_name()
    {
        $container = new ArrayContainer([
            'in' => 'foo',
            'out' => 'bar',
        ]);
        $this->application->useContainer($container, false, true);
        $this->application->command('greet name', function (Out $out, InputInterface $in) {
            $out->write('hello ' . $in->getArgument('name'));
        });
        $this->assertOutputIs('greet john', 'hello john');
    }

    /**
     * @test
     */
    public function it_should_inject_the_symfony_style_object()
    {
        $this->application->command('greet', function (SymfonyStyle $io) {
            $io->write('hello');
        });
        $this->assertOutputIs('greet', 'hello');
    }

    /**
     * @test
     */
    public function it_should_run_a_command_with_an_argument()
    {
        $this->application->command('greet name', function ($name, Out $output) {
            $output->write('hello ' . $name);
        });
        $this->assertOutputIs('greet john', 'hello john');
    }

    /**
     * @test
     */
    public function it_should_run_a_command_with_an_optional_argument()
    {
        $this->application->command('greet [name]', function ($name, Out $output) {
            $output->write('hello ' . $name);
        });
        $this->assertOutputIs('greet', 'hello ');
        $this->assertOutputIs('greet john', 'hello john');
    }

    /**
     * @test
     */
    public function it_should_run_a_command_with_a_flag()
    {
        $this->application->command('greet [-y|--yell]', function ($yell, Out $output) {
            $output->write(var_export($yell, true));
        });
        $this->assertOutputIs('greet', 'false');
        $this->assertOutputIs('greet -y', 'true');
        $this->assertOutputIs('greet --yell', 'true');
    }

    /**
     * @test
     */
    public function it_should_run_a_command_with_an_option()
    {
        $this->application->command('greet [-i|--iterations=]', function ($iterations, Out $output) {
            $output->write($iterations === null ? 'null' : $iterations);
        });
        $this->assertOutputIs('greet', 'null');
        $this->assertOutputIs('greet -i 123', '123');
        $this->assertOutputIs('greet --iterations=123', '123');
    }

    /**
     * @test
     */
    public function it_should_run_a_command_with_multiple_options()
    {
        $this->application->command('greet [-d|--dir=]*', function ($dir, Out $output) {
            $output->write('[' . implode(', ', $dir) . ']');
        });
        $this->assertOutputIs('greet', '[]');
        $this->assertOutputIs('greet -d foo', '[foo]');
        $this->assertOutputIs('greet -d foo -d bar', '[foo, bar]');
        $this->assertOutputIs('greet --dir=foo --dir=bar', '[foo, bar]');
    }

    /**
     * @test
     */
    public function it_should_match_hyphenated_arguments_to_lowercase_parameters()
    {
        $this->application->command('greet first-name', function ($firstname, Out $output) {
            $output->write('hello ' . $firstname);
        });
        $this->assertOutputIs('greet john', 'hello john');
    }

    /**
     * @test
     */
    public function it_should_match_hyphenated_arguments_to_mixedcase_parameters()
    {
        $this->application->command('greet first-name', function ($firstName, Out $output) {
            $output->write('hello ' . $firstName);
        });
        $this->assertOutputIs('greet john', 'hello john');
    }

    /**
     * @test
     */
    public function it_should_match_hyphenated_option_to_lowercase_parameters()
    {
        $this->application->command('greet [--yell-louder]', function ($yelllouder, Out $output) {
            $output->write(var_export($yelllouder, true));
        });
        $this->assertOutputIs('greet', 'false');
        $this->assertOutputIs('greet --yell-louder', 'true');
    }

    /**
     * @test
     */
    public function it_should_match_hyphenated_option_to_mixed_case_parameters()
    {
        $this->application->command('greet [--yell-louder]', function ($yellLouder, Out $output) {
            $output->write(var_export($yellLouder, true));
        });
        $this->assertOutputIs('greet', 'false');
        $this->assertOutputIs('greet --yell-louder', 'true');
    }

    /**
     * @test
     */
    public function it_can_resolve_a_callable_string_from_a_container()
    {
        $container = new ArrayContainer([
            'command.greet' => function (Out $output) {
                $output->write('hello');
            }
        ]);
        $this->application->useContainer($container);

        $this->application->command('greet', 'command.greet');

        $this->assertOutputIs('greet', 'hello');
    }

    /**
     * @test
     */
    public function it_can_resolve_a_callable_array_from_a_container()
    {
        $container = new ArrayContainer([
            // Calls $this->foo()
            'command.greet' => [$this, 'foo']
        ]);
        $this->application->useContainer($container);

        $this->application->command('greet', 'command.greet');

        $this->assertOutputIs('greet', 'hello');
    }

    /**
     * @test
     */
    public function it_can_inject_using_type_hints()
    {
        $stdClass = new stdClass();
        $stdClass->foo = 'hello';
        $container = new ArrayContainer([
            'stdClass' => $stdClass,
        ]);
        $this->application->useContainer($container, true);

        $this->application->command('greet', function (Out $output, stdClass $param) {
            $output->write($param->foo);
        });

        $this->assertOutputIs('greet', 'hello');
    }

    /**
     * @test
     */
    public function it_can_inject_using_parameter_names()
    {
        $stdClass = new stdClass();
        $stdClass->foo = 'hello';
        $container = new ArrayContainer([
            'param' => $stdClass,
        ]);
        $this->application->useContainer($container, false, true);

        $this->application->command('greet', function (Out $output, $param) {
            $output->write($param->foo);
        });

        $this->assertOutputIs('greet', 'hello');
    }

    /**
     * @test
     */
    public function it_should_inject_command_parameters_in_priority_over_dependency_injection()
    {
        $container = new ArrayContainer([
            'param' => 'bob',
        ]);
        $this->application->useContainer($container, false, true);

        $this->application->command('greet param', function (Out $output, $param) {
            $output->write($param);
        });

        $this->assertOutputIs('greet john', 'john');
    }

    /**
     * @test
     */
    public function it_should_inject_using_type_hint_in_priority_if_both_are_configured()
    {
        $stdClass1 = new stdClass();
        $stdClass1->foo = 'hello';
        $stdClass2 = new stdClass();
        $stdClass2->foo = 'nope!';
        $container = new ArrayContainer([
            'stdClass' => $stdClass1,
            'param'    => $stdClass2,
        ]);
        // Configured to inject both with type-hints and parameter names
        $this->application->useContainer($container, true, true);

        $this->application->command('greet', function (Out $output, stdClass $param) {
            $output->write($param->foo);
        });

        $this->assertOutputIs('greet', 'hello');
    }

    /**
     * Check that `$this` is the application.
     *
     * @test
     */
    public function it_should_run_a_command_in_the_scope_of_the_application()
    {
        $whatIsThis = null;
        $this->application->command('foo', function () use (&$whatIsThis) {
            $whatIsThis = $this;
        });

        $this->assertOutputIs('foo', '');
        $this->assertSame($this->application, $whatIsThis);
    }

    /**
     * @test
     */
    public function it_should_run_a_subcommand()
    {
        $this->application->command('foo', function (Out $output) {
            $output->write('hello');
        });
        $this->application->command('bar', function (Out $output) {
            $this->runCommand('foo', $output);
            $output->write(' world');
        });

        $this->assertOutputIs('bar', 'hello world');
    }

    /**
     * @test
     */
    public function it_should_throw_if_a_parameter_cannot_be_resolved()
    {
        $this->expectExceptionMessage('Impossible to call the \'greet\' command: Unable to invoke the callable because no value was given for parameter 1 ($foo)');
        $this->expectException(RuntimeException::class);
        $this->application->command('greet', function (stdClass $foo) {});
        $this->assertOutputIs('greet', '');
    }

    /**
     * @test
     */
    public function it_should_throw_if_the_command_is_not_a_callable()
    {
        $this->expectExceptionMessage("Impossible to call the 'greet' command: 'foo' is not a callable");
        $this->expectException(RuntimeException::class);
        $this->application->command('greet', 'foo');
        $this->assertOutputIs('greet', '');
    }

    /**
     * @test
     */
    public function it_should_throw_if_the_command_is_a_method_call_to_a_static_method()
    {
        $this->expectExceptionMessage("['Silly\Test\FunctionalTest', 'foo'] is not a callable because 'foo' is a static method. Either use [new Silly\Test\FunctionalTest(), 'foo'] or configure a dependency injection container that supports autowiring like PHP-DI.");
        $this->expectException(InvalidArgumentException::class);
        $this->application->command('greet', [__CLASS__, 'foo']);
        $this->assertOutputIs('greet', '');
    }

    /**
     * @test
     */
    public function it_can_run_as_a_single_command_application()
    {
        $this->application->command('run', function (Out $output) {
            $output->write('hello');
        });
        $this->application->setDefaultCommand('run');
        $this->assertOutputIs('', 'hello');
    }

    private function assertOutputIs($command, $expected)
    {
        $output = new SpyOutput();
        $this->application->run(new StringInput($command), $output);
        $this->assertEquals($expected, $output->output);
    }

    /**
     * Fixture method.
     * @param Out $output
     */
    public function foo(Out $output)
    {
        $output->write('hello');
    }
}
