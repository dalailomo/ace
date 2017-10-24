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
        $output = '';

        $this->config->onEachKey(function($group, $key) use (&$output) {
            $output .= $key . PHP_EOL . CommandOutputHelper::oldSchoolSeparator();

            $this->config->onEachGroup($key, function($commands, $groupName) use(&$output) {
                $output .= sprintf(
                    "%s\n\t%s\n\n", $groupName, implode(PHP_EOL . "\t", $commands)
                );
            });
        });

        file_put_contents(self::TMP_FILE, $output);
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
