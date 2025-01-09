<?php

declare(strict_types=1);

namespace Omniboard;

use pocketmine\event\Listener;
use pocketmine\event\block\BlockPlaceEvent;
use Omniboard\Utils\BlockPoints;

class EventListener implements Listener
{

    private Omniboard $plugin;

    public function __construct(Omniboard $plugin)
    {
        $this->plugin = $plugin;
    }

    public function onBlockPlace(BlockPlaceEvent $event): void
    {
        $player = $event->getPlayer();
        foreach ($event->getTransaction()->getBlocks() as [$x, $y, $z, $block]) {
            $points = BlockPoints::getPointsForBlock($block->getTypeId());

            if ($points > 0) {
                $this->plugin->getConfigManager()->addPoints($player->getName(), $points);
            }
        }
    }
}
