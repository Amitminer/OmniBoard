<?php

declare(strict_types=1);

namespace Omniboard\Manager;

use Omniboard\Omniboard;
use poggit\libasynql\DataConnector;
use poggit\libasynql\libasynql;

class ConfigManager {

    private Omniboard $plugin;
    private DataConnector $database;

    public function __construct(Omniboard $plugin) {
        $this->plugin = $plugin;
        $this->database = libasynql::create($plugin, $plugin->getConfig()->get("database"), [
            "sqlite" => "sqlite.sql"]);

        $this->database->asyncGeneric("players.createTable");
    }

    public function addPoints(string $playerName, float $points): void {
        $this->database->asyncInsert("players.updatePoints", ["player" => $playerName, "points" => $points]);
    }

    public function getTopIslands(callable $callback): void {
        $this->database->asyncSelect("players.getTopPlayers", [], $callback);
    }
}
