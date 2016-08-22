<?php

namespace Silly\Test;

use Silly\Application;
use Silly\Command\Command;

class CommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Application
     */
    private $application;

    /**
     * @var Command
     */
    private $command;

    public function setUp()
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

        $this->assertEquals('Greet someone', $this->command->getDescription());
        $this->assertEquals('Who?', $definition->getArgument('name')->getDescription());
        $this->assertEquals('Yell?', $definition->getOption('yell')->getDescription());
        $this->assertEquals('# of times to greet?', $definition->getOption('times')->getDescription());
    }

    /**
     * @test
     */
    public function allows_to_define_default_values()
    {
        $this->command->defaults([
            'name' => 'John',
            '--times' => '1',
        ]);

        $definition = $this->command->getDefinition();

        $this->assertEquals('John', $definition->getArgument('name')->getDefault());
        $this->assertEquals('1', $definition->getOption('times')->getDefault());
    }
}
