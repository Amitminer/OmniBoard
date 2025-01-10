<?php

declare(strict_types=1);

namespace Omniboard\commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use Omniboard\Omniboard;
use Omniboard\Utils\ConfigKeys;

/**
 * Class LeaderboardCommand
 *
 * Handles the /leaderboard command to set the top island leaderboard position.
 */
class LeaderboardCommand extends Command
{
    /** @var Omniboard $plugin The main plugin instance */
    private Omniboard $plugin;

    /**
     * LeaderboardCommand constructor.
     *
     * Initializes the command with its name, description, and usage.
     *
     * @param Omniboard $plugin The main plugin instance.
     */
    public function __construct(Omniboard $plugin)
    {
        parent::__construct("leaderboard", "Set leaderboard position", "/leaderboard settopisland", ["lb"]);
        $this->plugin = $plugin;
        $this->setPermission("omniboard.leaderboards");
    }

    /**
     * Executes the command when triggered.
     *
     * @param CommandSender $sender The entity executing the command.
     * @param string $commandLabel The command label.
     * @param array<string> $args The arguments passed to the command.
     * @return bool True if the command executed successfully, false otherwise.
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args): bool
    {
        if (!$sender instanceof Player) {
            $sender->sendMessage("This command can only be used in-game!");
            return false;
        }

        if (count($args) !== 1 || $args[0] !== "settopisland") {
            $sender->sendMessage("Usage: /leaderboard settopisland");
            return false;
        }
        $this->setPositionToConfig($sender);

        $sender->sendMessage("Leaderboard position set!");
        return true;
    }

    /**
     * Sets the top island position to the configuration.
     *
     * @param Player $player The player setting the position.
     */
    private function setPositionToConfig(Player $player): void
    {
        $position = $player->getPosition();
        $this->plugin->getConfigManager()->setNestedConfigValue(ConfigKeys::TOP_ISLAND_POSITION, [round($position->getX(), 1), round($position->getY(), 1), round($position->getZ(), 1)]);
    }
}
