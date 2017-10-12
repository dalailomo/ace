<?php

namespace DalaiLomo\ACE\Config;

use Symfony\Component\Yaml\Yaml;

class ACEConfig
{
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

    public function onEachChunk($key, \Closure $closure)
    {
        foreach ($this->config[$key]['command-chunks'] as $chunkName => $commandChunk) {
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
}
