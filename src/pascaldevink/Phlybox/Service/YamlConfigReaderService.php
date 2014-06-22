<?php

namespace pascaldevink\Phlybox\Service;

use Symfony\Component\Yaml\Parser;

class YamlConfigReaderService implements ConfigReaderService
{

    /**
     * Returns all configuration options as an array.
     *
     * @return array
     */
    public function getConfiguration()
    {
        $yaml = new Parser();
        $value = $yaml->parse(file_get_contents('phlybox.yml'));

        return $value;
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