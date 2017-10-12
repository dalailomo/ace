<?php

namespace DalaiLomo\ACE\Command;

use DalaiLomo\ACE\Chunk\ChunkExecutor;
use DalaiLomo\ACE\Helper\CommandOutputHelper;
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
        $this->addOption('key', 'k', InputOption::VALUE_OPTIONAL, 'Key of chunks to execute.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $key = $input->getOption('key');
        $config = Yaml::parse(file_get_contents(ACE_ROOT_DIR . 'config.yml'));

        if (null === $config) {
            $output->writeln("<error>Config file does not exists</error>");
            return -1;
        }

        if (null === $key) {
            $output->writeln('<error>No key given</error>');
            return -1;
        }

        if (false === isset($config[$key])) {
            $output->writeln(sprintf('<error>Key "%s" is invalid</error>', $key));
            return -1;
        }

        $chunkExecutor = new ChunkExecutor($config, $key, $input, $output);
        $chunkExecutor->executeChunks();

        $output->writeln(CommandOutputHelper::ninjaSeparator());
        $output->writeln(sprintf("Time spent: <info>%s seconds</info>", $chunkExecutor->getTimeSpent()));

        $output->writeln('Log file: ' . $this->logToFile($key));
        $output->writeln(CommandOutputHelper::ninjaSeparator());

        return 0;
    }

    private function logToFile($key)
    {
        $file = new \SplFileObject(ACE_ROOT_DIR . 'var/log/' . time() . '.log.json', 'w');
        $file->fwrite(json_encode([$key => $this->output]));
        return $file->getRealPath();
    }
}
