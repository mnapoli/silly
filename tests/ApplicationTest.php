<?php

namespace Silly\Test;

use EasyMock\EasyMock;
use Invoker\InvokerInterface;
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
        $invoker = EasyMock::mock(InvokerInterface::class);

        $this->application->setInvoker($invoker);

        $this->assertSame($invoker, $this->application->getInvoker());
    }
}
