# Slackhook
Slackhook is a simple PHP > 5.4 package for creating messages in Slack that utilize Slack attachments for updating your team on various events that take place within your application.

Slackhook was originally written for use with OpenCart to remove admin and additional email notifications, but has been refactored and updated to use composer. So if you have any PHP application, composer based or not, you can simply drop Slackhook wherever you need to store it, and use an `include` to import it, or if you are running a composer based app, import the class and off you go.

## Installation

Installing composer is beyond the scope of this project.

Go to [composer.org](http://composer.org) to learn more about installing Composer if you haven't already.

```bash
$ composer install 19peaches/slackhook
```

## Classes

Slackhook includes 2 main classes `SlackHook` and `SlackTemplate`.

### SlackHook

The `SlackHook` class provides the concrete implementation for creating, building, and sending your message.

### SlackTemplate

The `SlackTemplate` class provides methods for building specific attachments for your application. Included with the repo is a simple ecommerce sale template for executing when a sale is made on your shopping cart.

You should build out new templates for different events within this class, or by extending the class in your application.

```php
<?php

namespace App\Controller;

use SlackHook\SlackTemplate;

class AppSlackTemplate extends SlackTemplate {

	public function register($data) {

		// build out and return your template as an array here
	}
}
```

> NOTE: The `SlackTemplate` class included with the repo is not required as part of the `SlackHook` implementation, so you're free to write your own template class and implement it from wherever you like. As long at the message you pass to `SlackHook` is a valid JSON Slack attachment you're golden.
