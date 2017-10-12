<?php

namespace DalaiLomo\ACE\Log;

use DalaiLomo\ACE\Config\ACEConfig;
use DalaiLomo\ACE\Helper\CommandOutputHelper;

class LogDecorator
{
    /**
     * @var string
     */
    private $logName;

    /**
     * @var array
     */
    private $parsedLog;

    /**
     * @var string
     */
    private $streamsOutput;

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
        $this->onEachCommand(function($key, $chunkName, $commandName, $chunkStreams) use ($logFile) {
            $this->logName = $this->buildLogName($logFile, $this->parsedLog);
            $this->streamsOutput .= $this->buildOutputFromStreams($chunkName, $commandName, $chunkStreams);
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

    private function buildOutputFromStreams($chunkName, $commandName, $chunkStreams)
    {
        $output = CommandOutputHelper::oldSchoolSeparator();
        $output .= $chunkName . ' : ' . $commandName . ' >> STDOUT'.PHP_EOL;
        $output .= CommandOutputHelper::oldSchoolSeparator();
        $output .= isset($chunkStreams['stdout']) ? $chunkStreams['stdout'] : '';
        $output .= CommandOutputHelper::ninjaSeparator();

        $output .= CommandOutputHelper::oldSchoolSeparator();
        $output .= $chunkName . ' : ' . $commandName . ' >> STDERR'.PHP_EOL;
        $output .= CommandOutputHelper::oldSchoolSeparator();
        $output .= isset($chunkStreams['stderr']) ? $chunkStreams['stderr'] : '';
        $output .= CommandOutputHelper::ninjaSeparator();

        return $output;
    }

    private function onEachCommand(\Closure $closure)
    {
        // ryu would be proud
        foreach ($this->parsedLog as $key => $keyChunks) {
            foreach ($keyChunks as $chunkName => $chunk) {
                foreach ($chunk as $command) {
                    foreach ($command as $commandName => $chunkStreams) {
                        $closure($key, $chunkName, $commandName, $chunkStreams);
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
