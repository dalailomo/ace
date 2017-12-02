<?php

namespace DalaiLomo\ACE\Setup\Section\ExecutorSection;

use DalaiLomo\ACE\Command\ExecuteCommand;
use DalaiLomo\ACE\Helper\CommandOutputHelper;
use DalaiLomo\ACE\Setup\Section\AbstractSection;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\ConsoleOutput;

class ExecutorGroupSection extends AbstractSection
{
    /**
     * @var array
     */
    private $groups;

    /**
     * @var string
     */
    private $key;

    public function __construct(array $groups, string $key)
    {
        $this->groups = $groups;
        $this->key = $key;
    }

    public function getSectionName()
    {
        return sprintf('Execute group with key "%s"', $this->key);
    }

    public function doAction()
    {
        $input = new ArrayInput([
            '--key' => $this->key,
        ]);

        $output = new ConsoleOutput();
        $output->setDecorated(true);

        $executeCommand = new ExecuteCommand();
        $executeCommand->run($input, $output);

        readline('Finished. Press "Enter" to continue');

        $this->output->writeln(CommandOutputHelper::clearOutput());
    }
}
