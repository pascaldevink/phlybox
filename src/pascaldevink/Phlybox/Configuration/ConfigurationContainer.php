<?php

namespace pascaldevink\Phlybox\Configuration;

use pascaldevink\Phlybox\Configuration\Listener\Notification\SlackNotificatonConfigurationListener;
use pascaldevink\Phlybox\Configuration\Model\Configuration;
use \Puzzle\Configuration as RawConfiguration;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ConfigurationContainer
{
    const CONFIG_EVENT_NOTIFICATIONS = 'config_event_notifications';

    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    public function get(RawConfiguration $rawConfiguration)
    {
        $configuration = new Configuration();

        $configuration->setIpBase($rawConfiguration->read('phlybox/ip_base'));

        $configurationEvent = new ConfigurationEvent();
        $configurationEvent->setRawConfiguration($rawConfiguration);
        $configurationEvent->setConfiguration($configuration);

        $this->eventDispatcher->dispatch(self::CONFIG_EVENT_NOTIFICATIONS, $configurationEvent);
        $configuration = $configurationEvent->getConfiguration();

        return $configuration;
    }
}
