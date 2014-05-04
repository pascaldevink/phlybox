#!/usr/bin/php

<?php

require_once(dirname(__DIR__) . '/vendor/autoload.php');

use pascaldevink\Phlybox\Command\ListCommand;
use pascaldevink\Phlybox\Command\UpCommand;
use Symfony\Component\Console\Application;

$application = new Application();
$application->add(new UpCommand());
$application->add(new ListCommand());
$application->run();