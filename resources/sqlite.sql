-- #!sqlite

-- #{ players
-- #{ createTable

CREATE TABLE IF NOT EXISTS players (
    player TEXT PRIMARY KEY,
    points REAL DEFAULT 0
);
-- #}

-- # { updatePoints
-- # :player string
-- # :points float
INSERT INTO players (player, points)
VALUES (:player, :points)
ON CONFLICT(player) DO UPDATE SET points = points + excluded.points;
-- # }

-- # { getTopPlayers
SELECT player, points FROM players
ORDER BY points DESC LIMIT 10;
-- # }

-- #}