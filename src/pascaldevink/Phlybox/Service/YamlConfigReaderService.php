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
}