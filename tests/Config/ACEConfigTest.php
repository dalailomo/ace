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

        $this->config->onEachKey(function($groups, $key) use (&$actualKeys) {
            $actualKeys[] = $key;
            $this->assertTrue(is_array($groups));
        });

        $this->assertEquals(['fooKey', 'barKey'], $actualKeys);
    }

    public function onEachGroupProvider()
    {
        return [
            ['fooKey', ['fooGroup']],
            ['barKey', ['bazGroup', 'booGroup']],
        ];
    }

    /**
     * @dataProvider onEachGroupProvider
     */
    public function testShouldIterateThroughCommandGroupsByGivenKey($key, $expectedGroupNames)
    {
        $actualGroupNames = [];

        $this->config->onEachGroup($key, function($commands, $groupName) use (&$actualGroupNames) {
            $actualGroupNames[] = $groupName;
            $this->assertTrue(is_array($commands));
        });

        $this->assertEquals($expectedGroupNames, $actualGroupNames);
    }

    public function onEachProcessProvider()
    {
        return [
            ['fooKey', 'fooGroup', ['echo "a"', 'echo "b"', 'idontexist', 'echo "c"']],
            ['barKey', 'bazGroup', ['echo "hello"', 'echo "world"', 'echo "on fire"']],
            ['barKey', 'booGroup', ['echo "fantasmikos"', 'echo "in the night"', 'echo "oscura"']],
        ];
    }

    /**
     * @dataProvider onEachProcessProvider
     */
    public function testShouldIterateThroughProcessesByGivenKeyAndGroupName($key, $groupName, $expectedProcesses)
    {
        $actualProcesses = [];

        $this->config->onEachProcess($key, $groupName, function($command) use (&$actualProcesses) {
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
