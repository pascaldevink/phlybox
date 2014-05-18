<?php

namespace pascaldevink\Phlybox\Service;

class VagrantService 
{
    public function generateBoxName()
    {
        return time();
    }

    public function generateBoxIp()
    {
        $subIp = mt_rand(10,255);
        return "192.168.34.$subIp";
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
} 