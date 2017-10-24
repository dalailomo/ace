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
        $this->throwExceptionOnInvalidKey($key);

        foreach ($this->config[$key][self::COMMAND_GROUPS_KEY][$groupName] as $command) {
            $closure($command);
        }
    }

    public function onEachGroup($key, \Closure $closure, $onDemandGroups = [])
    {
        $this->throwExceptionOnInvalidKey($key);

        if (empty($onDemandGroups)) {
            foreach ($this->config[$key][self::COMMAND_GROUPS_KEY] as $groupName => $commands) {
                $closure($commands, $groupName);
            }

            return;
        }

        foreach ($onDemandGroups as $onDemandGroup) {
            if (isset($this->config[$key][self::COMMAND_GROUPS_KEY][$onDemandGroup])) {
                $closure($this->config[$key][self::COMMAND_GROUPS_KEY][$onDemandGroup], $onDemandGroup);
            }
        }
    }

    public function getGroup($key, $groupName)
    {
        $this->throwExceptionOnInvalidKey($key);

        return isset($this->config[$key][self::COMMAND_GROUPS_KEY][$groupName])
            ? $this->config[$key][self::COMMAND_GROUPS_KEY][$groupName]
            : null;
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

    private function throwExceptionOnInvalidKey($key)
    {
        if (false === isset($this->config[$key])) {
            throw new \InvalidArgumentException("Invalid Key");
        }
    }
}
