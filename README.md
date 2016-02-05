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

Slackhook includes 1 main class `Slack` and and an interface `TemplateProvider` for providing tempates for your attachments.

### Slack

The `Slack` class provides the concrete implementation for configuring, creating, and sending your message.

### TemplateProvider

The `TemplateProvider` interface provides a contract with one method `make` to build your attachments.

You should build out new templates for different events within this class, or by extending the class in your application.

### Example

In the `Examples` directory we've included an example template for sending a Sale message. Feel free to use this as an example for building out your own templates for different events.

## Usage

Create a new  `Slack` instance.
```php
<?php

$slack = new \Slackhook\Slack([
	'uri'      => 'https://hooks.slack.com/services/your_webhook_uri',
	'channel'  => 'example',
	'username' => 'web-bot',
	'domain'   => 'http://www.example.com',
	'site'     => 'Example Web Site',
	'color'    => '#F8F8F8',
	'icon'     => ':yourcustomicon:'
]);
	
```
Typically you'd store this as a singleton in your DI Container or Registry object so that you can call it anywhere in your app or events.

In OpenCart, we simply store it in the registry object like so:

```php
$this->registry('slack', $slack);

// Now we can access this anywhere via $this->slack
```

We've also included a Laravel config file and ServiceProvider for easy integration into your Laravel apps.

Once your instance is created you can simple create templates for each type of event you need to send a message for. 

```php
<?php

namespace App\Library;

use Slackhook\Slack;
use Slackhook\Contracts\TemplateProvider;

class SlackSale implements TemplateProvider
{

	public function make(array $data) {

		// build out your message here
	}
}
```

> NOTE: The `SlackSale` template included with the repo is not required as part of the `SlackHook` implementation, so you're free to write your own templates and implement them from wherever you like. As long at the template you pass to `Slack` implements the `TemplateProvider` interface your template can contain whatever type of message formatting you like.

As a quick example we can fire off a Slack sale message using the included example template from within our OpenCart `checkout/order` model like so.

```php
<?php

// Import our template.
use App\Library\SlackSale;

// New up our template passing in our Slack instance.
$sale = new SlackSale($this->slack);

// Format our variables that we'll use in the message
// this step is really only needed if you want to pass
// an object in, you could do your formatting here.
$order = $sale->format($order_info, $this->currency);

// Pass your formatted data into the make method
// to create your message.
$message = $sale->make($order);

// Send to the configured default channel or user.
$this->slack->make($message)->send();

// Here we're going to send this message to multiple
// users and channels replacing the emails that would
// normally go out in OpenCart. In our admin settings
// we've replaced our alert emails with users and channels
// that we'll send a Slack message.
// $this->config->get('config_alert_emails') = a string
// @steve,@joe,#websales,@tina The parse method will
// return an array of users and channels
$additional = $sale->parse($this->config->get('config_alert_emails'));

if (!empty($additional['channels'])) {

	foreach ($additional['channels'] as $channel) {
		// set the channel
		$this->slack->set('channel', $channel);
		// send our message to the channel
		$this->slack->make($message)->send();
	}
}

if (!empty($additional['users'])) {

	foreach ($additional['users'] as $user) {
		// send the message to each user
		$this->slack->make($message, $user)->send();
	}
}
```

That's about it. Enjoy and post any problems to issues.