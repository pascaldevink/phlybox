<?php

namespace pascaldevink\Phlybox\Service\Configuration;

use Gaufrette\Adapter\Local;
use Gaufrette\Filesystem;
use Puzzle\Configuration\Yaml;
use Puzzle\Configuration;

class YamlConfigReaderService implements ConfigReaderService
{
    private $workingDirectory;

    public function __construct($workingDirectory)
    {
        $this->workingDirectory = $workingDirectory;
    }

    /**
     * Returns a configuration object based on the yaml configuration in the working directory. @see __construct
     *
     * @return Configuration
     */
    public function getConfiguration()
    {
        $fileSystem = new Filesystem(
            new Local($this->workingDirectory)
        );
        $config = new Yaml($fileSystem);


        return $config;
    }

    /**
     * Returns the configured ip base.
     *
     * @return string
     */
    public function getIpBase()
    {
        $configuration = $this->getConfiguration();
        return $configuration['ip_base'];
    }

    /**
     * Returns the configured notification service with the bundled service configuration, or false if none is
     * configured.
     *
     * @return array|bool
     */
    public function getNotificationService()
    {
        $configuration = $this->getConfiguration();

        if (! isset($configuration['notification'])) {
            return false;
        }

        $serviceName = $configuration['notification'];
        $serviceConfiguration = array();

        foreach($configuration as $key => $value) {
            if (strpos($key, $serviceName) === 0) {
                $serviceConfiguration[$key] = $value;
            }
        }

        return array(
            'serviceName'           => $serviceName,
            'serviceConfiguration'  => $serviceConfiguration,
        );
    }
}