<?php

namespace Silly\Test\Command;

use PHPUnit\Framework\TestCase;
use Silly\Application;
use Silly\Command\Command;
use InvalidArgumentException;

class CommandTest extends TestCase
{
    /**
     * @var Application
     */
    private $application;

    /**
     * @var Command
     */
    private $command;

    public function setUp(): void
    {
        $this->application = new Application();
        $this->application->setAutoExit(false);
        $this->application->setCatchExceptions(false);

        $this->command = $this->application->command('greet [name] [--yell] [--times=]', function () {});
    }

    /**
     * @test
     */
    public function allows_to_define_descriptions()
    {
        $this->command->descriptions('Greet someone', [
            'name'   => 'Who?',
            '--yell' => 'Yell?',
            '--times' => '# of times to greet?',
        ]);

        $definition = $this->command->getDefinition();

        $this->assertSame('Greet someone', $this->command->getDescription());
        $this->assertSame('Who?', $definition->getArgument('name')->getDescription());
        $this->assertSame('Yell?', $definition->getOption('yell')->getDescription());
        $this->assertSame('# of times to greet?', $definition->getOption('times')->getDescription());
    }

    /**
     * @test
     */
    public function allows_to_define_default_values()
    {
        $this->command->defaults([
            'name' => 'John',
            'times' => '1',
        ]);

        $definition = $this->command->getDefinition();

        $this->assertSame('John', $definition->getArgument('name')->getDefault());
        $this->assertSame('1', $definition->getOption('times')->getDefault());
    }

    /**
     * @test
     */
    public function allows_default_values_to_be_inferred_from_closure_parameters()
    {
        $command = $this->application->command('greet [name] [--yell] [--times=]', function ($times = 15) {
            //
        });

        $definition = $command->getDefinition();

        $this->assertSame(15, $definition->getOption("times")->getDefault());
    }

    /**
     * @test
     */
    public function allows_default_values_to_be_inferred_from_camel_case_parameters()
    {
        $command = $this->application->command('greet [name] [--yell] [--number-of-times=]', function ($numberOfTimes = 15) {
            //
        });

        $definition = $command->getDefinition();

        $this->assertSame(15, $definition->getOption("number-of-times")->getDefault());
    }

    /**
     * @test
     */
    public function allows_default_values_to_be_inferred_from_callble_parameters()
    {
        $command = $this->application->command('greet [name] [--yell] [--times=]', [new GreetCommand, "greet"]);

        $definition = $command->getDefinition();

        $this->assertSame(15, $definition->getOption("times")->getDefault());
    }

    /**
     * @test
     */
    public function setting_defaults_falls_back_to_options_when_no_argument_exists()
    {
        $this->command->defaults([
            'times' => '5',
        ]);

        $definition = $this->command->getDefinition();

        $this->assertSame('5', $definition->getOption("times")->getDefault());
    }

    /**
     * @test
     *
     */
    public function setting_unknown_defaults_throws_an_exception()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->command->defaults([
            'doesnotexist' => '0',
        ]);
    }

    /**
     * @test
     */
    public function reflecting_defaults_for_nonexistant_inputs_does_not_throw_an_exception()
    {
        $this->expectNotToPerformAssertions();

        $this->application->command('greet [name]', [new GreetCommand, 'greet']);
        // An exception was thrown previously about the argument / option `times` not existing.
    }

    /**
     * @test
     */
    public function a_command_with_an_invalid_static_callable_show_throw_an_exception()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->application->command('greet [name]', [GreetCommand::class, 'greet']);
    }
}

class GreetCommand
{
    public function greet($times = 15)
    {
        //
    }
}
