<?php

namespace DalaiLomo\ACE\Helper;

use DalaiLomo\ACE\Process\ProcessMonitoring;
use DalaiLomo\ACE\Process\ProcessMonitoring\Darwin;
use Symfony\Component\Console\Output\OutputInterface;

class OS
{
    private static $monitorMap = [
        'Darwin' => Darwin::class
    ];

    public static function getProcessMonitoring(OutputInterface $outputInterface) : ?ProcessMonitoring
    {
        $osName = trim(shell_exec('uname'));

        return isset(self::$monitorMap[$osName]) ? new self::$monitorMap[$osName]($outputInterface) : null;
    }
}
