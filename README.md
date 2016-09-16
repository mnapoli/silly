---
currentMenu: home
---
Silly CLI micro-framework based on Symfony Console.

[![Build Status](https://img.shields.io/travis/mnapoli/silly/master.svg?style=flat-square)](https://travis-ci.org/mnapoli/silly)
[![Coverage Status](https://img.shields.io/coveralls/mnapoli/silly/master.svg?style=flat-square)](https://coveralls.io/r/mnapoli/silly?branch=master)
[![Scrutinizer Code Quality](https://img.shields.io/scrutinizer/g/mnapoli/silly.svg?style=flat-square)](https://scrutinizer-ci.com/g/mnapoli/silly/?branch=master)
[![Packagist](https://img.shields.io/packagist/dt/mnapoli/silly.svg?maxAge=2592000)](https://packagist.org/packages/mnapoli/silly)

## Installation

```bash
$ composer require mnapoli/silly
```

## Usage

Example of a Silly application:

```php
use Symfony\Component\Console\Output\OutputInterface;

$app = new Silly\Application();

$app->command('greet [name] [--yell]', function ($name, $yell, OutputInterface $output) {
    if ($name) {
        $text = 'Hello, '.$name;
    } else {
        $text = 'Hello';
    }

    if ($yell) {
        $text = strtoupper($text);
    }

    $output->writeln($text);
});

$app->run();
```

Running the application is the same as running any other Symfony Console application:

```bash
$ php application.php greet
Hello
$ php application.php greet john --yell
HELLO JOHN
$ php application.php greet --yell john
HELLO JOHN
```

`Silly\Application` extends `Symfony\Console\Application` and can be used wherever Symfony's Application can.

## Documentation

- [Command definition](docs/command-definition.md)
- [Command callables](docs/command-callables.md)
- [Console helpers](docs/helpers.md)
- [Dependency injection](docs/dependency-injection.md)
    - [The PHP-DI edition](docs/php-di.md)
    - [The Pimple edition](docs/pimple.md)

## Do more

Silly is just an implementation over the Symfony Console. Read [the Symfony documentation](http://symfony.com/doc/current/components/console/introduction.html) to learn everything you can do with it.

## Contributing

See the [CONTRIBUTING](CONTRIBUTING.md) file.
