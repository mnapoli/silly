<?php

namespace Silly\Test;

use Silly\Application;
use Silly\Test\Fixture\SpyOutput;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\OutputInterface as Out;

class FunctionalTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Application
     */
    private $application;

    public function setUp()
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

    private function assertOutputIs($command, $expected)
    {
        $output = new SpyOutput();
        $this->application->run(new StringInput($command), $output);
        $this->assertEquals($expected, $output->output);
    }
}
