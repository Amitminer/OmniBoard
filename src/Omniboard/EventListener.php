<?php
declare(strict_types=1);

namespace Omniboard;

use Generator;
use pocketmine\event\Listener;
use pocketmine\event\block\BlockPlaceEvent;
use SOFe\AwaitGenerator\Await;

/**
 * Class EventListener
 *
 * Handles various events related to the Omniboard plugin, such as block placement.
 */
class EventListener implements Listener
{
    /** @var Omniboard $plugin The main Omniboard plugin instance */
    private Omniboard $plugin;

    /**
     * EventListener constructor.
     *
     * @param Omniboard $plugin The main plugin instance.
     */
    public function __construct(Omniboard $plugin)
    {
        $this->plugin = $plugin;
    }

    /**
     * Handles the BlockPlace event.
     *
     * @param BlockPlaceEvent $event The block place event.
     */
    public function onBlockPlace(BlockPlaceEvent $event): void
    {
        $player = $event->getPlayer();
        $blocks = $event->getTransaction()->getBlocks();

        foreach ($blocks as [$x, $y, $z, $block]) {
            $points = $this->plugin->getBlockPoints()->getPointsForBlock($block->getTypeId());
            // print("Points: {$points} \n");
            // var_dump($points);

            if ($points > 0) {
                // @phpstan-ignore-next-line
                Await::f2c(function () use ($player, $points): Generator {
                    yield from $this->plugin->getDatabaseManager()->addPoints($player->getName(), $points);
                    return null;
                });
            }
        }
    }
}