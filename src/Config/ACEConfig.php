<?php

namespace DalaiLomo\ACE\Config;

use DalaiLomo\ACE\Helper\CommandOutputHelper;
use RomaricDrigon\MetaYaml\Loader\Loader;
use RomaricDrigon\MetaYaml\MetaYaml;

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

    /**
     * @var string
     */
    private $schema = <<<YML
root:
    _type: prototype
    _prototype:
        _type: array
        _not_empty: true
        _children:
            highlight-keywords:
                _type: prototype
                _prototype:
                    _type: text
                    _not_empty: true
            command-groups:
                _type: prototype
                _required: true
                _prototype:
                    _type: prototype
                    _prototype:
                        _type: text
                        _not_empty: true
YML;

    /**
     * @var Loader
     */
    private $loader;

    /**
     * @var MetaYaml
     */
    private $metaYaml;

    public function __construct($configFilePath, Loader $loader)
    {
        $this->loader = $loader;
        $this->config = $this->loader->loadFromFile($configFilePath);
        $this->configFilePath = $configFilePath;
    }

    public function validateConfig()
    {
        $this->metaYaml = new MetaYaml($this->loader->load($this->schema), true);
        $this->metaYaml->validate($this->config);
    }

    public function reloadConfig()
    {
        $this->config = $this->loader->loadFromFile($this->configFilePath);
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

    public function getSummary()
    {
        $output = '';

        $this->onEachKey(function($group, $key) use (&$output) {
            $output .= "--key {$key}" . PHP_EOL . CommandOutputHelper::oldSchoolSeparator();

            $this->onEachGroup($key, function($commands, $groupName) use(&$output) {
                $output .= sprintf(
                    "  %s\n    %s\n\n", $groupName, implode(PHP_EOL . "    ", $commands)
                );
            });
        });

        return $output;
    }

    private function throwExceptionOnInvalidKey($key)
    {
        if (false === isset($this->config[$key])) {
            throw new InvalidKeyException("Invalid key: '{$key}'");
        }
    }
}

