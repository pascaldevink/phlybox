<?php

namespace pascaldevink\Phlybox\Configuration\Listener\Notification;

use pascaldevink\Phlybox\Configuration\ConfigurationEvent;
use pascaldevink\Phlybox\Configuration\Listener\ConfigurationEventListener;
use pascaldevink\Phlybox\Configuration\Model\Notification\SlackNotificationConfiguration;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SlackNotificatonConfigurationListener implements ConfigurationEventListener
{
    const NOTIFICATION_TYPE = 'slack';

    /**
     * Is called if a configuration event this listener is listening for has been dispatched.
     * Gets the needed configuration elements from the raw configuration and stores it in the Configuration
     * object. @see pascaldevink\Phlybox\Configuration\Model\Configuration
     *
     * @param ConfigurationEvent $configurationEvent
     * @return void
     */
    public function onConfigurationEvent(ConfigurationEvent $configurationEvent)
    {
        $rawConfiguration = $configurationEvent->getRawConfiguration();
        $configuration = $configurationEvent->getConfiguration();

        $notificationType = $rawConfiguration->read('phlybox/notification');
        if ($notificationType !== self::NOTIFICATION_TYPE) {
            return;
        }

        $configurationEvent->stopPropagation();

        $notificationConfiguration = new SlackNotificationConfiguration();
        $notificationConfiguration->setChannel($rawConfiguration->read('phlybox/slack_channel'));
        $notificationConfiguration->setTeam($rawConfiguration->read('phlybox/slack_team'));
        $notificationConfiguration->setToken($rawConfiguration->read('phlybox/slack_token'));
        $notificationConfiguration->setUsername($rawConfiguration->read('phlybox/slack_username'));

        $configuration->setNotificationConfiguration($notificationConfiguration);
        $configurationEvent->setConfiguration($configuration);
    }
}