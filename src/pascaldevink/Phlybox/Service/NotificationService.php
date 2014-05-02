<?php

namespace pascaldevink\Phlybox\Service;

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