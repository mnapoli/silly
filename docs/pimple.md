---
currentMenu: pimple
---
# The Pimple edition

As explained in the [dependency injection](dependency-injection.md) documentation, Silly can work with any dependency injection container.

However in order to provide an easy way to get started we provide a "Pimple edition" that is already configured with the [Pimple container](http://pimple.sensiolabs.org/).

Pimple provides less features than the [PHP-DI edition](php-di.md) (for example no autowiring) but might interest those already familiar with it, e.g. Silex users.

## Installation

```bash
$ composer require mnapoli/silly-pimple
```

## Usage

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

$app = new Silly\Edition\Pimple\Application();
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
