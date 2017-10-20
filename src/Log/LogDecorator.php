<?php

namespace DalaiLomo\ACE\Log;

use DalaiLomo\ACE\Config\ACEConfig;
use DalaiLomo\ACE\Helper\CommandOutputHelper;

class LogDecorator
{
    /**
     * @var string
     */
    private $logName = '';

    /**
     * @var array
     */
    private $parsedLog = [];

    /**
     * @var string
     */
    private $streamsOutput = '';

    /**
     * @var ACEConfig
     */
    private $config;

    public function __construct($logFile, ACEConfig $config)
    {
        $this->parsedLog = json_decode(file_get_contents($logFile), true);
        $this->config = $config;

        $this->processLog($logFile);
    }

    public function getLogName()
    {
        return $this->logName;
    }

    public function getStreamsOutput()
    {
        return $this->streamsOutput;
    }

    private function processLog($logFile)
    {
        $this->onEachCommand(function($key, $groupName, $commandName, $commandStreams) use ($logFile) {
            $this->logName = $this->buildLogName($logFile, $this->parsedLog);
            $this->streamsOutput .= $this->buildOutputFromStreams($groupName, $commandName, $commandStreams);
            $this->logName .= $this->highlightIfKeywordsAreFoundOnStreamsOutput($key);
        });
    }

    private function buildLogName($logFile, $parsedLog)
    {
        $logTokens = explode('/', $logFile);
        $logLastToken = end($logTokens);
        $logTimestamp = explode('.', $logLastToken)[0];

        $output = array_keys($parsedLog)[0] . ' @ ' . date(\DateTime::ISO8601, $logTimestamp);

        return $output;
    }

    private function buildOutputFromStreams($groupName, $commandName, $commandStreams)
    {
        $output = CommandOutputHelper::oldSchoolSeparator();
        $output .= $groupName . ' : ' . $commandName . ' >> STDOUT'.PHP_EOL;
        $output .= CommandOutputHelper::oldSchoolSeparator();
        $output .= isset($commandStreams['stdout']) ? $commandStreams['stdout'] : '';
        $output .= CommandOutputHelper::ninjaSeparator();

        $output .= CommandOutputHelper::oldSchoolSeparator();
        $output .= $groupName . ' : ' . $commandName . ' >> STDERR'.PHP_EOL;
        $output .= CommandOutputHelper::oldSchoolSeparator();
        $output .= isset($commandStreams['stderr']) ? $commandStreams['stderr'] : '';
        $output .= CommandOutputHelper::ninjaSeparator();

        return $output;
    }

    private function onEachCommand(\Closure $closure)
    {
        // ryu would be proud
        foreach ($this->parsedLog as $key => $groups) {
            foreach ($groups as $groupName => $commands) {
                foreach ($commands as $command) {
                    foreach ($command as $commandName => $commandStreams) {
                        $closure($key, $groupName, $commandName, $commandStreams);
                    }
                }
            }
        }
    }

    private function highlightIfKeywordsAreFoundOnStreamsOutput($key)
    {
        $keywords = $this->config->getKeywordsToHighlight($key);
        $output = '';

        foreach ($keywords as $keyword) {
            if (strpos(strtolower($this->streamsOutput), strtolower($keyword))) {
                $output .= ' *' . $keyword;
            }
        }

        return $output;
    }
}
