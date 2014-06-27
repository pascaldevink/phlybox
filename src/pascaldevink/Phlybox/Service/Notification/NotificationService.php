<?php

namespace pascaldevink\Phlybox\Service\Notification;

interface NotificationService
{
    /**
     * Send out a notification
     *
     * @param string $message
     *
     * @return void
     */
    public function notify($message);
} 