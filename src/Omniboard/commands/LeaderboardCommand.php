<?php

declare(strict_types=1);

namespace Omniboard\commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use Omniboard\Omniboard;
use Omniboard\Utils\ConfigKeys;
use Omniboard\Leaderboard\FloatingTextEntity;

/**
 * Class LeaderboardCommand
 *
 * Handles the /leaderboard command for managing floating text leaderboards in the game.
 * This command allows players to create, remove, reload, and test leaderboards.
 *
 * @package Omniboard\commands
 */
class LeaderboardCommand extends Command
{
    /** @var Omniboard $plugin The main plugin instance */
    private Omniboard $plugin;

    /**
     * LeaderboardCommand constructor.
     *
     * Initializes the command with its name, description, usage, and aliases.
     *
     * @param Omniboard $plugin The main plugin instance.
     */
    public function __construct(Omniboard $plugin)
    {
        parent::__construct(
            "leaderboard", 
            "Create and manage floating text leaderboards", 
            "/leaderboard <create|remove|reload|test> [island|money|all]", 
            ["lb"]
        );
        $this->plugin = $plugin;
        $this->setPermission("omniboard.leaderboards");
    }

    /**
     * Executes the command when triggered by a player or console.
     *
     * @param CommandSender $sender The entity executing the command.
     * @param string $commandLabel The command label used.
     * @param array<string> $args The arguments passed to the command.
     * @return bool True if the command executed successfully, false otherwise.
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args): bool
    {
        if (!$sender instanceof Player) {
            $sender->sendMessage("§cThis command can only be used by players.");
            return true;
        }

        if (!$this->testPermission($sender)) {
            return true;
        }

        if (count($args) < 1) {
            $this->sendUsage($sender);
            return true;
        }

        $subCommand = strtolower($args[0]);

        switch ($subCommand) {
            case "create":
                $this->createLeaderboard($sender, $args);
                break;
            case "remove":
                $this->removeLeaderboard($sender, $args);
                break;
            case "reload":
                $this->reloadLeaderboard($sender, $args);
                break;
            case "test":
                $this->testLeaderboard($sender);
                break;
            default:
                $this->sendUsage($sender);
                break;
        }

        return true;
    }

    /**
     * Sends the usage information to the sender.
     *
     * @param CommandSender $sender The command sender.
     */
    private function sendUsage(CommandSender $sender): void
    {
        $sender->sendMessage("§e==== Leaderboard Command Help ====");
        $sender->sendMessage("§f/leaderboard create <island|money> §7- Create a leaderboard at your position");
        $sender->sendMessage("§f/leaderboard remove <island|money> §7- Remove a leaderboard");
        $sender->sendMessage("§f/leaderboard reload <island|money|all> §7- Reload leaderboard data");
        $sender->sendMessage("§f/leaderboard test §7- Create a test floating text");
    }

    /**
     * Creates a leaderboard at the player's current position.
     *
     * @param Player $player The player executing the command.
     * @param array<string> $args The command arguments.
     */
    private function createLeaderboard(Player $player, array $args): void
    {
        if (count($args) < 2) {
            $player->sendMessage("§cUsage: /leaderboard create <island|money>");
            return;
        }

        $type = strtolower($args[1]);
        if (!in_array($type, ["island", "money"])) {
            $player->sendMessage("§cInvalid type. Use 'island' or 'money'");
            return;
        }

        $location = $player->getLocation();
        $world = $player->getWorld()->getFolderName();
        
        // Set the appropriate configuration values based on the leaderboard type
        if ($type === "island") {
            $this->plugin->getConfigManager()->setNestedConfigValue(
                ConfigKeys::TOP_ISLAND_POSITION,
                [$location->getX(), $location->getY(), $location->getZ()]
            );
            $this->plugin->getConfigManager()->setNestedConfigValue(
                ConfigKeys::TOP_ISLAND_WORLD,
                $world
            );
            $player->sendMessage("§a✓ Island leaderboard position set to your current location!");
        } else {
            $this->plugin->getConfigManager()->setNestedConfigValue(
                ConfigKeys::TOP_MONEY_POSITION,
                [$location->getX(), $location->getY(), $location->getZ()]
            );
            $this->plugin->getConfigManager()->setNestedConfigValue(
                ConfigKeys::TOP_MONEY_WORLD,
                $world
            );
            $player->sendMessage("§a✓ Money leaderboard position set to your current location!");
        }
        
        // Save the configuration and notify the player
        $this->plugin->getConfig()->save();
        $player->sendMessage("§a✓ Configuration saved. The leaderboard will update on the next refresh.");
    }

