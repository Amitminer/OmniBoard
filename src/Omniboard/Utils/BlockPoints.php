<?php

declare(strict_types=1);

namespace Omniboard\Utils;

use pocketmine\item\StringToItemParser;
use Omniboard\Omniboard;

class BlockPoints {

    private array $points = [];

    public function __construct(Omniboard $plugin)
    {
        $config = $plugin->getConfig()->get("Blocks", []);
        $parser = StringToItemParser::getInstance();

        foreach ($config as $blockName => $pointValue) {
            $item = $parser->parse($blockName);

            if ($item !== null) {
                $this->points[$item->getBlock()->getTypeId()] = (float) $pointValue;
            } else {
                $plugin->getLogger()->warning("Invalid block name in config: {$blockName}");
            }
        }
    }

    public function getPointsForBlock(int $blockTypeId): float
    {
        return $this->points[$blockTypeId] ?? 0.0;
    }

    public function getCachedPoints(): array
    {
        return $this->points;
    }
}
