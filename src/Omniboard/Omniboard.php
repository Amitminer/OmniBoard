<?php

declare(strict_types=1);

namespace Omniboard;

use Omniboard\commands\LeaderboardCommand;
use Omniboard\Leaderboard\FloatingTextEntity;
use pocketmine\plugin\PluginBase;
use Omniboard\Manager\ConfigManager;
use Omniboard\Manager\DatabaseManager;
use Omniboard\tasks\UpdateTask;
use Omniboard\Utils\BlockPoints;
use pocketmine\entity\Entity;
use pocketmine\entity\EntityDataHelper;
use pocketmine\entity\EntityFactory;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\world\World;

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

        // Register custom entity
        $this->registerLeaderboardEntity();
        
        // Register command
        $this->getServer()->getCommandMap()->register("omniboard", new LeaderboardCommand($this));

        /** @var int $updateInterval */
        $updateInterval = $this->getConfig()->get("update-interval", 300); // Default to 60 seconds
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
     * Registers the FloatingTextEntity with the EntityFactory.
     * This allows the creation of floating text entities in the game world.
     * 
     * The registration includes:
     * - The entity class (FloatingTextEntity)
     * - A factory function that creates new instances
     * - The identifier for the entity type
     */
    public function registerLeaderboardEntity(): void
    {
        EntityFactory::getInstance()->register(
            FloatingTextEntity::class,
            static function (World $world, CompoundTag $nbt): Entity {
                $loc = EntityDataHelper::parseLocation($nbt, $world);
                return new FloatingTextEntity($loc, "");
            },
            [FloatingTextEntity::class]
        );
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
