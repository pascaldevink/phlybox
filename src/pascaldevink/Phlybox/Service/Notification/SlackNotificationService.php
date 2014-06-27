<?php

namespace pascaldevink\Phlybox\Service\Notification;

use Slack\Client;
use Slack\Message\Message;
use Slack\Notifier;

class SlackNotificationService implements NotificationService
{
    private $team;
    private $token;
    private $channel;
    private $username;

    public function __construct($team, $token, $channel, $username)
    {
        $this->team = $team;
        $this->token = $token;
        $this->channel = $channel;
        $this->username = $username;
    }

    /**
     * Send out a notification
     *
     * @param string $message
     *
     * @return void
     */
    public function notify($message)
    {
        $client = new Client($this->team, $this->token);
        $slack = new Notifier($client);

        $message = new Message($message);

        $message->setChannel($this->channel);
        $message->setIconEmoji(':shipit:');
        $message->setUsername($this->username);

        $slack->notify($message);
    }
}