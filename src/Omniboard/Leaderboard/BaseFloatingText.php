<?php

declare(strict_types=1);

namespace Omniboard\Leaderboard;

use pocketmine\world\particle\FloatingTextParticle;
use pocketmine\math\Vector3;
use pocketmine\world\World;

/**
 * Class BaseFloatingText
 *
 * Represents a base implementation of a floating text particle in the game world.
 */
class BaseFloatingText extends FloatingTextParticle {
    /** @var World $world The world in which the floating text exists */
    protected World $world;

    /** @var Vector3 $position The position in the world where the floating text appears */
    protected Vector3 $position;

    /**
     * BaseFloatingText constructor.
     *
     * @param World $world The world instance.
     * @param Vector3 $position The position in the world.
     * @param string $title The title of the floating text.
     * @param string $text The content of the floating text.
     */
    public function __construct(World $world, Vector3 $position, string $title = "", string $text = "") {
        parent::__construct($title, $text);
        $this->world = $world;
        $this->position = $position;
    }

    /**
     * Set the title of the floating text.
     *
     * @param string $title The new title.
     */
    public function setTitle(string $title): void {
        $this->title = $title;
        $this->update();
    }

    /**
     * Set the content (text) of the floating text.
     *
     * @param string $text The new text.
     */
    public function setText(string $text): void {
        $this->text = $text;
        $this->update();
    }

    /**
     * Updates the floating text by adding the particle to the world.
     */
    public function update(): void {
        $this->world->addParticle($this->position, $this);
    }
}
