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
| Option with a shortcut                          | `greet [-y|--yell]`      |

Options are always optional (duh). If an option is required, then it should be an argument.

## Default values

```php
$app->command('greet [firstname] [lastname]', function () {
    // ...
})->defaults([
    'firstname' => 'John',
    'lastname'  => 'Doe',
]);
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
