<?php

declare(strict_types=1);

namespace Omniboard\Utils;

use pocketmine\item\StringToItemParser;
use Omniboard\Omniboard;

class BlockPoints {

    /** @var array<int, float> $points An associative array mapping block type IDs to their corresponding points */
    private array $points = [];
    private float $defaultPoints;

    public function __construct(Omniboard $plugin)
    {
        $config = $plugin->getConfig()->get("Blocks", []);
        $this->defaultPoints = (float)$plugin->getConfig()->get("DefaultPoints", 1);
        if (!is_array($config)) {
            $plugin->getLogger()->warning("Blocks configuration is not valid.");
            return;
        }

        $parser = StringToItemParser::getInstance();
        foreach ($config as $blockName => $pointValue) {
            if (!is_string($blockName) || !is_numeric($pointValue)) {
                $plugin->getLogger()->warning("Invalid block name or point value in config.");
                continue;
            }

            $item = $parser->parse($blockName);
            if ($item !== null) {
                $this->points[$item->getBlock()->getTypeId()] = (float)$pointValue;
            } else {
                $plugin->getLogger()->warning("Invalid block name in config: {$blockName}");
            }
        }
    }

    /**
     * Gets the points for a specific block type ID.
     *
     * @param int $blockTypeId The block type ID
     * @return float The points associated with the block type ID
     */
    public function getPointsForBlock(int $blockTypeId): float
    {
        return $this->points[$blockTypeId] ?? (float)$this->defaultPoints;
    }
}
