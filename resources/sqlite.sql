-- #!sqlite

-- #{ topislanddata
-- #{ createTable

CREATE TABLE IF NOT EXISTS topislanddata (
    player TEXT PRIMARY KEY,
    points REAL DEFAULT 0
);
-- #}

-- # { updatePoints
-- # :player string
-- # :points float
INSERT INTO topislanddata (player, points)
VALUES (:player, :points)
ON CONFLICT(player) DO UPDATE SET points = points + excluded.points;
-- # }

-- # { getTopPlayers
SELECT player, points FROM topislanddata
ORDER BY points DESC LIMIT 10;
-- # }

-- #}