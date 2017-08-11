<?php

namespace DalaiLomo\ACE\Helper;


class CommandOutputHelper
{
    public static function clearOutput()
    {
        return sprintf("\033\143");
    }

    public static function oldSchoolSeparator()
    {
        return "-------------------------------------------------" . PHP_EOL;
    }

    public static function ninjaSeparator()
    {
        return PHP_EOL;
    }
}
