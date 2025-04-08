<?php

declare(strict_types=1);

namespace Omniboard\tasks;

use cooldogedev\BedrockEconomy\BedrockEconomy;
use pocketmine\scheduler\Task;
use pocketmine\entity\Location;
use Omniboard\Omniboard;
use Omniboard\Utils\ConfigKeys;
use Omniboard\Leaderboard\FloatingTextEntity;
use SOFe\AwaitGenerator\Await;
use Generator;
use cooldogedev\BedrockEconomy\database\cache\GlobalCache;

/**
 * Task to update the floating text leaderboards for top island and top money.
 */
class UpdateTask extends Task
{
    /**
     * @var Omniboard The main plugin instance.
     */
    private Omniboard $plugin;

    /**
     * @var ?FloatingTextEntity The floating text entity for the top island leaderboard.
     */
    private ?FloatingTextEntity $floatingTextIsland = null;

    /**
     * @var ?FloatingTextEntity The floating text entity for the top money leaderboard.
     */
    private ?FloatingTextEntity $floatingTextMoney = null;

    /**
     * UpdateTask constructor.
     *
     * @param Omniboard $plugin The main plugin instance.
     */
    public function __construct(Omniboard $plugin)
    {
        $this->plugin = $plugin;
    }

    /**
     * Called when the task is run. Updates both the top island and top money leaderboards.
     */
    public function onRun(): void
    {
        $this->updateTopIslandLeaderboard();
        $this->updateTopMoneyLeaderboard();
    }

    /**
     * Updates the floating text entity for the top island leaderboard.
     */
    private function updateTopIslandLeaderboard(): void
    {
        // Top Island
        $positionIsland = $this->plugin->getConfig()->getNested(ConfigKeys::TOP_ISLAND_POSITION);
        $worldNameIsland = $this->plugin->getConfig()->getNested(ConfigKeys::TOP_ISLAND_WORLD, "world");

        if (is_array($positionIsland)) {
            $world = $this->plugin->getServer()->getWorldManager()->getWorldByName($worldNameIsland);
            if ($world === null) {
                $this->plugin->getLogger()->error("World '$worldNameIsland' for Island leaderboard is not loaded.");
                return;
            }

            $location = new Location($positionIsland[0], $positionIsland[1], $positionIsland[2], $world, 0.0, 0.0);

            // Only create the entity if it doesn't exist
            if ($this->floatingTextIsland === null || $this->floatingTextIsland->isClosed()) {
                $title = $this->plugin->getConfigManager()->getTopIslandTitle();
                $this->floatingTextIsland = FloatingTextEntity::create($location, $title);
                $this->floatingTextIsland->spawnToAll();
            }

            $this->updateFloatingTextIslandData();
        } else {
            $this->plugin->getLogger()->warning("Top Island position is not properly configured.");
        }
    }

    /**
     * Updates the floating text entity for the top money leaderboard.
     */
    private function updateTopMoneyLeaderboard(): void
    {
        // Top Money
        $positionMoney = $this->plugin->getConfig()->getNested(ConfigKeys::TOP_MONEY_POSITION);
        $worldNameMoney = $this->plugin->getConfig()->getNested(ConfigKeys::TOP_MONEY_WORLD, "world");

        if (is_array($positionMoney)) {
            $world = $this->plugin->getServer()->getWorldManager()->getWorldByName($worldNameMoney);
            if ($world === null) {
                $this->plugin->getLogger()->error("World '$worldNameMoney' for Money leaderboard is not loaded.");
                return;
            }

            $location = new Location($positionMoney[0], $positionMoney[1], $positionMoney[2], $world, 0.0, 0.0);

            // Only create the entity if it doesn't exist
            if ($this->floatingTextMoney === null || $this->floatingTextMoney->isClosed()) {
                $title = $this->plugin->getConfigManager()->getTopMoneyTitle();
                $this->floatingTextMoney = FloatingTextEntity::create($location, $title);
                $this->floatingTextMoney->spawnToAll();
            }

            $this->updateFloatingTextMoneyData();
        } else {
            $this->plugin->getLogger()->warning("Top Money position is not properly configured.");
        }
    }

