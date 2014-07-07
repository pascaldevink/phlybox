<?php

namespace pascaldevink\Phlybox\Configuration\Listener;

use pascaldevink\Phlybox\Configuration\ConfigurationEvent;

interface ConfigurationEventListener
{
    /**
     * Is called if a configuration event this listener is listening for has been dispatched.
     * Gets the needed configuration elements from the raw configuration and stores it in the Configuration
     * object. @see pascaldevink\Phlybox\Configuration\Model\Configuration
     *
     * @param ConfigurationEvent $configurationEvent
     * @return void
     */
    public function onConfigurationEvent(ConfigurationEvent $configurationEvent);
} 