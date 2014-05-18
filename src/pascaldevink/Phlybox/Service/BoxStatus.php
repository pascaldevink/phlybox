<?php

namespace pascaldevink\Phlybox\Service;

class BoxStatus 
{
    const STATUS_CLONING = 10;
    const STATUS_MERGING = 20;

    const STATUS_BOOTING = 30;
    const STATUS_READY = 40;

    const STATUS_HALTED = 50;

    static private $statusArray = array(
        self::STATUS_CLONING  => 'cloning',
        self::STATUS_MERGING  => 'merging',
        self::STATUS_BOOTING  => 'booting',
        self::STATUS_READY    => 'ready',
        self::STATUS_HALTED   => 'halted',
    );

    public static function getStatusByNumber($status)
    {
        return self::$statusArray[$status];
    }

    public static function getStatusByName($status)
    {
        $statusArray = array_flip(self::$statusArray);
        return $statusArray[$status];
    }
} 