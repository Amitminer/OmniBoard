<?php

declare(strict_types=1);

namespace Omniboard;

use Omniboard\commands\LeaderboardCommand;
use pocketmine\plugin\PluginBase;
use Omniboard\Manager\ConfigManager;
use Omniboard\Manager\DatabaseManager;
use Omniboard\tasks\UpdateTask;
use Omniboard\Utils\BlockPoints;

/**
 * The main class for the Omniboard plugin.
 */
class Omniboard extends PluginBase
{

    private ConfigManager $configManager;
    private BlockPoints $blockPoints;
    private DatabaseManager $databaseManager;

    /**
     * Initializes the plugin, sets up configuration, and initializes managers and tasks.
     */
    protected function onEnable(): void
    {
        $this->saveDefaultConfig();
        $this->initializeManagers();
        $this->databaseManager->loadDatabase();

        // Register event listener
        $this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);

        // Register command
        $this->getServer()->getCommandMap()->register("omniboard", new LeaderboardCommand($this));

        /** @var int $updateInterval */
        $updateInterval = $this->getConfig()->get("update-interval", 60); // Default to 60 seconds
        $this->getScheduler()->scheduleRepeatingTask(new UpdateTask($this), 20 * (int) $updateInterval);
    }

    /**
     * Closes the database connection.
     */
    public function onDisable(): void
    {
        $this->databaseManager->getDatabase(false);
    }

    /**
     * Initializes all managers used by the plugin.
     */
    private function initializeManagers(): void
    {
        $this->configManager = new ConfigManager($this);
        $this->blockPoints = new BlockPoints($this);
        $this->databaseManager = new DatabaseManager($this);
    }

    /**
     * Get the DatabaseManager instance.
     * 
     * @return DatabaseManager The database manager instance.
     */
    public function getDatabaseManager(): DatabaseManager
    {
        return $this->databaseManager;
    }

    /**
     * Get the BlockPoints instance.
     * 
     * @return BlockPoints The block points utility instance.
     */
    public function getBlockPoints(): BlockPoints
    {
        return $this->blockPoints;
    }

    /**
     * Get the ConfigManager instance.
     * 
     * @return ConfigManager The config manager instance.
     */
    public function getConfigManager(): ConfigManager
    {
        return $this->configManager;
    }
}
