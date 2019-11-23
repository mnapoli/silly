---
currentMenu: pimple
---
# Pimple

As explained in the [dependency injection](dependency-injection.md) documentation, Silly can work with any PSR-11 dependency injection container.

[Pimple](https://pimple.symfony.com/) provides less features than the [PHP-DI edition](php-di.md) (for example no autowiring) but might interest those already familiar with it, e.g. Silex users.

## Installation

```bash
$ composer require pimple/pimple ^3.0
```

## Usage

Use Pimple as an application container:

```php
$container    = new \Pimple\Container();
$psrContainer = new \Pimple\Psr11\Container($container);

$app = new \Silly\Application();
$app->useContainer($psrContainer);
```

You can store command callables in the container:

```php
class MyCommand
{
    public function execute($name, OutputInterface $output)
    {
        if ($name) {
            $text = 'Hello, '.$name;
        } else {
            $text = 'Hello';
        }

        $output->writeln($text);
    }
}

$pimple = $app->getContainer();

$pimple['command.greet'] = function () {
    return new MyCommand();
};

$app->command('greet [name]', 'command.greet');

$app->run();
```

## Dependency injection in parameters

You can also use dependency injection in parameters:

```php
use Psr\Logger\LoggerInterface;

// ...

$pimple = $app->getContainer();

$pimple['dbHost'] = 'localhost';
$pimple[LoggerInterface::class] = function () {
    new Monolog\Logger(/* ... */);
};

$app->command('greet [name]', function ($name, $dbHost, LoggerInterface $logger) {
    // ...
});

$app->run();
```

Dependency injection in parameters follows the precedence rules explained in the [dependency injection](dependency-injection.md) documentation:

- command parameters are matched in priority using the parameter names (`$name`)
- then container entries are matched using the callable type-hint (`Psr\Logger\LoggerInterface`)
- finally container entries are matched using the parameter names (`$dbHost`)
