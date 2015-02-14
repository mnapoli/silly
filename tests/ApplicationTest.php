<?php

namespace Silly\Test;

use Silly\Application;

class ApplicationTest extends \PHPUnit_Framework_TestCase
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
    public function should_define_command_descriptions()
    {
        $this->application->command('greet name --yell', function () {});
        $this->application->descriptions('greet', 'Greet someone', [
            'name'   => 'Who?',
            '--yell' => 'Yell?',
        ]);

        $command = $this->application->get('greet');

        $this->assertEquals('Greet someone', $command->getDescription());
        $this->assertEquals('Who?', $command->getDefinition()->getArgument('name')->getDescription());
        $this->assertEquals('Yell?', $command->getDefinition()->getOption('yell')->getDescription());
    }
}
