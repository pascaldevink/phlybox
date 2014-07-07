<?php

namespace pascaldevink\Phlybox\Configuration\Model\Notification;

interface NotificationConfiguration
{
    /**
     * Returns the named type of the notification
     *
     * @return string
     */
    public function getNotificationType();
} 