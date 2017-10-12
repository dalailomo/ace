<?php

namespace DalaiLomo\ACE\Log;

use DalaiLomo\ACE\Helper\CommandOutputHelper;

class LogDecorator
{
    /**
     * @var string
     */
    private $logFile;

    /**
     * @var array
     */
    private $parsedLog;

    /**
     * @var string
     */
    private $logOutput;

    // TODO make blacklist configurable
    private $blacklist = [
        'exception'
    ];

    public function __construct($logFile)
    {
        $this->logFile = $logFile;
        $this->parsedLog = json_decode(file_get_contents($logFile), true);
    }

    public function getLogName()
    {
        $logTokens = explode('/', $this->logFile);
        $logLastToken = end($logTokens);
        $logTimestamp = explode('.', $logLastToken)[0];

        $output = array_keys($this->parsedLog)[0] . ' @ ' . date(\DateTime::ISO8601, $logTimestamp);

        return $this->flagFileNameIfBlacklistedContentIsFound($output, $this->logFile);
    }

    public function getStreamsOutput()
    {
        $output = '';

        foreach (reset($this->parsedLog) as $chunkName => $chunk) {
            foreach($chunk as $command) {
                foreach ($command as $commandName => $chunkStreams) {
                    $output .= CommandOutputHelper::oldSchoolSeparator();
                    $output .= $chunkName . ' : ' . $commandName . ' >> STDOUT'.PHP_EOL;
                    $output .= CommandOutputHelper::oldSchoolSeparator();
                    $output .= isset($chunkStreams['stdout']) ? $chunkStreams['stdout'] : '';
                    $output .= CommandOutputHelper::ninjaSeparator();

                    $output .= CommandOutputHelper::oldSchoolSeparator();
                    $output .= $chunkName . ' : ' . $commandName . ' >> STDERR'.PHP_EOL;
                    $output .= CommandOutputHelper::oldSchoolSeparator();
                    $output .= isset($chunkStreams['stderr']) ? $chunkStreams['stderr'] : '';
                    $output .= CommandOutputHelper::ninjaSeparator();
                }
            }
        }

        return $output;
    }

    // TODO: change the way to do this
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
