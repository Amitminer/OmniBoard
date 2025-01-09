<?php

declare(strict_types=1);

namespace Omniboard\Leaderboard;

use pocketmine\world\particle\FloatingTextParticle;
use pocketmine\math\Vector3;
use pocketmine\world\World;

class BaseFloatingText extends FloatingTextParticle {
    protected World $world;
    protected Vector3 $position;

    public function __construct(World $world, Vector3 $position, string $title = "", string $text = "") {
        parent::__construct($title, $text);
        $this->world = $world;
        $this->position = $position;
    }

    public function setTitle(string $title): void {
        $this->title = $title;
        $this->update();
    }

    public function setText(string $text): void {
        $this->text = $text;
        $this->update();
    }

    public function update(): void {
        $this->world->addParticle($this->position, $this);
    }
}
