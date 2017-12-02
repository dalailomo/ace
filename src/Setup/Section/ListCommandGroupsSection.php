<?php

namespace DalaiLomo\ACE\Setup\Section;

use DalaiLomo\ACE\Helper\CommandOutputHelper;
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
        file_put_contents(self::TMP_FILE, $this->config->getSummary());
        $process = new Process('less ' . self::TMP_FILE);

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
