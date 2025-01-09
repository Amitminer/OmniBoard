<?php

namespace Omniboard\Manager;

use OmniBoard\LeaderBoard\BaseFloatingText;
use Omniboard\Omniboard;
use pocketmine\entity\Location;
use pocketmine\world\World;
use pocketmine\Server;
use pocketmine\entity\FloatingText;
use pocketmine\math\Vector3;

class FloatingTextManager
{

    private Omniboard $plugin;
    /** @var array $floatingTexts */
    private array $floatingTexts = [];

    public function __construct(Omniboard $plugin)
    {
        $this->plugin = $plugin;
    }

    public function createFloatingText(World $world, Vector3 $position): BaseFloatingText
    {
        $floatingText = new BaseFloatingText($world, $position);
        $this->floatingTexts[] = $floatingText;

        return $floatingText;
    }

    /**
     * Update the text of a specific floating text.
     *
     * @param string $text
     */
    public function updateText(string $text): void
    {
        foreach ($this->floatingTexts as $floatingText) {
            $floatingText->setText($text);
        }
    }

    /**
     * Remove all floating texts.
     */
    public function removeAll(): void
    {
        foreach ($this->floatingTexts as $floatingText) {
            $floatingText->close();
        }

        $this->floatingTexts = [];
    }

    /**
     * Remove a specific floating text.
     *
     * @param FloatingText $floatingText
     */
    public function remove(BaseFloatingText $floatingText): void
    {
        $floatingText->close();
        unset($this->floatingTexts[array_search($floatingText, $this->floatingTexts)]);
    }
}
