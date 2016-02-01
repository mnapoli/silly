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
