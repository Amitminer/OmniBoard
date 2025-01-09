<?php

declare(strict_types=1);

namespace Omniboard\commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use Omniboard\Omniboard;

class LeaderboardCommand extends Command {

    private Omniboard $plugin;

    public function __construct(Omniboard $plugin) {
        parent::__construct("leaderboard", "Set leaderboard position", "/leaderboard settopisland", ["lb"]);
        $this->plugin = $plugin;
        $this->setPermission("omniboard.leaderboards");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): bool {
        if (!$sender instanceof Player) {
            $sender->sendMessage("This command can only be used in-game!");
            return false;
        }

        if (count($args) !== 1 || $args[0] !== "settopisland") {
            $sender->sendMessage("Usage: /leaderboard settopisland");
            return false;
        }

        $position = $sender->getPosition();
        $this->plugin->getConfig()->setNested("topisland.position", [$position->getX(), $position->getY(), $position->getZ()]);
        $this->plugin->saveConfig();

        $sender->sendMessage("Leaderboard position set! at" . $position->__toString());
        return true;
    }
}