    /**
     * Asynchronously fetches and updates the text of the top island floating text entity.
     */
    private function updateFloatingTextIslandData(): void
    {
        Await::f2c(function (): Generator {
            try {
                $data = yield from $this->plugin->getDatabaseManager()->getTopIslands();

                if (empty($data)) {
                    $text = "§6§l★ §eTop Island Leaderboard §6§l★\n§7No data available yet";
                } else {
                    $text = $this->generateIslandLeaderboardText($data);
                }

                if ($this->floatingTextIsland !== null && !$this->floatingTextIsland->isClosed()) {
                    $this->floatingTextIsland->setNameTag($text);
                }
            } catch (\Throwable $e) {
                $this->plugin->getLogger()->error("Failed to update Top Island floating text: " . $e->getMessage());
            }
        });
    }

    /**
     * Updates the text of the top money floating text entity.
     */
    private function updateFloatingTextMoneyData(): void
    {
        try {
            if (!$this->plugin->getServer()->getPluginManager()->getPlugin("BedrockEconomy")) {
                $text = "§6§l★ §eTop Money Leaderboard §6§l★\n§cBedrockEconomy not found!";
                if ($this->floatingTextMoney !== null && !$this->floatingTextMoney->isClosed()) {
                    $this->floatingTextMoney->setNameTag($text);
                }
                return;
            }

            $cache = GlobalCache::TOP()->getAll();

            if (!is_array($cache) || empty($cache)) {
                $text = "§6§l★ §eTop Money Leaderboard §6§l★\n§7No data available yet";
                if ($this->floatingTextMoney !== null && !$this->floatingTextMoney->isClosed()) {
                    $this->floatingTextMoney->setNameTag($text);
                }
                return;
            }

            $data = [];
            foreach ($cache as $player => $entry) {
                if ($entry instanceof \cooldogedev\BedrockEconomy\database\cache\CacheEntry) {
                    $data[] = [
                        'player' => $player,
                        'amount' => $entry->amount,
                        'decimals' => $entry->decimals
                    ];
                }
            }

            if (empty($data)) {
                $text = "§6§l★ §eTop Money Leaderboard §6§l★\n§7No valid data available";
            } else {
                usort($data, fn($a, $b) => $b['amount'] <=> $a['amount']);

                foreach ($data as &$entry) {
                    $entry['amount'] = BedrockEconomy::getInstance()->getCurrency()->formatter->format($entry['amount'], $entry['decimals']);
                }

                $text = $this->generateMoneyLeaderboardText($data);
            }

            if ($this->floatingTextMoney !== null && !$this->floatingTextMoney->isClosed()) {
                $this->floatingTextMoney->setNameTag($text);
            }
        } catch (\Throwable $e) {
            $this->plugin->getLogger()->error("Failed to update Top Money floating text: " . $e->getMessage());
        }
    }

    /**
     * Generates the formatted text for the top money leaderboard.
     *
     * @param iterable $data An array of player data with 'player' and 'amount'.
     * @return string The formatted leaderboard text.
     */
    private function generateMoneyLeaderboardText(iterable $data): string
    {
        $text = "§6§l★ §eTop Money Leaderboard §6§l★\n";
        $i = 1;

        foreach ($data as $row) {
            $player = $row['player'] ?? null;
            $amount = $row['amount'] ?? null;

            if ($player !== null && $amount !== null) {
                $rankColor = match ($i) {
                    1 => "§6",
                    2 => "§7",
                    3 => "§c",
                    default => "§b",
                };
                $text .= "\n{$rankColor}[{$i}] §r§a{$player} §r- §e" . (string)$amount;
                ++$i;
                if ($i >= 11) {
                    break;
                }
            }
        }
        return $text;
    }

    /**
     * Generates the formatted text for the top island leaderboard.
     *
     * @param iterable $data An array of island data with 'player' and 'points'.
     * @return string The formatted leaderboard text.
     */
    private function generateIslandLeaderboardText(iterable $data): string
    {
        $text = "§6§l★ §eTop Island Leaderboard §6§l★\n";
        $i = 1;

        foreach ($data as $row) {
            $player = $row['player'] ?? "Unknown";
            $points = $row['points'] ?? 0;

            if ($player !== null && $points !== null) {
                $rankColor = match ($i) {
                    1 => "§6",
                    2 => "§7",
                    3 => "§c",
                    default => "§b",
                };
                $formattedPoints = number_format((float)$points, 2);
                $text .= "\n{$rankColor}[{$i}] §r§a{$player} §r- §e{$formattedPoints} Points";
                ++$i;
                if ($i >= 11) {
                    break;
                }
            }
        }
        return $text;
    }
}