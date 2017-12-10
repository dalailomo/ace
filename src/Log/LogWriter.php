<?php

namespace DalaiLomo\ACE\Log;

class LogWriter
{
    /**
     * @var string
     */
    private $aceFolder;

    public function __construct(string $aceFolder)
    {
        $this->aceFolder = $aceFolder;
    }

    public function logToFile(array $commandsOutput, string $key)
    {
        $file = new \SplFileObject(sprintf('%s/%s.%s%s', $this->aceFolder, time(), $key, LogScanner::LOG_EXTENSION), 'w');
        $file->fwrite(json_encode([$key => $commandsOutput]));
        return $file->getRealPath();
    }
}