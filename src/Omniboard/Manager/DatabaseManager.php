<?php

declare(strict_types=1);

namespace Omniboard\Manager;

use Omniboard\Omniboard;
use Omniboard\Utils\SqlQueries;
use poggit\libasynql\DataConnector;
use poggit\libasynql\libasynql;
use Generator;
use Throwable;

class DatabaseManager
{
    private Omniboard $plugin;
    private DataConnector $database;

    /**
     * DatabaseManager constructor.
     * 
     * Initializes the DatabaseManager with the provided plugin and database configuration.
     * 
     * @param Omniboard $plugin The main plugin instance.
     */
    public function __construct(Omniboard $plugin)
    {
        $this->plugin = $plugin;
        $this->database = libasynql::create($plugin, $plugin->getConfig()->get("database"), [
            "sqlite" => "sqlite.sql"
        ]);
    }

    /**
     * Manage the database connection.
     *
     * @param bool $load If true, loads the database; otherwise, closes it.
     */
    public function getDatabase(bool $load = true): void
    {
        if ($load) {
            $this->loadDatabase();
        } else {
            $this->closeDatabase();
        }
    }

    /**
     * Load the database and initialize necessary tables.
     *
     * Executes the SQL query to create tables asynchronously.
     */
    public function loadDatabase(): void
    {
        $this->database->executeGeneric(SqlQueries::CREATE_ISLAND_TABLE, [], function () {
            // $this->plugin->getLogger()->info("Database table initialized successfully.");
        }, function (\Throwable $error) {
            $this->plugin->getLogger()->critical("Error initializing database table: " . $error->getMessage());
        });
    }

    /**
     * Close the database connection.
     */
    private function closeDatabase(): void
    {
        if (isset($this->database)) {
            $this->database->close();
        }
    }

    /**
     * Add points to a player's record in the database.
     *
     * @param string $playerName The player's name.
     * @param float $points The points to add.
     * @return Generator The asynchronous operation result.
     */
    public function addPoints(string $playerName, float $points): Generator
    {
        try {
            [$insertId, $affectedRows] = yield from $this->database->asyncInsert(SqlQueries::ISLAND_UPDATE_POINTS, [
                "player" => $playerName,
                "points" => $points
            ]);

            //$this->plugin->getLogger()->debug("Points added: Player={$playerName}, Points={$points}, AffectedRows={$affectedRows}");
        } catch (Throwable $e) {
            $this->plugin->getLogger()->error("Failed to add points for Player={$playerName}: {$e->getMessage()}");
        }
    }

    /**
     * Retrieve the top islands from the database.
     *
     * @return Generator The asynchronous operation result containing top islands data.
     */
    public function getTopIslands(): Generator
    {
        $result = yield from $this->database->asyncSelect(SqlQueries::ISLAND_GET_TOP, []);
        return $result;
    }

    /**
     * Reloads the Top Islands data by forcing a fresh query to the database.
     */
    public function reloadTopIslands(): Generator
    {
        try {
            $result = yield from $this->database->asyncSelect(SqlQueries::ISLAND_GET_TOP, []);

            if (empty($result)) {
                // $this->plugin->getLogger()->warning("⚠️ No data found for Top Islands leaderboard.");
            } else {
                // $this->plugin->getLogger()->info("✅ Reloaded Top Islands leaderboard: " . json_encode($result));
            }

            return $result; // Return the data for use
        } catch (Throwable $error) {
            // $this->plugin->getLogger()->error("❌ Failed to reload Top Islands leaderboard: " . $error->getMessage());
            return [];
        }
    }
}