    /**
     * Removes a leaderboard from the configuration.
     *
     * @param Player $player The player executing the command.
     * @param array<string> $args The command arguments.
     */
    private function removeLeaderboard(Player $player, array $args): void
    {
        if (count($args) < 2) {
            $player->sendMessage("§cUsage: /leaderboard remove <island|money>");
            return;
        }

        $type = strtolower($args[1]);
        if (!in_array($type, ["island", "money"])) {
            $player->sendMessage("§cInvalid type. Use 'island' or 'money'");
            return;
        }

        // Remove the position data from configuration based on leaderboard type
        if ($type === "island") {
            $this->plugin->getConfigManager()->setNestedConfigValue(
                ConfigKeys::TOP_ISLAND_POSITION,
                []
            );
            $player->sendMessage("§a✓ Island leaderboard position removed!");
        } else {
            $this->plugin->getConfigManager()->setNestedConfigValue(
                ConfigKeys::TOP_MONEY_POSITION,
                []
            );
            $player->sendMessage("§a✓ Money leaderboard position removed!");
        }
        
        // Save the configuration and notify the player
        $this->plugin->getConfig()->save();
        $player->sendMessage("§a✓ Configuration saved. The leaderboard will be removed on the next refresh.");
    }

    /**
     * Reloads the leaderboard data from the database.
     *
     * @param Player $player The player executing the command.
     * @param array<string> $args The command arguments.
     */
    private function reloadLeaderboard(Player $player, array $args): void
    {
        if (count($args) < 2) {
            $player->sendMessage("§cUsage: /leaderboard reload <island|money|all>");
            return;
        }

        $type = strtolower($args[1]);
        if (!in_array($type, ["island", "money", "all"])) {
            $player->sendMessage("§cInvalid type. Use 'island', 'money', or 'all'");
            return;
        }

        // Reload the appropriate leaderboard data based on type
        if ($type === "island" || $type === "all") {
            $this->plugin->getDatabaseManager()->reloadTopIslands();
            $player->sendMessage("§a✓ Island leaderboard data reloaded!");
        }
        
        if ($type === "money" || $type === "all") {
            $player->sendMessage("§a✓ Money leaderboard data will be updated on the next refresh!");
        }
    }

    /**
     * Creates a test floating text entity at the player's location.
     * This helps debugging the leaderboard display functionality.
     *
     * @param Player $player The player executing the command.
     */
    private function testLeaderboard(Player $player): void
    {
        $location = $player->getLocation();
        $world = $player->getWorld();
        
        // Format coordinates for display
        $x = round($location->getX(), 1);
        $y = round($location->getY(), 1);
        $z = round($location->getZ(), 1);
        
        // Create a test floating text entity with multiple lines
        $text = "§6§l★ §eTest Floating Text §6§l★\n";
        $text .= "§aThis is a test floating text entity.\n";
        $text .= "§bPosition: {$x}, {$y}, {$z}\n";
        $text .= "§cIf you can see this text, the floating text is working!";
        
        // Create and spawn the entity
        $entity = FloatingTextEntity::create($location, $text);
        $entity->spawnToAll();
        
        // Notify the player
        $player->sendMessage("§a✓ Test floating text entity created with ID: " . $entity->getId());
        $player->sendMessage("§a✓ Position: {$x}, {$y}, {$z}");
        $player->sendMessage("§a✓ If you don't see the floating text, try looking around or moving closer.");
    }
}