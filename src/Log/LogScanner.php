<?php

namespace DalaiLomo\ACE\Log;

class LogScanner
{
    const MAX_NUM_LOGS = 10;
    const LOG_EXTENSION = '.log.json';

    /**
     * @var string
     */
    private $logFilePath;

    /**
     * @var array
     */
    private $logFileList = [];

    public function __construct($logFilePath)
    {
        $this->logFilePath = $logFilePath;
        $this->logFileList = $this->scanForLogs($logFilePath);
    }

    public function getLogFileList()
    {
        return $this->logFileList;
    }

    public function onEachLogFile(\Closure $closure)
    {
        $i = 0;
        array_map(function($logFile) use(&$i, $closure) {
            if ($i++ >= self::MAX_NUM_LOGS) {
                return;
            }

            $closure($this->logFilePath . $logFile);
        }, $this->logFileList);
    }

    private function scanForLogs($logFilePath)
    {
        $logFiles = array_filter(scandir($logFilePath), function($file) {
            return strpos($file, self::LOG_EXTENSION);
        });

        arsort($logFiles);

        return $logFiles;
    }
}
