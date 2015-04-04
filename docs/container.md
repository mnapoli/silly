# Dependency injection

Silly is a micro-framework that helps you to do dependency injection without forcing you to use a specific dependency injection container.

## Choose your container

Silly uses the [container-interop standard](https://github.com/container-interop/container-interop) in order to be compatible with any dependency injection container. The idea is simple: you can set up Silly to use the implementation you prefer.

```php
$app->useContainer($container);
```

## Features

Once you have configured a dependency injection container, Silly can use it in several ways.

### Callables in the container

By registering a container with `$app->useContainer()`, you can now store your callables inside your containers and Silly with be able to resolve them:

```php
$app->command('greet [name]', 'the-service-id');
```

This allows to define commands as PHP classes that can use dependency injection (given the class is resolved from the container).

Here is an example by defining a command with an [invokable class](http://php.net/manual/en/language.oop5.magic.php#object.invoke):

```php
class ScanCommand
{
    private $scanner;

    public function __construct(Scanner $scanner)
    {
        $this->scanner = $scanner;
    }

    public function __invoke($directory)
    {
        $this->scanner->scan($directory);
    }
}

$app->command('process [directory]', 'MyApp\Command\ScanCommand');
```

Here `'MyApp\Command\ScanCommand'` is a container entry ID, so it can be a class name or any other string, as long as your container can resolve it.

Here is another example using an object method:

```php
class ScanCommand
{
    // ...

    public function execute($directory)
    {
        $this->scanner->scan($directory);
    }
}

$app->command('process [directory]', ['MyApp\Command\ScanCommand', 'execute']);
```

You might recognize the PHP callable `['MyApp\Command\ScanCommand', 'execute']` (array callable) except the first item is not an object: it is the ID of a container entry (a class name or any other string, as long as your container can resolve it).
