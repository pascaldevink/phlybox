<?php

namespace pascaldevink\Phlybox\Service\Notification;

use pascaldevink\Phlybox\Configuration\Model\Notification\NotificationConfiguration;
use pascaldevink\Phlybox\Configuration\Model\Notification\SlackNotificationConfiguration;
use pascaldevink\Phlybox\Exception\NotificationServiceNotFoundException;

class NotificationServiceFactory
{

    /**
     * Generates a notification service based on the given configuration.
     * Throws an exception if the service wasn't found.
     *
     * @param NotificationConfiguration $configuration
     *
     * @return NotificationService
     *
     * @throws \pascaldevink\Phlybox\Exception\NotificationServiceNotFoundException
     */
    public static function generate(NotificationConfiguration $configuration)
    {
        switch (true) {
            case $configuration instanceof SlackNotificationConfiguration:
                return new SlackNotificationService(
                    $configuration->getTeam(),
                    $configuration->getToken(),
                    '#' . $configuration->getChannel(),
                    $configuration->getUsername()
                );
                break;
            default:
                throw new NotificationServiceNotFoundException($configuration->getNotificationType());
        }
    }
}
