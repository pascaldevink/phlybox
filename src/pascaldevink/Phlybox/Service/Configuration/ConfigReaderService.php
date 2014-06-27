<?php

namespace pascaldevink\Phlybox\Service\Configuration;

interface ConfigReaderService
{
    /**
     * Returns all configuration options as an array.
     *
     * @return array
     */
    public function getConfiguration();
} 