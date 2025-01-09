<?php

declare(strict_types=1);

namespace Omniboard\Utils;

use pocketmine\block\BlockTypeIds;

class BlockPoints {

    public const POINTS = [
        BlockTypeIds::DIAMOND => 0.7, // Diamond Block
        BlockTypeIds::EMERALD => 1.0, // Emerald Block
        // Add other blocks here
    ];

    public static function getPointsForBlock(int $blockTypeId): float {
        return self::POINTS[$blockTypeId] ?? 0;
    }
}
