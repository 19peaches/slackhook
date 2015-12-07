<?php

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
     * Our JSON data property.
     *
     * @var string
     */
    protected $data;

    /**
     * Channel to send message to.
     *
     * @var # string
     */
    protected $channel;

    /**
     * Username the message is from.
     *
     * @var @ string
     */
    protected $username;

    protected $domain;

    protected $site;

    /**
     * Instantiate a new message object.
     *
     * @param  string $uri      webhook uri.
     * @param  string $channel  receiving channel.
     * @param  string $username sending username.
     * @return object $this     chainable instance of class.
     */
    public function __construct(array $config = [])
    {
        // Enter your URI here or pass it in when instantiating the object.
        // If you're passing in the uri make sure it's https protocol
        $this->uri = "https://hooks.slack.com/services/webhook/endpoint";

        if (!empty($config["uri"])) {
            $this->uri = $config["uri"];
        }

        // Enter your channel here or pass it in when instantiating the object.
        // If you want to send to specific user, you can ovveride that in the
        // make method below.
        $this->channel = "#example";

        if (!empty($config["channel"])) {
            $this->channel = "#{$config["channel"]}";
        }

        // Set the username your hook will display in the message
        // or pass it in when instantiating the object.
        $this->username = "example-bot";

        if (!empty($config["username"])) {
            $this->username = $config["username"];
        }

        return $this;
    }

    /**
     * Make our JSON payload request.
     *
     * @param  TemplateProvider $message \Slackhook\Contracts\TemplateProvider
     * @param  bool/string      $user    pass in a username WITH an @ symbol to override channel.
     * @param  string           $color   message bar color.
     * @param  string           $icon    message emoji
     * @return object           $this    chainable instance of class.
     */
    public function make(TemplateProvider $message, $user = false, $color = "#000000", $icon = ":mailbox:")
    {
        // If the user is passed in then our channel needs
        // to be changed to reflect that this is a private
        // message.
        if ($user) {
            $this->channel = $user;
        }

        // Build up our payload data.
        $payload = [
            "channel"     => $this->channel,
			"icon_emoji"  => $icon,
			"username"    => $this->username,
			"attachments" => $this->setAttachments($message, $color)
        ];

        $this->data = json_encode($payload);

        return $this;
    }

    /**
     * Build our attachments array for payload.
     *
     * @param  array  $message our message array to parse.
     * @param  string $color   message bar color.
     * @return array           attachments array.
     */
    protected function setAttachments(array $message, $color)
    {
        $fallback    = false;
        $attachments = [];

        // Fire an exception if there's no text passed
        // in our $message array.
        if (empty($message["text"])) {
            throw new Exception("Your passed in message array must contain a text message.", 1);
        } else {
            // add text to attachment
            $attachments["text"] = $message["text"];
        }

        // Let's build up our fallback message and attachments
        // as we go.
        if (!empty($message["pretext"])) {
            $fallback .= $message["pretext"];
            $attachments["pretext"] = $message["pretext"];
        }

        if (!empty($message["title"])) {
            $fallback .= " - " . $message["title"];
            $attachments["title"] = $message["title"];
        }

        if (!empty($message["title_link"])) {
            $fallback .= " - " . $message["title_link"];
            $attachments["title_link"] = $message["title_link"];
        }

        // Please note that the fallback field is required,
        // and is displayed whenever message attachments
        // cannot be shown (ie. mobile notifications, desktop
        // notifications, IRC). If we have no fallback yet
        // use our text instead.

        if ($fallback) {
            $attachments["fallback"] = $fallback;
        } else {
            $attachments["fallback"] = $message["text"];
        }

        // Add color
        $attachments["color"] = $color;

        // Add in any fields
        if (!empty($message["fields"])) {
            $attachments["fields"] = $message["fields"];
        }

        // Markdown
        if (!empty($message["mrkdwn_in"])) {
            $attachments["mrkdwn_in"] = $message["mrkdwn_in"];
        }

		return [$attachments];
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
