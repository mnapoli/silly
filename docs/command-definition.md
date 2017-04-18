---
currentMenu: definition
---
# Command definition

Commands are defined using a string expression.

The expression must start with the command name, optionally using `:` as a namespace separator, for example:

- `greet`
- `demo:greet`

## Arguments

A command can take arguments:

| Description                    | Example         |
|--------------------------------|-----------------|
| Required argument              | `greet name`    |
| Optional argument              | `greet [name]`  |
| Array argument with 0-n values | `greet [name]*` |
| Array argument with 1-n values | `greet name*`   |

## Options

A command can take options:

| Description                                     | Example                  |
|-------------------------------------------------|--------------------------|
| Simple flag (boolean value)                     | `greet [--yell]`         |
| Option with an mandatory value                  | `greet [--iterations=]`  |
| Option that can be used 0-n times (array value) | `greet [--iterations=]*` |
| Option with a shortcut                          | `greet [-y\|--yell]`      |

Options are always optional (duh). If an option is required, then it should be an argument.

## Default values

Default values for arguments and options can be defined explicitly:

```php
$app->command('greet [firstname] [lastname] [--age=]', function () {
    // ...
})->defaults([
    'firstname' => 'John',
    'lastname'  => 'Doe',
    'age' => 25,
]);
```

They can also be inferred from the callback parameters *if it is a callable*:

```php
$app->command('greet [name] [--age=]', function ($name = 'John', $age = 25) {
    // ...
});
```

## Descriptions

```php
$app->command('greet name [--yell]', function () {
    // ...
})->descriptions('Greet someone', [
    'name'   => 'Who do you want to greet?',
    '--yell' => 'If set, the task will yell in uppercase letters',
]);
```

## Hyphens

Arguments and options containing hyphens (`-`) are matched to camelCase variables:

```php
$app->command('run [--dry-run]', function ($dryRun) {
    // ...
});
```

## Single command applications

Sometimes you write an application with a single command, or you want one command to be the default command.

Symfony provides the `setDefaultCommand()` method for that, you can use it like this:

```php
$app->command('run', /* ... */);
$app->setDefaultCommand('run');
```
