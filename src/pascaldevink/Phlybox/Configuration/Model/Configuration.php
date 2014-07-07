<?php

namespace pascaldevink\Phlybox\Configuration\Model;

use pascaldevink\Phlybox\Configuration\Model\Notification\NotificationConfiguration;

class Configuration
{
    /** @var string */
    private $ipBase;

    /** @var NotificationConfiguration */
    private $notificationConfiguration;

    /**
     * @param string $ipBase
     * @return $this
     */
    public function setIpBase($ipBase)
    {
        $this->ipBase = $ipBase;
        return $this;
    }

    /**
     * @return string
     */
    public function getIpBase()
    {
        return $this->ipBase;
    }

    /**
     * @return \pascaldevink\Phlybox\Configuration\Model\Notification\NotificationConfiguration
     */
    public function getNotificationConfiguration()
    {
        return $this->notificationConfiguration;
    }

    /**
     * @param \pascaldevink\Phlybox\Configuration\Model\Notification\NotificationConfiguration $notificationConfiguration
     * @return $this
     */
    public function setNotificationConfiguration($notificationConfiguration)
    {
        $this->notificationConfiguration = $notificationConfiguration;
        return $this;
    }
} 