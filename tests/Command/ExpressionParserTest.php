<?php

namespace Silly\Test\Command;

use PHPUnit\Framework\TestCase;
use Silly\Command\ExpressionParser;
use Silly\Input\InputArgument;
use Silly\Input\InputOption;

class ExpressionParserTest extends TestCase
{
    /**
     * @test
     */
    public function it_parses_command_names()
    {
        $this->assertParsesTo('greet', [
            'name' => 'greet',
            'arguments' => [],
            'options' => [],
        ]);
    }

    /**
     * @test
     */
    public function it_parses_command_names_containing_namespaces()
    {
        $this->assertParsesTo('demo:greet', [
            'name' => 'demo:greet',
            'arguments' => [],
            'options' => [],
        ]);
    }

    /**
     * @test
     */
    public function it_parses_mandatory_arguments()
    {
        $this->assertParsesTo('greet firstname lastname', [
            'name' => 'greet',
            'arguments' => [
                new InputArgument('firstname', InputArgument::REQUIRED),
                new InputArgument('lastname', InputArgument::REQUIRED),
            ],
            'options' => [],
        ]);
    }

    /**
     * @test
     */
    public function it_parses_optional_arguments()
    {
        $this->assertParsesTo('greet [firstname] [lastname]', [
            'name' => 'greet',
            'arguments' => [
                new InputArgument('firstname', InputArgument::OPTIONAL),
                new InputArgument('lastname', InputArgument::OPTIONAL),
            ],
            'options' => [],
        ]);
    }

    /**
     * @test
     */
    public function it_parses_array_arguments()
    {
        $this->assertParsesTo('greet [names]*', [
            'name' => 'greet',
            'arguments' => [
                new InputArgument('names', InputArgument::IS_ARRAY),
            ],
            'options' => [],
        ]);
    }

    /**
     * @test
     */
    public function it_parses_array_arguments_with_at_least_one_value()
    {
        $this->assertParsesTo('greet names*', [
            'name' => 'greet',
            'arguments' => [
                new InputArgument('names', InputArgument::IS_ARRAY | InputArgument::REQUIRED),
            ],
            'options' => [],
        ]);
    }

    /**
     * @test
     */
    public function it_parses_options()
    {
        $this->assertParsesTo('greet [--yell]', [
            'name' => 'greet',
            'arguments' => [],
            'options' => [
                new InputOption('yell', null, InputOption::VALUE_NONE),
            ],
        ]);
    }

    /**
     * @test
     */
    public function it_parses_options_with_mandatory_values()
    {
        $this->assertParsesTo('greet [--iterations=]', [
            'name' => 'greet',
            'arguments' => [],
            'options' => [
                new InputOption('iterations', null, InputOption::VALUE_REQUIRED),
            ],
        ]);
    }

    /**
     * @test
     */
    public function it_parses_options_with_multiple_values()
    {
        $this->assertParsesTo('greet [--name=]*', [
            'name' => 'greet',
            'arguments' => [],
            'options' => [
                new InputOption('name', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY),
            ],
        ]);
    }

    /**
     * @test
     */
    public function it_parses_options_with_shortcuts()
    {
        $this->assertParsesTo('greet [-y|--yell] [-it|--iterations=] [-n|--name=]*', [
            'name' => 'greet',
            'arguments' => [],
            'options' => [
                new InputOption('yell', 'y', InputOption::VALUE_NONE),
                new InputOption('iterations', 'it', InputOption::VALUE_REQUIRED),
                new InputOption('name', 'n', InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY),
            ],
        ]);
    }

    /**
     * @test
     * @expectedException \Silly\Command\InvalidCommandExpression
     * @expectedExceptionMessage An option must be enclosed by brackets: [--option]
     */
    public function it_provides_an_error_message_on_options_missing_brackets()
    {
        $parser = new ExpressionParser();
        $parser->parse('greet --yell');
    }

    public function assertParsesTo($expression, $expected)
    {
        $parser = new ExpressionParser();

        $this->assertEquals($expected, $parser->parse($expression));
    }
}
