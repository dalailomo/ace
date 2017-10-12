<?php

namespace DalaiLomo\ACE\Config;

use Symfony\Component\Yaml\Yaml;

class ACEConfig
{
    const COMMAND_CHUNKS_KEY = 'command-chunks';
    const HIGHLIGHT_KEYWORDS_KEY = 'highlight-keywords';

    /**
     * @var array
     */
    private $config;

    /**
     * @var string
     */
    private $configFilePath;

    public function __construct($configFilePath)
    {
        $fileContents = file_get_contents($configFilePath);

        if (false === $fileContents) {
            throw new \InvalidArgumentException('Config file not found');
        }

        $this->config = Yaml::parse($fileContents);
        $this->configFilePath = $configFilePath;
    }

    public function onEachProcess($key, $chunkName, \Closure $closure)
    {
        foreach ($this->config[$key][self::COMMAND_CHUNKS_KEY][$chunkName] as $command) {
            $closure($command);
        }
    }

    public function onEachChunk($key, \Closure $closure)
    {
        foreach ($this->config[$key][self::COMMAND_CHUNKS_KEY] as $chunkName => $commandChunk) {
            $closure($commandChunk, $chunkName);
        }
    }

    public function onEachKey(\Closure $closure)
    {
        foreach($this->config as $key => $chunks) {
            $closure($chunks, $key);
        }
    }

    public function getConfigFilePath()
    {
        return $this->configFilePath;
    }

    public function getKeywordsToHighlight($key)
    {
        return isset($this->config[$key][self::HIGHLIGHT_KEYWORDS_KEY])
            ? $this->config[$key][self::HIGHLIGHT_KEYWORDS_KEY]
            : [];
    }
}
