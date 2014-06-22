<?php

namespace pascaldevink\Phlybox\Service;

use Leth\IPAddress\IP\Address;
use Leth\IPAddress\IP\NetworkAddress;

class VagrantService
{
    public function generateBoxName()
    {
        return time();
    }

    /**
     * Generates a random IP address within the range of the base IP that is given.
     *
     * @param $ipBase string in the form of 10.0.0.0/8
     * @return bool|string
     */
    public function generateBoxIp($ipBase)
    {
        $netAddress = NetworkAddress::factory($ipBase);
        $boxIp = $this->generateRandomIp(
            $netAddress->get_network_start()->format(Address::FORMAT_COMPACT),
            $netAddress->get_network_end()->format(Address::FORMAT_COMPACT)
        );

        return $boxIp;
    }

    public function vagrantUp($boxName, $boxIp)
    {
        $command = "cd $boxName && IP=$boxIp vagrant up --provision";
        system($command);
    }

    public function vagrantHalt($boxName)
    {
        $command = "cd $boxName && vagrant halt";
        system($command);
    }

    protected function generateRandomIp($start, $end)
    {
        if (strcmp($start, $end) > 0) {
            return false;
        }

        $arrStart = explode('.', $start);
        $arrEnd = explode('.', $end);

        // First
        $arrIp[0] = rand($arrStart[0], $arrEnd[0]);

        // Second
        if ($arrIp[0] == $arrStart[0] && $arrIp[0] == $arrEnd[0]) {
            $arrIp[1] = rand($arrStart[1], $arrEnd[1]);
        } elseif ($arrIp[0] == $arrStart[0]) {
            $arrIp[1] = rand($arrStart[1], 255);
        } elseif ($arrIp[0] == $arrEnd[0]) {
            $arrIp[1] = rand(0, $arrEnd[1]);
        } else {
            $arrIp[1] = rand(0, 255);
        }

        // Third
        if ($arrIp[1] == $arrStart[1] && $arrIp[1] == $arrEnd[1]) {
            $arrIp[2] = rand($arrStart[2], $arrEnd[2]);
        } elseif ($arrIp[1] == $arrStart[1]) {
            $arrIp[2] = rand($arrStart[2], 255);
        } elseif ($arrIp[1] == $arrEnd[1]) {
            $arrIp[2] = rand(0, $arrEnd[2]);
        } else {
            $arrIp[2] = rand(0, 255);
        }

        // Fourth
        if ($arrIp[2] == $arrStart[2] && $arrIp[02] == $arrEnd[2]) {
            $arrIp[3] = rand($arrStart[3], $arrEnd[3]);
        } elseif ($arrIp[2] == $arrStart[2]) {
            $arrIp[3] = rand($arrStart[3], 255);
        } elseif ($arrIp[2] == $arrEnd[2]) {
            $arrIp[3] = rand(0, $arrEnd[3]);
        } else {
            $arrIp[3] = rand(0, 255);
        }

        return implode(".", $arrIp);
    }
}
