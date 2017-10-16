<?php

namespace DalaiLomo\ACE\Tests\Config;

use DalaiLomo\ACE\Log\LogScanner;
use PHPUnit\Framework\TestCase;

class LogScannerTest extends TestCase
{
    /**
     * @var LogScanner
     */
    private $logScanner;

    /**
     * @var string
     */
    private $logFilePath = __DIR__ . '/../logFixtures';

    public function setUp()
    {
        parent::setUp();

        $this->logScanner = new LogScanner($this->logFilePath);
    }

    public function testShouldReturnLogFilesSortedByNewest()
    {
        $this->assertEquals([
            0 => '1507803832.log.json',
            1 => '1507803812.log.json',
            2 => '1507803802.log.json',
        ], $this->logScanner->getLogFileList());
    }

    public function testShouldIterateThroughEachLogFile()
    {
        $logFiles = [];

        $this->logScanner->onEachLogFile(function($logFile) use (&$logFiles) {
            $logFiles[] = $logFile;
        });

        $this->assertEquals([
            0 => $this->logFilePath . '1507803832.log.json',
            1 => $this->logFilePath . '1507803812.log.json',
            2 => $this->logFilePath . '1507803802.log.json',
        ], $logFiles);
    }
}
