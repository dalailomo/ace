<?php

namespace DalaiLomo\ACE\Setup\Section;

use DalaiLomo\ACE\Helper\CommandOutputHelper;
use RomaricDrigon\MetaYaml\Exception\NodeValidatorException;
use DalaiLomo\ACE\Helper\Env;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class ListCommandGroupsSection extends AbstractSection
{
    const TMP_FILE = '/tmp/ace_groups';

    public function getSectionName()
    {
        return 'List command groups';
    }

    public function doAction()
    {
        try {
            $this->config->validateConfig();
        } catch (NodeValidatorException $e) {
            $this->output->writeln('<error>Validation of config file failed: ' . $e->getMessage() . '</error>');

            readline('Please check your config file. Press "Enter" to continue');

            $this->output->writeln(CommandOutputHelper::clearOutput());

            return;
        }

        file_put_contents(self::TMP_FILE, $this->config->getSummary());
        $process = new Process(Env::getPager() . ' ' . self::TMP_FILE);

        try {
            $process->setTty(true);
            $process->mustRun(function ($type, $buffer) {
                echo $buffer;
            });
        } catch (ProcessFailedException $e) {
            echo $e->getMessage();
        }

        $this->output->writeln(CommandOutputHelper::clearOutput());
    }
}
