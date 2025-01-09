<?php

declare(strict_types=1);

namespace Omniboard;

use Omniboard\commands\LeaderboardCommand;
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\Server;
use Omniboard\Manager\ConfigManager;
use Omniboard\tasks\UpdateTask;

class Omniboard extends PluginBase {

    private ConfigManager $configManager;

    protected function onEnable(): void {
        $this->saveDefaultConfig();
        $this->configManager = new ConfigManager($this);

        $this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);
        $this->getServer()->getCommandMap()->register("omniboard",new LeaderboardCommand($this));
        $this->getScheduler()->scheduleRepeatingTask(new UpdateTask($this), 20 * 60); // Update every minute
    }

    public function getConfigManager(): ConfigManager {
        return $this->configManager;
    }
}
