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

use Exception;
use Slackhook\Contracts\TemplateProvider;

class Slack {

    /**
     * Uri to your slack webhook.
     *
     * @var string
     */
    protected $uri;

    /**
     * Template provider instance;
     *
     * @var \Slackhook\Contracts\TemplateProvider
     */
    protected $provider;

    /**
     * Attached and formatted template array;
     *
     * @var array
     */
    protected $attached;

    /**
     * Our JSON data property.
     *
     * @var string
     */
    protected $data;

    /**
     * Channel to send message to.
     *
     * @var string
     */
    protected $channel;

    /**
     * Username the message is from.
     *
     * @var string
     */
    protected $username;

    /**
     * Color for attachments.
     *
     * @var string
     */
    protected $color = "#000000";

    /**
     * Icon for attachments.
     *
     * @var string
     */
    protected $icon = ":mailbox:";

    /**
     * Domain URI for template providers.
     *
     * @var string
     */
    public $domain;

    /**
     * Site name for template providers.
     *
     * @var string
     */
    public $site;

    /**
     * Instantiate a new message object.
     *
     * @param array $config configuration variable
     */
    public function __construct(array $config = [])
    {
        // Make sure we have a uri
        if (empty($config["uri"])) {
            throw new Exception("Your Slack webhook URI is required.", 1);
        }

        $this->uri = $config["uri"];

        // If you want to send to specific user, you can ovveride that in the
        // make method below.
        if (empty($config["channel"])) {
            throw new Exception("Your Slack channel is required", 1);
        }

        $this->channel = "#{$config["channel"]}";

        // Set the username your hook will display in the message.
        if (empty($config["username"])) {
            throw new Exception("A sending Slack username is required.", 1);
        }

        $this->username = $config["username"];

        // Set site domain for use in template providers.
        if (empty($config["domain"])) {
            throw new Exception("Domain is required for template providers.", 1);
        }

        $this->domain = $config["domain"];

        // Set site name for use in template providers.
        if (empty($config["site"])) {
            throw new Exception("Site name is required for template providers.", 1);
        }

        $this->site = $config["site"];

        // Set color if not empty.
        if (! empty($config["color"])) {
            $this->color = $config["color"];
        }

        // Set icon if not empty.
        if (! empty($config["icon"])) {
            $this->icon = $config["icon"];
        }

        return $this;
    }

    /**
     * Make our JSON payload request.
     *
     * @param  TemplateProvider $template \Slackhook\Contracts\TemplateProvider
     * @param  bool/string      $user     pass in a username WITHOUT an @ symbol to override channel.
     * @return object           $this     chainable instance of class.
     */
    public function make(TemplateProvider $template, $user = false)
    {
        // Set our local provider property to passed in object;
        $this->provider = $template;

        // If the user is passed in then our channel needs
        // to be changed to reflect that this is a private
        // message.
        if ($user) {
            $this->channel = "@{$user}";
        }

        // Build our attachment.
        $this->setAttachments();

        // Build up our payload data.
        $payload = [
            "channel"     => $this->channel,
			"icon_emoji"  => $this->icon,
			"username"    => $this->username,
			"attachments" => $this->attached,
        ];

        $this->data = json_encode($payload);

        return $this;
    }

    /**
     * Setter to update properties on the fly.
     *
     * @param string $key   property to change
     * @param string $value new property value
     */
    public function set($key, $value)
    {
        $this->{$key} = $value;
    }

    /**
     * Build our attachments array for payload.
     *
     * @return array attachments array.
     */
    protected function setAttachments()
    {
        $fallback    = false;
        $attachments = [];

        // Fire an exception if there's no text passed
        // in our $message array.
        if (empty($this->provider->message["text"])) {
            throw new Exception("Your passed in message array must contain a text message.", 1);
        } else {
            // add text to attachment
            $attachments["text"] = $this->provider->message["text"];
        }

        // Let's build up our fallback message and attachments
        // as we go.
        if (!empty($this->provider->message["pretext"])) {
            $fallback .= $this->provider->message["pretext"];
            $attachments["pretext"] = $this->provider->message["pretext"];
        }

        if (!empty($this->provider->message["title"])) {
            $fallback .= " - " . $this->provider->message["title"];
            $attachments["title"] = $this->provider->message["title"];
        }

        if (!empty($this->provider->message["title_link"])) {
            $fallback .= " - " . $this->provider->message["title_link"];
            $attachments["title_link"] = $this->provider->message["title_link"];
        }

        // Please note that the fallback field is required,
        // and is displayed whenever message attachments
        // cannot be shown (ie. mobile notifications, desktop
        // notifications, IRC). If we have no fallback yet
        // use our text instead.

        if ($fallback) {
            $attachments["fallback"] = $fallback;
        } else {
            $attachments["fallback"] = $this->provider->message["text"];
        }

        // Add color
        $attachments["color"] = $this->color;

        // Add in any fields
        if (!empty($this->provider->message["fields"])) {
            $attachments["fields"] = $this->provider->message["fields"];
        }

        // Markdown
        if (!empty($this->provider->message["mrkdwn_in"])) {
            $attachments["mrkdwn_in"] = $this->provider->message["mrkdwn_in"];
        }

        $this->attached = [$attachments];
    }

    /**
     * Send our JSON payload to Slack.
     *
     * @return string slack response string.
     */
    public function send()
    {
        $ch = curl_init($this->uri);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, ['payload' => $this->data]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        curl_close($ch);

        unset($this->data);

        return $result;
    }
}
