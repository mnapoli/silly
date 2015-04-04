# Console helpers

You can use [Symfony's console helpers](http://symfony.com/doc/current/components/console/helpers/index.html) by getting them from the application:

```php
$app = new Silly\Application();

$app->command('greet', function ($input, $output) use ($app) {
    $helper = $app->getHelperSet()->get('question');

    $question = new ConfirmationQuestion('Are you sure?', false);

    if ($helper->ask($input, $output, $question)) {
        $output->writeln('Hello!');
    }
});
```
