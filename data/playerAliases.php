<?php

// Data-fetching helpers for the admin "Player Aliases" tab (playerAliases.php).
// Relies on query()/fetch_array()/$conn from functions.php/connections.php.

function getAllPlayerNames()
{
    global $conn;

    $names = [];
    $result = $conn->query("
        SELECT player AS name FROM rosters
        UNION
        SELECT player AS name FROM draft
        UNION
        SELECT player AS name FROM player_aliases
        UNION
        SELECT alias_1 AS name FROM player_aliases WHERE alias_1 IS NOT NULL
        UNION
        SELECT alias_2 AS name FROM player_aliases WHERE alias_2 IS NOT NULL
        UNION
        SELECT alias_3 AS name FROM player_aliases WHERE alias_3 IS NOT NULL
        ORDER BY name ASC
    ");
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        if (!empty($row['name'])) {
            $names[] = $row['name'];
        }
    }

    return $names;
}

// Finds the player_aliases row for a given name, matching on the canonical
// player or any of its aliases (so searching by an alias still finds it).
function getPlayerAliasRecord($playerName)
{
    global $conn;

    $escaped = SQLite3::escapeString($playerName);
    $row = $conn->querySingle("
        SELECT * FROM player_aliases
        WHERE player = '$escaped'
           OR alias_1 = '$escaped'
           OR alias_2 = '$escaped'
           OR alias_3 = '$escaped'
        LIMIT 1
    ", true);

    return $row ?: null;
}

// Upserts by the row's canonical `player` name. Blank alias fields are saved as NULL.
function savePlayerAliases($player, $alias1, $alias2, $alias3)
{
    global $conn;

    $player = SQLite3::escapeString(trim($player));
    $alias1 = trim($alias1) === '' ? 'NULL' : "'" . SQLite3::escapeString(trim($alias1)) . "'";
    $alias2 = trim($alias2) === '' ? 'NULL' : "'" . SQLite3::escapeString(trim($alias2)) . "'";
    $alias3 = trim($alias3) === '' ? 'NULL' : "'" . SQLite3::escapeString(trim($alias3)) . "'";

    $existingId = $conn->querySingle("SELECT id FROM player_aliases WHERE player = '$player'");

    if ($existingId) {
        $conn->exec("
            UPDATE player_aliases
            SET alias_1 = $alias1, alias_2 = $alias2, alias_3 = $alias3
            WHERE id = $existingId
        ");
    } else {
        $conn->exec("
            INSERT INTO player_aliases (player, alias_1, alias_2, alias_3)
            VALUES ('$player', $alias1, $alias2, $alias3)
        ");
    }
}
