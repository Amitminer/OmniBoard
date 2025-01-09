<?php

declare(strict_types=1);

namespace Omniboard\tasks;

use pocketmine\scheduler\Task;
use pocketmine\world\World;
use pocketmine\math\Vector3;
use Omniboard\Omniboard;
use Omniboard\Leaderboard\BaseFloatingText;

class UpdateTask extends Task {

    private Omniboard $plugin;
    private ?BaseFloatingText $floatingText = null;

    public function __construct(Omniboard $plugin) {
        $this->plugin = $plugin;
    }

    public function onRun(): void {
        $positionData = $this->plugin->getConfig()->getNested("topisland.position");
        $world = $this->plugin->getServer()->getWorldManager()->getDefaultWorld();

        if ($world instanceof World && is_array($positionData)) {
            $position = new Vector3($positionData[0], $positionData[1], $positionData[2]);

            if ($this->floatingText === null) {
                $this->floatingText = new BaseFloatingText($world, $position, "Top Island Leaderboard", "");
                print("Floating text created");
                var_dump($this->floatingText);
            } else {
                print("Floating text already exists");
                var_dump($this->floatingText);
            }

            $this->plugin->getConfigManager()->getTopIslands(function(array $data) {
                $text = "Top Islands:\n";
                foreach ($data as $index => $row) {
                    $text .= ($index + 1) . ". {$row['player']} - {$row['points']} points\n";
                }

                $this->floatingText->setText($text);
                $this->floatingText->update();
            });
        }
    }
}
