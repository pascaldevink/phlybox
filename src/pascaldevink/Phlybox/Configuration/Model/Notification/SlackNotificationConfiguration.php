<?php

namespace pascaldevink\Phlybox\Configuration\Model\Notification;

class SlackNotificationConfiguration implements NotificationConfiguration
{
    /** @var string */
    private $team;

    /** @var string */
    private $token;

    /** @var string */
    private $channel;

    /** @var string */
    private $username;

    /**
     * @param string $channel
     * @return $this
     */
    public function setChannel($channel)
    {
        $this->channel = $channel;
        return $this;
    }

    /**
     * @return string
     */
    public function getChannel()
    {
        return $this->channel;
    }

    /**
     * @param string $team
     * @return $this
     */
    public function setTeam($team)
    {
        $this->team = $team;
        return $this;
    }

    /**
     * @return string
     */
    public function getTeam()
    {
        return $this->team;
    }

    /**
     * @param string $token
     * @return $this
     */
    public function setToken($token)
    {
        $this->token = $token;
        return $this;
    }

    /**
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @param string $username
     * @return $this
     */
    public function setUsername($username)
    {
        $this->username = $username;
        return $this;
    }

    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }


    /**
     * Returns the named type of the notification
     *
     * @return string
     */
    public function getNotificationType()
    {
        return 'Slack';
    }
}