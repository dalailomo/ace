<?php

namespace DalaiLomo\ACE\Process;

use DalaiLomo\ACE\Helper\CommandOutputHelper;
use React\ChildProcess\Process;
use React\EventLoop\LoopInterface;
use React\Stream\Stream;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ProcessGroup
{
    const PERIODIC_TIMER_INTERVAL = 0.5;

    /**
     * @var LoopInterface
     */
    private $loop;

    /**
     * @var InputInterface
     */
    private $input;

    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @var array
     */
    private $commandsOutput = [];

    /**
     * @var ProcessMonitoring
     */
    private $processMonitoring;


    public function __construct(LoopInterface $loop, InputInterface $input, OutputInterface $output)
    {
        $this->loop = $loop;
        $this->input = $input;
        $this->output = $output;
    }

    public function add(Process $process)
    {
        $process->start($this->loop);

        /** @var Stream $stdout */
        $stdout = $process->stdout;

        /** @var Stream $stderr */
        $stderr = $process->stderr;

        $process->on('exit', function() use($process) {
            $this->onProcessExit($process);
        });

        $stdout->on('data', function($streamOutput) use($process) {
            $this->onProcessSTDOUT($process, $streamOutput);
        });

        $stderr->on('data', function($streamOutput) use($process) {
            $this->onProcessSTDERR($process, $streamOutput);
        });

        if ($this->processMonitoring) {
            $this->processMonitoring->add($process);
        }
    }

    public function addProcessMonitoring(ProcessMonitoring $processMonitoring)
    {
        $this->processMonitoring = $processMonitoring;
    }

    public function runLoop()
    {
        if ($this->processMonitoring) {
            $this->loop->addPeriodicTimer(self::PERIODIC_TIMER_INTERVAL, $this->processMonitoring->onPeriodicTimerTick());
        }

        $this->loop->run();
    }

    public function getCommandsOutput()
    {
        return $this->commandsOutput;
    }

    private function onProcessExit(Process $process)
    {
        $this->output->writeln(
            sprintf(
                "Finished <fg=magenta>%s</> : <info>%s</info> : Exit code (<fg=%s>%s</>)",
                $process->getPid(),
                $process->getCommand(),
                ($process->getExitCode() === 0) ? 'green' : 'red',
                $process->getExitCode()
            )
        );
    }

    private function onProcessSTDOUT(Process $process, $streamOutput)
    {
        $this->collectOutput('stdout', $process->getPid(), $streamOutput, $process->getCommand());
    }

    private function onProcessSTDERR(Process $process, $streamOutput)
    {
        $this->collectOutput('stderr', $process->getPid(), $streamOutput, $process->getCommand());

        if (false === $this->input->getOption('diagnosis')) {
            return;
        }

        $this->output->writeln(CommandOutputHelper::ninjaSeparator());
        $this->output->writeln(
            sprintf(
                "<fg=blue>Diagnosis output</> <fg=magenta>%s</> : <info>'%s</info>'",
                $process->getPid(),
                $process->getCommand()
            )
        );
        $this->output->writeln("$streamOutput");
    }

    private function collectOutput($stream, $pid, $outputChunk, $command)
    {
        isset($this->commandsOutput[$pid][$command][$stream])
            ? $this->commandsOutput[$pid][$command][$stream] .= $outputChunk
            : $this->commandsOutput[$pid][$command][$stream] = $outputChunk;
    }
}
