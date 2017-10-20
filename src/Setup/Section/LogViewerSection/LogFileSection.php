<?php

namespace DalaiLomo\ACE\Setup\Section\LogViewerSection;

use DalaiLomo\ACE\Helper\CommandOutputHelper;
use DalaiLomo\ACE\Log\LogDecorator;
use DalaiLomo\ACE\Setup\Section\AbstractSection;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class LogFileSection extends AbstractSection
{
    /**
     * @var LogDecorator
     */
    private $logDecorator;


    public function __construct(LogDecorator $logDecorator)
    {
        $this->logDecorator = $logDecorator;
    }

    public function getSectionName()
    {
        return $this->logDecorator->getLogName();
    }

    public function doAction()
    {
        file_put_contents('/tmp/ace_log_output', $this->logDecorator->getStreamsOutput());
        $process = new Process('less /tmp/ace_log_output');

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
