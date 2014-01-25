<?php

require_once(dirname(__DIR__) . '/vendor/autoload.php');

use pascaldevink\Phlybox\Command\UpCommand;
use Symfony\Component\Console\Application;

$application = new Application();
$application->add(new UpCommand());
$application->run();