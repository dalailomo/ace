<?php

namespace DalaiLomo\ACE\Tests\Config;

use DalaiLomo\ACE\Config\ACEConfig;
use PHPUnit\Framework\TestCase;

class ACEConfigTest extends TestCase
{
    /**
     * @var ACEConfig
     */
    private $config;

    /**
     * @var string
     */
    private $configFilePathInput = __DIR__ . '/../configtest.yml';

    public function setUp()
    {
        parent::setUp();

        $this->config = new ACEConfig($this->configFilePathInput);
    }

    public function testShouldThrowExceptionIfConfigFileIsNotValid()
    {
        $this->expectException(\InvalidArgumentException::class);

        new ACEConfig('wrong/path/to/config.yml');
    }

    public function testShouldIterateThroughKeys()
    {
        $actualKeys = [];

        $this->config->onEachKey(function($chunks, $key) use (&$actualKeys) {
            $actualKeys[] = $key;
            $this->assertTrue(is_array($chunks));
        });

        $this->assertEquals(['fooKey', 'barKey'], $actualKeys);
    }

    public function onEachChunkProvider()
    {
        return [
            ['fooKey', ['fooChunk']],
            ['barKey', ['bazChunk', 'booChunk']],
        ];
    }

    /**
     * @dataProvider onEachChunkProvider
     */
    public function testShouldIterateThroughCommandChunksByGivenKey($key, $expectedChunkNames)
    {
        $actualChunkNames = [];

        $this->config->onEachChunk($key, function($commandChunk, $chunkName) use (&$actualChunkNames) {
            $actualChunkNames[] = $chunkName;
            $this->assertTrue(is_array($commandChunk));
        });

        $this->assertEquals($expectedChunkNames, $actualChunkNames);
    }

    public function onEachProcessProvider()
    {
        return [
            ['fooKey', 'fooChunk', ['echo "a"', 'echo "b"', 'idontexist', 'echo "c"']],
            ['barKey', 'bazChunk', ['echo "hello"', 'echo "world"', 'echo "on fire"']],
            ['barKey', 'booChunk', ['echo "fantasmikos"', 'echo "in the night"', 'echo "oscura"']],
        ];
    }

    /**
     * @dataProvider onEachProcessProvider
     */
    public function testShouldIterateThroughProcessesByGivenKeyAndChunkName($key, $chunkName, $expectedProcesses)
    {
        $actualProcesses = [];

        $this->config->onEachProcess($key, $chunkName, function($command) use (&$actualProcesses) {
            $actualProcesses[] = $command;
        });

        $this->assertEquals($expectedProcesses, $actualProcesses);
    }

    public function testShouldGiveBackConfigFilePath()
    {
        $this->assertEquals($this->configFilePathInput, $this->config->getConfigFilePath());
    }

    public function keywordsToHighlightProvider()
    {
        return [
            ['fooKey', []],
            ['barKey', ['fire']],
        ];
    }

    /**
     * @dataProvider keywordsToHighlightProvider
     */
    public function testShouldProvideKeywordsToHighlightByKey($key, $expectedKeywords)
    {
        $this->assertEquals($expectedKeywords, $this->config->getKeywordsToHighlight($key));
    }
}
