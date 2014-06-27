<?php

namespace pascaldevink\Phlybox\Service;

use pascaldevink\Phlybox\Exception\NotificationServiceNotFoundException;

class NotificationServiceFactory
{

    /**
     * Generates a notification service based on the given name with the given configuration.
     * Throws an exception if the service wasn't found.
     *
     * @param $serviceName
     * @param $configuration
     *
     * @return NotificationService
     *
     * @throws \pascaldevink\Phlybox\Exception\NotificationServiceNotFoundException
     */
    public static function generate($serviceName, $configuration)
    {
        switch ($serviceName) {
            case 'slack':
                return new SlackNotificationService(
                    $configuration['slack_team'],
                    $configuration['slack_token'],
                    '#' . $configuration['slack_channel'],
                    $configuration['slack_username']
                );
                break;
            default:
                throw new NotificationServiceNotFoundException($serviceName);
        }
    }
}
