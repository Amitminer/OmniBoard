<?php

declare(strict_types=1);

namespace Omniboard\tasks;

use pocketmine\scheduler\Task;
use pocketmine\math\Vector3;
use Omniboard\Omniboard;
use Omniboard\Leaderboard\BaseFloatingText;
use Omniboard\Utils\ConfigKeys;
use SOFe\AwaitGenerator\Await;
use Generator;

/**
 * Class UpdateTask
 *
 * This class handles the periodic update of the floating text leaderboard.
 */
class UpdateTask extends Task
{

    /** @var Omniboard $plugin The main Omniboard plugin instance */
    private Omniboard $plugin;

    /** @var BaseFloatingText|null $floatingText The floating text instance to be updated */
    private ?BaseFloatingText $floatingText = null;

    /**
     * UpdateTask constructor.
     *
     * @param Omniboard $plugin The main plugin instance
     */
    public function __construct(Omniboard $plugin)
    {
        $this->plugin = $plugin;
    }

    /**
     * Executes the task every scheduled interval.
     */
    public function onRun(): void
    {
        $world = $this->plugin->getServer()->getWorldManager()->getDefaultWorld();
        if ($world === null) {
            $this->plugin->getLogger()->error("Default world is not loaded.");
            return;
        }
        $position = $this->plugin->getConfig()->getNested(ConfigKeys::TOP_ISLAND_POSITION);

        if (is_array($position) && isset($position[0], $position[1], $position[2])) {
            /** @var int[] $position */
            $positionVec = new Vector3((int)$position[0], (int)$position[1], (int)$position[2]);

            if ($this->floatingText === null) {
                $title = $this->plugin->getConfigManager()->getTopIslandTitle();
                $this->floatingText = new BaseFloatingText($world, $positionVec, $title, "");
            }

            $this->updateFloatingText();
        } else {
            $this->plugin->getLogger()->error("Invalid position data in configuration.");
        }
    }

    /**
     * Updates the floating text with the latest top islands data.
     * Uses Await::f2c to asynchronously update the leaderboard.
     */
    private function updateFloatingText(): void
    {
        // @phpstan-ignore-next-line
        Await::f2c(function (): Generator {
            try {
                /** @var iterable<int, array<string>> $data */
                $data = yield from $this->plugin->getDatabaseManager()->getTopIslands();

                if (is_iterable($data)) {
                    $text = $this->generateLeaderboardText($data);
                    // $this->plugin->getLogger()->info("Leaderboard updated: " . $text);

                    if ($this->floatingText !== null) {
                        $this->floatingText->setText($text);
                        $this->floatingText->update();
                    }
                } else {
                    $this->plugin->getLogger()->error("Invalid data received for leaderboard.");
                }
            } catch (\Throwable $e) {
                $this->plugin->getLogger()->error("Failed to update floating text: " . $e->getMessage());
            }
        });
    }

    /**
     * Generates the leaderboard text from data.
     *
     * @param iterable<int, array<string>> $data
     * @return string
     */
    private function generateLeaderboardText(iterable $data): string
    {
        $text = "§d§l§oLeaderboard\n"; // Styled header
        $i = 1;

        foreach ($data as $row) {
            $playerRow = $row['player'] ?? null;
            $pointsRow = $row['points'] ?? null;

            if ($playerRow !== null && $pointsRow !== null) {
                $text .= "\n " . $i . ". §7" . (string)$playerRow . "  §aTotalPoints  §f" . (string)$pointsRow . " §aPoints";
                ++$i;
                if ($i >= 11) { // Limit to 10 items (can be customized)
                    break;
                }
            }
        }
        return $text;
    }
}
