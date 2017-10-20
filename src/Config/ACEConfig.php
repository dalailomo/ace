<?php

namespace DalaiLomo\ACE\Config;

use Symfony\Component\Yaml\Yaml;

class ACEConfig
{
    const COMMAND_GROUPS_KEY = 'command-groups';
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
        if (false === file_exists($configFilePath)) {
            throw new \InvalidArgumentException('Config file not found');
        }

        $fileContents = file_get_contents($configFilePath);

        $this->config = Yaml::parse($fileContents);
        $this->configFilePath = $configFilePath;
    }

    public function onEachProcess($key, $groupName, \Closure $closure)
    {
        foreach ($this->config[$key][self::COMMAND_GROUPS_KEY][$groupName] as $command) {
            $closure($command);
        }
    }

    public function onEachGroup($key, \Closure $closure)
    {
        foreach ($this->config[$key][self::COMMAND_GROUPS_KEY] as $groupName => $commands) {
            $closure($commands, $groupName);
        }
    }

    public function onEachKey(\Closure $closure)
    {
        foreach($this->config as $key => $groups) {
            $closure($groups, $key);
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
