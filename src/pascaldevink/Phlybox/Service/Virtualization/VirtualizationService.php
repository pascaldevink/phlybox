<?php

namespace pascaldevink\Phlybox\Service\Virtualization;

interface VirtualizationService
{
    /**
     * @return string
     */
    public function generateBoxName();

    /**
     * Generates a random IP address within the range of the base IP that is given.
     *
     * @param $ipBase string in the form of 10.0.0.0/8
     * @return bool|string
     */
    public function generateBoxIp($ipBase);

    public function up($boxName, $boxIp);

    public function down($boxName);
} 