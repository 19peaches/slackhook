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

namespace Slackhook\Examples;

use Slackhook\Slack;
use Slackhook\Contracts\TemplateProvider;

class SlackSale implements TemplateProvider 
{
    /**
     * Local instance of our Slack object.
     *
     * @var \Slackhook\Slack
     */
    public $hook;

    /**
     * Message template array.
     *
     * @var array
     */
    public $message;

    /**
     * Construct our template object.
     *
     * @param Slack $config \Slackhook\Slack
     */
    public function __construct(Slack $config)
    {
        $this->hook = $config;
    }

    /**
     * Build our template.
     *
     * @param  array  $data    order data from your cart app
     * @return object $this   \Slackhook\Contracts\TemplateProvider
     */
    public function make(array $data)
    {
        // extract data fields from order passed in.
        $fields = [
			[
				"title" => "Item Qty",
				"value" => $data["qty"],
				"short" => true
			],
			[
				"title" => "Total",
				"value" => $data["total"],
				"short" => true
			]
		];

		$this->message = [
			"pretext"    => "*New Sale* on *<" . $this->hook->domain . "|" . $this->hook->site . ">*",
			"title"      => "Sales Order #" . $data["order_id"] . " on " . $this->hook->site,
			"title_link" => $this->hook->domain . "/admin/index.php?route=sale/order/info&order_id=" . $data["order_id"],
			"text"       => "There's been a sale on the *<" . $this->hook->domain . "|" . $this->hook->site . ">* website. \n Click the *<" . $this->hook->domain . "/admin/index.php?route=sale/order/info&order_id=" . $data["order_id"] . "|Sales Order>* link above to check it out.",
            "fields"     => $fields,
            "mrkdwn_in"  => ["text", "pretext"]
		];

        return $this;
    }

    /**
     * Useful method for formatting data before passing
     * it into the make method. Totally optional and
     * need not be included in your template if you
     * don't need it.
     * 
     * @param  array  $order        OpenCart order array as example
     * @param  object $currency     OpenCart currency object as example
     * @return array  $data
     */
    public function format(array $order, $currency) 
    {
        $data = [
            'name'     => $order["firstname"] . ' ' . $order["lastname"],
            'order_id' => $order["order_id"],
            'total'    => $currency->format($order["total"]),
        ];

        return $data;
    }

    /**
     * Used for parsing an array of mixed @users and
     * #channels for sending a single message to 
     * multiple channels/users.
     * 
     * @param  array $users     mixed array of @ and #
     * @return array $data      array of channels and users
     */
    public function parse($users) 
    {
        $data = [];

        $users = explode(',', $users);

        foreach ($users as $user) {
            if (preg_match("/#/i", $user)) {
                // strip # from channel
                $user = str_replace('#', '', $user);
                $data['channels'][] =  $user;
            } elseif (preg_match("/@/i", $user)) {
                // strip @ from user.
                $user = str_replace('@', '', $user);
                $data['users'][] = $user;
            }
        }

        return $data;
    }
}
