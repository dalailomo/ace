<?php

namespace DalaiLomo\ACE\Command;

use DalaiLomo\ACE\Chunk\ChunkExecutor;
use DalaiLomo\ACE\Config\ACEConfig;
use DalaiLomo\ACE\Helper\CommandOutputHelper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ExecuteCommand extends Command
{
    /**
     * @var ChunkExecutor
     */
    private $chunkExecutor;

    protected function configure()
    {
        $this
            ->setName('ace:execute')
            ->setDescription('Executes commands asynchronously, weeeee!');

        $this->addOption('diagnosis', 'd', InputOption::VALUE_NONE, 'Show process diagnosis output while running.');
        $this->addOption('key', 'k', InputOption::VALUE_REQUIRED, 'Key of chunks to execute.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $key = $input->getOption('key');
        $config = new ACEConfig(ACE_ROOT_DIR . 'config.yml');

        $this->chunkExecutor = new ChunkExecutor($config, $key, $input, $output);
        $this->chunkExecutor->executeChunks();

        $output->writeln(CommandOutputHelper::ninjaSeparator());
        $output->writeln(sprintf("Time spent: <info>%s seconds</info>", $this->chunkExecutor->getTimeSpent()));

        $output->writeln('Log file: ' . $this->logToFile($key));
        $output->writeln(CommandOutputHelper::ninjaSeparator());

        return 0;
    }

    private function logToFile($key)
    {
        $file = new \SplFileObject(ACE_ROOT_DIR . 'var/log/' . time() . '.log.json', 'w');
        $file->fwrite(json_encode([$key => $this->chunkExecutor->getCommandsOutput()]));
        return $file->getRealPath();
    }
}
