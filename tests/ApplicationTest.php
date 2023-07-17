<?php

namespace Silly\Test;

use EasyMock\EasyMock;
use Invoker\InvokerInterface;
use PHPUnit\Framework\TestCase;
use Silly\Application;
use Silly\Test\Fixture\SpyOutput;
use Symfony\Component\Console\Output\OutputInterface;

class ApplicationTest extends TestCase
{
    use EasyMock;

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
    public function allows_to_define_commands()
    {
        $command = $this->application->command('foo', function () {
            return 1;
        });

        $this->assertSame($command, $this->application->get('foo'));
    }

    /**
     * @test
     */
    public function allows_to_set_an_invoker()
    {
        /** @var InvokerInterface $invoker */
        $invoker = $this->easyMock(InvokerInterface::class);

        $this->application->setInvoker($invoker);

        $this->assertSame($invoker, $this->application->getInvoker());
    }

    /**
     * @test
     */
    public function runs_a_command()
    {
        $this->application->command('foo', function (OutputInterface $output) {
            $output->write('hello');
        });

        $output = new SpyOutput();
        $code = $this->application->runCommand('foo', $output);

        $this->assertSame('hello', $output->output);
        $this->assertSame(0, $code);
    }

    /**
     * @test
     */
    public function runs_a_command_with_arguments()
    {
        $this->application->command('greet [name] [--yell]', function ($name, $yell, OutputInterface $output) {
            if ($name) {
                $text = 'Hello, '.$name;
            } else {
                $text = 'Hello';
            }

            if ($yell) {
                $text = strtoupper($text);
            }

            $output->write($text);
        });

        $output = new SpyOutput();
        $code = $this->application->runCommand('greet', $output);
        $this->assertSame('Hello', $output->output);
        $this->assertSame(0, $code);

        $output = new SpyOutput();
        $code = $this->application->runCommand('greet John', $output);
        $this->assertSame('Hello, John', $output->output);
        $this->assertSame(0, $code);

        $output = new SpyOutput();
        $code = $this->application->runCommand('greet John --yell', $output);
        $this->assertSame('HELLO, JOHN', $output->output);
        $this->assertSame(0, $code);
    }

    /**
     * @test
     */
    public function runs_a_command_without_output()
    {
        $this->application->command('foo', function (OutputInterface $output) {
            $output->write('hello');
        });

        $code = $this->application->runCommand('foo');

        $this->assertSame(0, $code);
    }

    /**
     * @test
     */
    public function runs_a_command_and_returns_exit_code()
    {
        $this->application->command('foo', function () {
            return 1;
        });
        $this->assertSame(1, $this->application->runCommand('foo'));
    }

    /**
     * @test
     */
    public function runs_a_command_via_its_alias_and_returns_exit_code()
    {
        $this->application->command('foo', function () {
            return 1;
        }, ['bar']);
        $this->assertSame(1, $this->application->runCommand('bar'));
    }
}
