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

        $this->database->executeGeneric("players.createTable");
    }

    public function addPoints(string $playerName, float $points): void {
        $this->database->executeInsert("players.updatePoints", ["player" => $playerName, "points" => $points]);
    }

    public function getTopIslands(callable $callback): void {
        $this->database->executeSelect("players.getTopPlayers", [], $callback);
    }
}
