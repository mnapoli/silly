---
currentMenu: helpers
---
# Console helpers

You can use [Symfony's console helpers](http://symfony.com/doc/current/components/console/helpers/index.html) by getting them from the application:

```php
$app->command('greet', function ($input, $output) {
    $helper = $this->getHelperSet()->get('question');

    $question = new ConfirmationQuestion('Are you sure?', false);

    if ($helper->ask($input, $output, $question)) {
        $output->writeln('Hello!');
    }
});
```

## Running a sub-command

Silly implements a little helper to run sub-commands easily:

```php
$app->command('init', function ($input, $output) {
    $this->runCommand('db:drop --force', $output)
    $this->runCommand('db:create', $output)
    $this->runCommand('db:fixtures --verbose', $output)
});
```
