<?php

namespace DalaiLomo\ACE\Process\ProcessMonitoring;

use DalaiLomo\ACE\Process\ProcessMonitoring;
use React\ChildProcess\Process;
use React\EventLoop\Timer\TimerInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Darwin implements ProcessMonitoring
{
    const WARNING_THRESHOLD = 70.0;
    const DANGER_THRESHOLD = 87.0;

    const WARNING_THRESHOLD_COUNT = 3;

    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @var array
     */
    private $processesOnGroup = [];

    /**
     * @var int
     */
    private $warningThresholdCount = 0;

    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
    }

    public function add(Process $process) : void
    {
        $this->processesOnGroup[] = $process;
    }

    public function onPeriodicTimerTick() : \Closure
    {
        return function (TimerInterface $timer) {
            $runningProcesses = $this->getRunningProcesses($this->processesOnGroup);

            if (empty($runningProcesses)) {
                $timer->cancel();
            }

            $cpuUsage = $this->getCurrentCpuUsage();

            if ($cpuUsage['user'] >= self::WARNING_THRESHOLD && $cpuUsage['user'] < self::DANGER_THRESHOLD) {
                $this->warningThresholdCount++;

                if ($this->warningThresholdCount > self::WARNING_THRESHOLD_COUNT) {
                    $this->output->writeln(
                        sprintf(
                            "<fg=yellow>\tWarning, user cpu usage is over %s percent</>",
                            self::WARNING_THRESHOLD
                        )
                    );

                    $this->warningThresholdCount = 0;
                }
            } else {
                if ($cpuUsage['user'] >= self::DANGER_THRESHOLD) {
                    $this->output->writeln(
                        sprintf("<error>\tDANGER, user cpu usage is over %s percent</error>", self::DANGER_THRESHOLD)
                    );
                }
            }
        };
    }

    private function getRunningProcesses(array $processCollection) : array
    {
        return array_reduce(
            $processCollection,
            function (array $acc, Process $process) {
                if ($process->isRunning()) {
                    $acc[] = $process->getPid();
                }

                return $acc;
            },
            []
        );
    }

    private function getCurrentCpuUsage()
    {
        return array_reduce(
            explode(",", $this->topSample('CPU usage:')),
            function ($acc, $carry) {
                $x = explode('%', $carry);
                $acc[trim($x[1])] = (float) $x[0];

                return $acc;
            },
            []
        );
    }

    private function topSample($grep)
    {
        return ltrim(exec(sprintf("top -l 1 | grep '%s'", $grep)), $grep);
    }
}
