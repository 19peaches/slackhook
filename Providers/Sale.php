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

namespace Slackhook\Providers;

use Slackhook\Slack;
use Slackhook\Contracts\TemplateProvider;

class Sale implements TemplateProvider {

    /**
     * Local instance of our Slack object.
     *
     * @var \Slackhook\Slack
     */
    protected $hook;

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
     * @param  array $data    order data from your cart app
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
				"value" => "$" . $data["order_total"], // you could also pass in a specific currency symbol as well
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
}
