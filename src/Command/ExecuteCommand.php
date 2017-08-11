<?php

namespace DalaiLomo\ACE\Command;

use DalaiLomo\ACE\Helper\CommandOutputHelper;
use React\ChildProcess\Process;
use React\EventLoop\Factory as EventFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

class ExecuteCommand extends Command
{
    private $output = [];

    protected function configure()
    {
        $this
            ->setName('ace:execute')
            ->setDescription('Executes commands asynchronously, weeeee!');

        $this->addOption('diagnosis', 'd', InputOption::VALUE_NONE, 'Show process diagnosis output while running.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $start_time = microtime(true);

        $config = Yaml::parse(file_get_contents(ACE_ROOT_DIR . 'config.yml'));

        if (null === $config) {
            $output->writeln("<error>Config file does not exists</error>");
            return -1;
        }

        foreach($config['ace']['command-chunks'] as $chunkName => $commandChunk) {
            $loop = EventFactory::create();

            $output->writeln(CommandOutputHelper::ninjaSeparator());
            $output->writeln(sprintf("Starting chunk <info>%s</info>", $chunkName));

            foreach($commandChunk as $command) {
                $process = new Process($command);
                $process->start($loop);

                $output->writeln(
                    sprintf(
                        "Started <fg=magenta>%s</> : <info>%s</info>",
                        $process->getPid(),
                        $command
                    )
                );

                $process->on('exit', function() use($command, $process, $output) {
                    $output->writeln(
                        sprintf(
                            "Finished <fg=magenta>%s</> : <info>%s</info> : Exit code (<fg=%s>%s</>)",
                            $process->getPid(),
                            $command,
                            ($process->getExitCode() === -1) ? 'red' : 'green',
                            $process->getExitCode()
                        )
                    );
                });

                $process->stdout->on('data', function($outputChunk) use($command, $process) {
                    $this->collectOutput('stdout', $process->getPid(), $outputChunk, $command);
                });

                $process->stderr->on('data', function($outputChunk) use($command, $process, $output, $input) {
                    $this->collectOutput('stderr', $process->getPid(), $outputChunk, $command);

                    if (false === $input->getOption('diagnosis')) {
                        return;
                    }

                    $output->writeln(CommandOutputHelper::ninjaSeparator());
                    $output->writeln(
                        sprintf(
                            "<fg=blue>Diagnosis output</> <fg=magenta>%s</> : <info>'%s</info>'",
                            $process->getPid(),
                            $command
                        )
                    );
                    $output->writeln("$outputChunk");
                });
            }

            $loop->run();
        }

        $end_time = microtime(true);

        $output->writeln(CommandOutputHelper::ninjaSeparator());
        $output->writeln(sprintf("Time spent: <info>%s seconds</info>", round($end_time - $start_time, 2)));

        $output->writeln('Log file: ' . $this->logToFile());
        $output->writeln(CommandOutputHelper::ninjaSeparator());

        return 0;
    }

    private function collectOutput($key, $pid, $outputChunk, $command)
    {
        isset($this->output[$pid][$command][$key])
            ? $this->output[$pid][$command][$key] .= $outputChunk
            : $this->output[$pid][$command][$key] = $outputChunk;
    }

    private function logToFile()
    {
        $file = new \SplFileObject(ACE_ROOT_DIR . 'var/log/' . time() . '.log.json', 'w');
        $file->fwrite(json_encode($this->output));
        return $file->getRealPath();
    }
}
