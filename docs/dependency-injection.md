---
currentMenu: dependency-injection
---
# Dependency injection

Silly helps you to do dependency injection without forcing you to use a specific dependency injection container.

## Choose your container

Silly uses the [container-interop standard](https://github.com/container-interop/container-interop) in order to be compatible with any dependency injection container. The idea is simple: you can set up Silly to use the implementation you prefer.

```php
$app->useContainer($container);
```

You can retrieve the container using `$app->getContainer()`.

## Callables in the container

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

## Dependency injection in parameters

Storing your command callables in a container (as shown above) is a good solution for using dependency injection with classes.

However if you define your commands using closures, you cannot benefit of dependency injection. To solve that problem, Silly supports dependency injection in your callables's parameters.

Confused? Don't worry, this is similar to how [dependency injection is implemented in AngularJS](https://docs.angularjs.org/guide/di):

```js
angular.controller('MyController', function (myService, myOtherService) {
  // ...
});
```

In Silly, it would look like this:

```php
use Psr\Logger\LoggerInterface;

$app->command('process [directory]', function (LoggerInterface $logger, $directory) {
    $logger->info('Processing directory ' . $directory);

    // ...
});
```

Silly can inject services and values into parameters by looking into the container using:

- the **type-hint** (i.e. the interface/class name): `Psr\Logger\LoggerInterface`
- the **parameter's name**: `logger`

Depending on how you declare your container entries you might want to enable one or the other way, or both.

```php
$app->useContainer($container, $injectByTypeHint = true, $injectByParameterName = true);
```

*Note that by default both options are disabled.*

If you set both to `true`, it will first look using the type-hint, then using the parameter name. In case of conflict with a command parameters, the command parameter is injected in priority over dependency injection.

Remember again that the order of parameters doesn't matter, even when you mix dependency injection with the command parameters. Here is an example:

```php
$app->command('process [directory]', function ($output, Logger $logger, $directory) {
    // ...
});
```

## Learn more

Are you a library/framework developer interested in such features? Do you want to understand how all this work in details? Silly uses the [Invoker](https://github.com/mnapoli/Invoker#built-in-support-for-dependency-injection) library for supporting dependency injection.
