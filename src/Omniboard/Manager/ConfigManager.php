<?php

declare(strict_types=1);

namespace Omniboard\Manager;

use Omniboard\Omniboard;
use Omniboard\Utils\ConfigKeys;

/**
 * Class ConfigManager
 *
 * Manages the plugin's configuration values.
 */
class ConfigManager
{
    /** @var Omniboard $plugin The main plugin instance */
    private Omniboard $plugin;

    /**
     * ConfigManager constructor.
     *
     * @param Omniboard $plugin The main plugin instance.
     */
    public function __construct(Omniboard $plugin)
    {
        $this->plugin = $plugin;
    }

    /**
     * Get the value from the plugin's configuration.
     *
     * @param string $key The configuration key.
     * @param mixed $default The default value if the key doesn't exist.
     * @return mixed The configuration value.
     */
    public function getConfigValue(string $key, $default = null)
    {
        return $this->plugin->getConfig()->get($key, $default);
    }

    /**
     * Set a value in the plugin's configuration.
     *
     * @param string $key The configuration key.
     * @param mixed $value The value to set.
     */
    public function setConfigValue(string $key, $value): void
    {
        $config = $this->plugin->getConfig();
        $config->set($key, $value);
        $this->plugin->saveConfig();
    }

    /**
     * Get all values from the plugin's configuration.
     *
     * @return array<int|string, mixed> The configuration values.
     */
    public function getAllConfigValues(): array
    {
        return $this->plugin->getConfig()->getAll();
    }

    /**
     * Get a nested value from the plugin's configuration.
     *
     * @param string $keys The nested path to the configuration value.
     * @param mixed $default The default value if the path doesn't exist.
     * @return mixed The nested configuration value.
     */
    public function getNestedConfigValue(string $keys, $default = null): mixed
    {
        return $this->plugin->getConfig()->getNested($keys, $default);
    }

    /**
     * Set a nested value in the plugin's configuration.
     *
     * @param string $keys The nested path to the configuration value.
     * @param mixed $value The value to set.
     */
    public function setNestedConfigValue(string $keys, $value): void
    {
        $config = $this->plugin->getConfig();
        $config->setNested($keys, $value);
        $config->save();
    }

    /**
     * Get the top island position.
     *
     * @return array<mixed, mixed>|null The top island position, if exists.
     */
    public function getTopIslandPosition(): ?array
    {
        $value = $this->getNestedConfigValue(ConfigKeys::TOP_ISLAND_POSITION);
        return is_array($value) ? $value : null;
    }

    /**
     * Get the top island title.
     *
     * @return string The top island title.
     */
    public function getTopIslandTitle(): string
    {
        $value = $this->getConfigValue(ConfigKeys::TOP_ISLAND_TITLE, "Top Island Leaderboard");
        return is_string($value) ? $value : "Top Island Leaderboard";
    }
}
