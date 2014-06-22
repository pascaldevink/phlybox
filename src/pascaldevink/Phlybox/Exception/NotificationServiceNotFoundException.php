<?php

namespace pascaldevink\Phlybox\Exception;

use Exception;

class NotificationServiceNotFoundException extends \Exception
{
    public function __construct($serviceName = "")
    {
        $message = "Service with name '$serviceName' not found";

        parent::__construct($message, 0, null);
    }
}
