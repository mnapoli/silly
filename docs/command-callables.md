---
currentMenu: callable
---
# Command callables

A command can be [any PHP callable](http://php.net/manual/en/language.types.callable.php):

```php
// Closure
$app->command('foo', function () {
    // ...
});

// An object implementing __invoke()
$app->command('foo', new InvokableObject());

// An object method
$app->command('foo', [$object, 'method']);

// A static class method
$app->command('foo', ['MyClass', 'method']);

// A function
$app->command('foo', 'someFunction');
```

## Parameters

The callable can take as parameters the arguments and options defined in the expression:

```php
$app->command('greet name [--yell]', function ($name, $yell) {
    // ...
});
```

The order of parameters doesn't matter as they are always matched by name.

When running `$ bin/console greet john --yell`:

- `$name` will be `'john'`
- `$yell` will be `true`

### Input and output

You can also ask for the `$input` and `$output` parameters to get the traditional Symfony `InputInterface` and `OutputInterface` objects (the type-hint is optional):

```php
$app->command(
    'greet name [--yell]',
    function (InputInterface $input, OutputInterface $output) {
        $name = $input->getArgument('name');
        $yell = $input->getOption('yell');

        // ...
    }
);
```

Finally, you can mix all that (remember the order of parameters doesn't matter):

```php
$app->command(
    'greet name [--yell]',
    function ($name, InputInterface $input, OutputInterface $output) {
        // ...
    }
);
```

### Dependency injection

It is also possible to set up dependency injection through the callables parameters. To learn more about that, read the [Dependency injection](dependency-injection.md) documentation.
