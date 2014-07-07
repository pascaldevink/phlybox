<?php

namespace pascaldevink\Phlybox\Configuration;

use Symfony\Component\EventDispatcher\Event;
use \Puzzle\Configuration as RawConfiguration;

class ConfigurationEvent extends Event
{
    /** @var RawConfiguration */
    private $rawConfiguration;

    /** @var \pascaldevink\Phlybox\Configuration\Model\Configuration */
    private $configuration;

    /**
     * @param \Puzzle\Configuration $rawConfiguration
     * @return $this
     */
    public function setRawConfiguration($rawConfiguration)
    {
        $this->rawConfiguration = $rawConfiguration;
        return $this;
    }

    /**
     * @return \Puzzle\Configuration
     */
    public function getRawConfiguration()
    {
        return $this->rawConfiguration;
    }

    /**
     * @return \pascaldevink\Phlybox\Configuration\Model\Configuration
     */
    public function getConfiguration()
    {
        return $this->configuration;
    }

    /**
     * @param \pascaldevink\Phlybox\Configuration\Model\Configuration $configuration
     * @return $this
     */
    public function setConfiguration($configuration)
    {
        $this->configuration = $configuration;
        return $this;
    }
}
