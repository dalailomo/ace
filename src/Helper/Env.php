<?php

namespace DalaiLomo\ACE\Helper;

class Env
{
    public static function getPager()
    {
        $envPager = trim(shell_exec('echo $PAGER'));

        return false === empty($envPager) ? $envPager : 'less';
    }

    public static function getEditor()
    {
        $envEditor = trim(shell_exec('echo $EDITOR'));

        return false === empty($envEditor) ? $envEditor : 'nano';
    }
}