<?php

namespace DalaiLomo\ACE\Setup\Section\LogViewerSection;

use DalaiLomo\ACE\Helper\CommandOutputHelper;
use DalaiLomo\ACE\Setup\Section\AbstractSection;
use DalaiLomo\ACE\Setup\Section\FileReadable;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class LogFileSection extends AbstractSection implements FileReadable
{
    private $filePath;

    // TODO make blacklist configurable
    private $blacklist = [
        'exception'
    ];

    public function getSectionName()
    {
        return $this->decorateLogFileName($this->filePath);
    }

    public function doAction()
    {
        $parsedJson = json_decode(file_get_contents($this->filePath), true);

        $output = '';

        foreach ($parsedJson as $chunk) {
            $output .= CommandOutputHelper::ninjaSeparator();
            $output .= CommandOutputHelper::oldSchoolSeparator();
            $output .= array_keys($chunk)[0] . PHP_EOL;
            $output .= CommandOutputHelper::oldSchoolSeparator();
            $output .= CommandOutputHelper::ninjaSeparator();

            foreach($chunk as $chunkStreams) {
                $output .= 'STDOUT' . PHP_EOL;
                $output .= CommandOutputHelper::oldSchoolSeparator();
                $output .= isset($chunkStreams['stdout']) ? $chunkStreams['stdout'] : '';
                $output .= CommandOutputHelper::ninjaSeparator();

                $output .= 'STDERR' . PHP_EOL;
                $output .= CommandOutputHelper::oldSchoolSeparator();
                $output .= isset($chunkStreams['stderr']) ? $chunkStreams['stderr'] : '';
                $output .= CommandOutputHelper::ninjaSeparator();
            }

            $output .= CommandOutputHelper::ninjaSeparator();
        }

        $process = new Process(sprintf('echo "%s" | less', $output));

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

    public function setFilePath($filePath)
    {
        $this->filePath = $filePath;

        return $this;
    }

    private function decorateLogFileName($logFile)
    {
        $logTokens = explode('/', $logFile);
        $logLastToken = end($logTokens);
        $logTimestamp = explode('.', $logLastToken)[0];

        $output = date(\DateTime::ISO8601, $logTimestamp);

        return $this->flagFileNameIfBlacklistedContentIsFound($output, $logFile);
    }

    private function flagFileNameIfBlacklistedContentIsFound($output, $logFile)
    {
        $fileContents = file_get_contents($logFile);

        foreach ($this->blacklist as $blacklistElement) {
            if (strpos(strtolower($fileContents), strtolower($blacklistElement))) {
                $output .= ' *' . $blacklistElement;
            }
        }

        return $output;
    }

}
