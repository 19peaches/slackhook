<?php

/*
|--------------------------------------------------------------------------
|  Slackhook Message Package
|--------------------------------------------------------------------------
|
|  Copyright: 19 Peaches, LLC.
|  Author:    Vince Kronlein <vince@19peaches.com>
|
|  For the full copyright and license information, please view the LICENSE
|  file that was distributed with this source code.
|
*/

namespace Slackhook;

use Illuminate\Support\ServiceProvider;

class SlackhookServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('slack', function() {
        	return new Slack([
				'uri'      => config('slack.uri'),
				'channel'  => config('slack.channel'),
				'username' => config('slack.username'),
				'domain'   => config('slack.domain'),
				'site'     => config('slack.site'),
				'color'    => config('slack.color'),
				'icon'     => config('slack.icon')
        	]);
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}