<?php

namespace DalaiLomo\ACE\Process;

use React\ChildProcess\Process;

interface ProcessMonitoring
{
    public function add(Process $process) : void;

    public function onPeriodicTimerTick() : \Closure;
}
