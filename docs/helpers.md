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

## Desktop notifications

You can easily send desktop notifications thanks to the [JoliNotif](https://github.com/jolicode/JoliNotif) package:

![](https://github.com/jolicode/JoliNotif/raw/master/doc/images/demo.gif)

Install it with Composer:

```
composer require jolicode/jolinotif
```

You can then either configure your container to inject the `Notifier` instance, or create it manually:

```php
use Joli\JoliNotif\Notification;
use Joli\JoliNotif\NotifierFactory;

[…]

$notifier = NotifierFactory::create();

$app->command('greet', function () use (notifier) {
    $notification = (new Notification)
        ->setTitle('Notification title')
        ->setBody('This is the body of your notification')
    ;

    $notifier->send($notification);
});
```
