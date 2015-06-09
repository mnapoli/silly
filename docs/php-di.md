---
currentMenu: php-di
---
# The PHP-DI edition

As explained in the [dependency injection](dependency-injection.md) documentation, Silly can work with any dependency injection container.

However in order to provide an easy way to get started we provide a "PHP-DI edition" that is already configured with the [PHP-DI container](http://php-di.org).

## Installation

```bash
$ composer require mnapoli/silly-php-di
```

## Usage

Thanks to PHP-DI's autowiring capabilities you can define your commands in classes:

```php
use Symfony\Component\Console\Output\OutputInterface;

class MyCommand
{
    public function __invoke($name, OutputInterface $output)
    {
        if ($name) {
            $text = 'Hello, '.$name;
        } else {
            $text = 'Hello';
        }

        $output->writeln($text);
    }
}

$app = new Silly\Edition\PhpDi\Application();

$app->command('greet [name]', 'MyCommand');

$app->run();
```

PHP-DI will automatically create a new instance of `MyCommand` when the `greet` command is called.

## Dependency injection in parameters

You can also use dependency injection in parameters:

```php
use Psr\Logger\LoggerInterface;

// ...

$container = $app->getContainer();

$container->set('dbHost', 'localhost');
// Monolog's configuration is voluntarily skipped
$container->set(LoggerInterface::class, DI\object('Monolog\Logger'));

$app->command('greet [name]', function ($name, $dbHost, LoggerInterface $logger) {
    // ...
});

$app->run();
```

Dependency injection in parameters follows the precedence rules explained in the [dependency injection](dependency-injection.md) documentation:

- command parameters are matched in priority using the parameter names (`$name`)
- then container entries are matched using the callable type-hint (`Psr\Logger\LoggerInterface`)
- finally container entries are matched using the parameter names (`$dbHost`)
