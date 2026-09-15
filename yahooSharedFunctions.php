<?php

include 'connections.php';


// Make curl call
function make_curl_request(string $method, string $final_url, string $params = '', $bearer = '')
{
    $ch = curl_init();

    if ($method == 'POST') {
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
    }
    
    if ($bearer != '') {
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer '.$bearer]);
    }
    curl_setopt($ch, CURLOPT_URL, $final_url);
    curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);

    $timeout = 60; // seconds
    curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);

    $contents = curl_exec($ch);
    $ret_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $errno = curl_errno($ch);
    $error_str = curl_error($ch);

    if( $errno || $error_str ) {
    //print "Error: ${error_str} (${errno})\n";
    }

    curl_close($ch);

    $data = [
        'return_code' => $ret_code,
        'contents'    => $contents,
        'error_str'   => $error_str,
        'errno'       => $errno
    ];

    return $data;
}

///////////////////////////////////////////////////////////////////////////////
//  FUNCTION oauth_response_to_array
/// @brief Break up the oauth response data into an associate array
///////////////////////////////////////////////////////////////////////////////
function oauth_response_to_array(string $response) {
    $data = [];
    foreach (explode('&', $response) as $param) {
        $parts = explode( '=', $param );
        if (count( $parts ) == 2) {
            $data[urldecode($parts[0])] = urldecode($parts[1]);
        }
    }

    return $data;
}

/**
 * Better GI than print_r or var_dump -- but, unlike var_dump, you can only dump one variable.  
 * Added htmlentities on the var content before echo, so you see what is really there, and not the mark-up.
 * 
 * Also, now the output is encased within a div block that sets the background color, font style, and left-justifies it
 * so it is not at the mercy of ambient styles.
 *
 * Inspired from:     PHP.net Contributions
 * Stolen from:       [highstrike at gmail dot com]
 * Modified by:       stlawson *AT* JoyfulEarthTech *DOT* com 
 *
 * @param mixed $var  -- variable to dump
 * @param string $var_name  -- name of variable (optional) -- displayed in printout making it easier to sort out what variable is what in a complex output
 * @param string $indent -- used by internal recursive call (no known external value)
 * @param unknown_type $reference -- used by internal recursive call (no known external value)
 */
function do_dump(&$var, $var_name = NULL, $indent = NULL, $reference = NULL)
{
    $do_dump_indent = "<span style='color:#666666;'>|</span> &nbsp;&nbsp; ";
    $reference = $reference.$var_name;
    $keyvar = 'the_do_dump_recursion_protection_scheme'; $keyname = 'referenced_object_name';
    
    // So this is always visible and always left justified and readable
    echo "<div style='text-align:left; background-color:white; font: 100% monospace; color:black;'>";

    if (is_array($var) && isset($var[$keyvar]))
    {
        $real_var = &$var[$keyvar];
        $real_name = &$var[$keyname];
        $type = ucfirst(gettype($real_var));
        echo "$indent$var_name <span style='color:#666666'>$type</span> = <span style='color:#e87800;'>&amp;$real_name</span><br>";
    }
    else
    {
        $var = array($keyvar => $var, $keyname => $reference);
        $avar = &$var[$keyvar];

        $type = ucfirst(gettype($avar));
        if($type == "String") $type_color = "<span style='color:green'>";
        elseif($type == "Integer") $type_color = "<span style='color:red'>";
        elseif($type == "Double"){ $type_color = "<span style='color:#0099c5'>"; $type = "Float"; }
        elseif($type == "Boolean") $type_color = "<span style='color:#92008d'>";
        elseif($type == "NULL") $type_color = "<span style='color:black'>";

        if(is_array($avar))
        {
            $count = count($avar);
            echo "$indent" . ($var_name ? "$var_name => ":"") . "<span style='color:#666666'>$type ($count)</span><br>$indent(<br>";
            $keys = array_keys($avar);
            foreach($keys as $name)
            {
                $value = &$avar[$name];
                do_dump($value, "['$name']", $indent.$do_dump_indent, $reference);
            }
            echo "$indent)<br>";
        }
        elseif(is_object($avar))
        {
            echo "$indent$var_name <span style='color:#666666'>$type</span><br>$indent(<br>";
            foreach($avar as $name=>$value) do_dump($value, "$name", $indent.$do_dump_indent, $reference);
            echo "$indent)<br>";
        }
        elseif(is_int($avar)) echo "$indent$var_name = <span style='color:#666666'>$type(".strlen($avar).")</span> $type_color".htmlentities($avar)."</span><br>";
        elseif(is_string($avar)) echo "$indent$var_name = <span style='color:#666666'>$type(".strlen($avar).")</span> $type_color\"".htmlentities($avar)."\"</span><br>";
        elseif(is_float($avar)) echo "$indent$var_name = <span style='color:#666666'>$type(".strlen($avar).")</span> $type_color".htmlentities($avar)."</span><br>";
        elseif(is_bool($avar)) echo "$indent$var_name = <span style='color:#666666'>$type(".strlen($avar).")</span> $type_color".($avar == 1 ? "TRUE":"FALSE")."</span><br>";
        elseif(is_null($avar)) echo "$indent$var_name = <span style='color:#666666'>$type(".strlen($avar).")</span> {$type_color}NULL</span><br>";
        else echo "$indent$var_name = <span style='color:#666666'>$type(".strlen($avar).")</span> ".htmlentities($avar)."<br>";

        $var = $var[$keyvar];
    }
    
    echo "</div>";
}

/**
 * Look up the local manager_id for a given Yahoo team id/year. Used by both
 * the OAuth/API path (yahooApiRequest.php) and the web-scrape fallback path
 * (yahooScrapeRequest.php) — moved here so both files can share it instead
 * of duplicating it.
 */
function lookupManager(int $yahooTeamId, int $year)
{
    $result = query("SELECT managers.id FROM season_managers
        JOIN managers on managers.id = season_managers.manager_id
        WHERE yahoo_id = $yahooTeamId and year = $year");
    while ($manager = fetch_array($result)) {
        return $manager['id'];
    }
}

/**
 * Look up the Yahoo league_id for a given year. Same year -> league_id
 * mapping already hardcoded in yahooApi.php/yahooApiRequest.php (only the
 * league_id half is needed here — the OAuth path's game_code isn't used by
 * the scrape path, which drives real web pages instead of the Yahoo API).
 * Returns null if the year isn't a known season, so callers can surface a
 * clear error instead of passing a bad league id downstream.
 */
function getSeasonLeagueId(int $year): ?int
{
    $seasons = [
        2026 => 18261,
        2025 => 23237,
        2024 => 98957,
        2023 => 74490,
        2022 => 84027,
        2021 => 16064,
        2020 => 43673,
        2019 => 201651,
        2018 => 224863,
        2017 => 262191,
        2016 => 477642,
        2015 => 217861,
        2014 => 53077,
        2013 => 27577,
        2012 => 26725,
        2011 => 163601,
        2010 => 35443,
        2009 => 42150,
        2008 => 8224,
        2007 => 73988,
        2006 => 48909,
    ];

    return $seasons[$year] ?? null;
}

function query($sql)
{
    global $conn, $DB_TYPE;

    if ($DB_TYPE == 'sqlite') {
        $sql = str_replace("if(", "iif(", $sql);
        $sql = str_replace("IF(", "IIF(", $sql);
        $sql = str_replace("IF (", "IIF (", $sql);

        try {
            $result = $conn->query($sql);

            return $result;
        } catch (Exception $e) {
            dd("Exception in query: " . $e->getMessage());
            return false;
        }
    }

    $result = mysqli_query($conn, $sql);
    if (!$result) {
        dd("MySQL Error: " . mysqli_error($conn));
    }
    return $result;
}

function fetch_array($result)
{
    global $DB_TYPE;

    if ($DB_TYPE == 'sqlite') {
        return $result->fetchArray();
    } 
        
    return mysqli_fetch_array($result);
}

/**
 * Look in table for rows matching params. If found, update. If not found, insert.
 */
function updateOrCreate(string $table, array $params, array $values)
{
    $lastId = null;
    $query = "SELECT * FROM {$table} WHERE ";
    foreach ($params as $key => $value) {
        // Use SQLite3::escapeString for PDO or just quote the value
        $escapedValue = is_string($value) ? str_replace("'", "''", $value) : $value;
        $query .= "{$key} = '{$escapedValue}' AND ";
    }
    $query = substr($query, 0, -5);
    $result = query($query);
    $row = fetch_array($result);
    if ($row) {
        // update
        $query = "UPDATE {$table} SET ";
        foreach ($values as $key => $value) {
            // Properly escape values
            $escapedValue = is_string($value) ? str_replace("'", "''", $value) : $value;
            $query .= "{$key} = '{$escapedValue}', ";
        }
        $query = substr($query, 0, -2);
        $query .= " WHERE ";
        foreach ($params as $key => $value) {
            $escapedValue = is_string($value) ? str_replace("'", "''", $value) : $value;
            $query .= "{$key} = '{$escapedValue}' AND ";
        }
        $query = substr($query, 0, -5);
        $result = query($query);
    } else {
        // insert
        $query = "INSERT INTO {$table} (";
        foreach ($params as $key => $value) {
            $query .= "{$key}, ";
        }
        foreach ($values as $key => $value) {
            $query .= "{$key}, ";
        }
        $query = substr($query, 0, -2);
        $query .= ") VALUES (";
        foreach ($params as $key => $value) {
            $escapedValue = is_string($value) ? str_replace("'", "''", $value) : $value;
            $query .= "'{$escapedValue}', ";
        }
        foreach ($values as $key => $value) {
            $escapedValue = is_string($value) ? str_replace("'", "''", $value) : $value;
            $query .= "'{$escapedValue}', ";
        }
        $query = substr($query, 0, -2);
        $query .= ")";
        $result = query($query);

        // query for the id of the item just inserted
        $query = "SELECT id FROM {$table} ORDER BY id DESC LIMIT 1";
        $result = query($query);
        $row = fetch_array($result);
        $lastId = $row['id'];
    }

    // return the id of the item just inserted
    if ($lastId) {
        return $lastId;
    } else {
        return $row['id'];
    }
}

/**
 * Recalculate the `standings` table for a given year/week from scratch,
 * from ALL regular_season_matchups rows through that week. Moved here
 * (originally lived in yahooApiRequest.php) so the web-scrape fallback path
 * (yahooScrapeRequest.php's handle_scraped_matchups()) can call it too,
 * without duplicating it — matches this file's existing pattern for
 * lookupManager()/getSeasonLeagueId(). Behavior is unchanged from the
 * original.
 */
function updateStandingsForWeek($year, $week) {

    // Delete existing standings for this week to recalculate
    $deleteResult = query("DELETE FROM standings WHERE year = $year AND week = $week");

    // Calculate cumulative standings through this week
    $standings = [];

    // Initialize standings for all managers (assuming 10 managers)
    for ($x = 1; $x <= 10; $x++) {
        $standings[$x] = [
            'manager_id' => $x,
            'wins' => 0,
            'losses' => 0,
            'points' => 0,
            'name' => ''
        ];
    }

    // Get all matchups through current week - avoid duplicates by using manager1_id < manager2_id
    $result = query("SELECT rsm.*, m1.name as manager1_name, m2.name as manager2_name
                    FROM regular_season_matchups rsm
                    JOIN managers m1 ON rsm.manager1_id = m1.id
                    JOIN managers m2 ON rsm.manager2_id = m2.id
                    WHERE rsm.year = $year AND rsm.week_number <= $week AND rsm.manager1_id < rsm.manager2_id
                    ORDER BY rsm.week_number");

    while ($row = fetch_array($result)) {
        // Process manager1
        if (isset($standings[$row['manager1_id']])) {
            if ($row['winning_manager_id'] == $row['manager1_id']) {
                $standings[$row['manager1_id']]['wins']++;
            } else {
                $standings[$row['manager1_id']]['losses']++;
            }
            $standings[$row['manager1_id']]['name'] = $row['manager1_name'];
            $standings[$row['manager1_id']]['points'] += $row['manager1_score'];
        }

        // Process manager2 in the same loop
        if (isset($standings[$row['manager2_id']])) {
            if ($row['winning_manager_id'] == $row['manager2_id']) {
                $standings[$row['manager2_id']]['wins']++;
            } else {
                $standings[$row['manager2_id']]['losses']++;
            }
            $standings[$row['manager2_id']]['name'] = $row['manager2_name'];
            $standings[$row['manager2_id']]['points'] += $row['manager2_score'];
        }
    }

    // Remove managers with no data (empty name)
    $standings = array_filter($standings, function($standing) {
        return !empty($standing['name']);
    });

    // Sort by wins (desc) then points (desc) to determine rankings
    usort($standings, function($a, $b) {
        $winDiff = $b['wins'] - $a['wins'];
        if ($winDiff != 0) return $winDiff;

        return $b['points'] <=> $a['points'];
    });

    // Insert updated standings into database
    $rank = 1;
    foreach ($standings as $standing) {
        $managerId = $standing['manager_id'];
        $points = round($standing['points'], 2);
        $wins = $standing['wins'];
        $losses = $standing['losses'];

        $insertSql = "INSERT INTO standings (year, week, manager_id, rank, points, wins, losses)
                     VALUES ($year, $week, $managerId, $rank, $points, $wins, $losses)";

        $insertResult = query($insertSql);
        echo "<br>Inserted standing for manager {$managerId} ({$standing['name']}) - rank {$rank}, wins {$wins}, losses {$losses}, points {$points}";
        $rank++;
    }
}

// Look in table for rows matching params. If found, return id. If not found, insert and return id.
function firstOrCreate(string $table, array $params, array $values) {
    $lastId = null;
    $query = "SELECT * FROM {$table} WHERE ";
    foreach ($params as $key => $value) {
        $escapedValue = is_string($value) ? str_replace("'", "''", $value) : $value;
        $query .= "{$key} = '{$escapedValue}' AND ";
    }
    $query = substr($query, 0, -5);
    $result = query($query);
    $row = fetch_array($result);
    if ($row) {
        // return id
        return $row['id'];
    } else {
        // insert
        $query = "INSERT INTO {$table} (";
        foreach ($params as $key => $value) {
            $query .= "{$key}, ";
        }
        foreach ($values as $key => $value) {
            $query .= "{$key}, ";
        }
        $query = substr($query, 0, -2);
        $query .= ") VALUES (";
        foreach ($params as $key => $value) {
            $escapedValue = is_string($value) ? str_replace("'", "''", $value) : $value;
            $query .= "'{$escapedValue}', ";
        }
        foreach ($values as $key => $value) {
            $escapedValue = is_string($value) ? str_replace("'", "''", $value) : $value;
            $query .= "'{$escapedValue}', ";
        }
        $query = substr($query, 0, -2);
        $query .= ")";
        $result = query($query);

        // query for the id of the item just inserted
        $query = "SELECT id FROM {$table} ORDER BY id DESC LIMIT 1";
        $result = query($query);
        $row = fetch_array($result);
        $lastId = $row['id'];
    }

    // return the id of the item just inserted
    if ($lastId) {
        return $lastId;
    } else {
        return $row['id'];
    }
}

function calculateOptimalForManager(string $managerName, int $year, int $week): float
{
    // Get season position slots (excluding bench/IR), ordered by sort_order
    $positions = [];
    $positionCounts = [];
    $result = query("SELECT position FROM season_positions WHERE year = $year AND position NOT IN ('BN', 'IR') ORDER BY sort_order ASC");
    while ($row = fetch_array($result)) {
        $pos = strtolower($row['position']);
        $positions[] = $pos;
        $positionCounts[$pos] = isset($positionCounts[$pos]) ? $positionCounts[$pos] + 1 : 1;
    }

    if (empty($positions)) {
        return 0.0;
    }

    // Build optimal roster slot array (multi-slot positions get numbered keys: rb1, rb2, wr1, wr2, etc.)
    $optimalRoster = [];
    $slotCounters = [];
    foreach ($positions as $pos) {
        if ($positionCounts[$pos] > 1) {
            $slotCounters[$pos] = isset($slotCounters[$pos]) ? $slotCounters[$pos] + 1 : 1;
            $optimalRoster[$pos . $slotCounters[$pos]] = 0;
        } else {
            $optimalRoster[$pos] = 0;
        }
    }

    // Detect flex and super-flex keys
    $flexKey = null;
    if (array_key_exists('w/r/t', $optimalRoster)) {
        $flexKey = 'w/r/t';
    } elseif (array_key_exists('wrt', $optimalRoster)) {
        $flexKey = 'wrt';
    } elseif (array_key_exists('w/r', $optimalRoster)) {
        $flexKey = 'w/r';
    } elseif (array_key_exists('w/t', $optimalRoster)) {
        $flexKey = 'w/t';
    }

    $superFlexKey = null;
    if (array_key_exists('q/w/r/t', $optimalRoster)) {
        $superFlexKey = 'q/w/r/t';
    } elseif (array_key_exists('qwrt', $optimalRoster)) {
        $superFlexKey = 'qwrt';
    }

    $totalSlots = count($positions);

    // Fetch all rostered players (including bench, excluding IR) sorted descending by points
    // Bench players are included so the algorithm can determine the true optimal starting lineup
    $safeManager = str_replace("'", "''", $managerName);
    $roster = [];
    $result = query("SELECT position, points FROM rosters WHERE manager = '$safeManager' AND year = $year AND week = $week AND roster_spot NOT IN ('IR', 'N/A') ORDER BY points DESC");
    while ($row = fetch_array($result)) {
        $roster[] = ['pos' => $row['position'], 'points' => (float)$row['points']];
    }

    $fullRoster = 0;
    foreach ($roster as $player) {
        if ($fullRoster >= $totalSlots) {
            break;
        }
        $pos = $player['pos'];
        $pts = $player['points'];

        if ($pos === 'QB') {
            if (isset($optimalRoster['qb1']) && $optimalRoster['qb1'] == 0) {
                $optimalRoster['qb1'] = $pts; $fullRoster++;
            } elseif (isset($optimalRoster['qb2']) && $optimalRoster['qb2'] == 0) {
                $optimalRoster['qb2'] = $pts; $fullRoster++;
            } elseif (isset($optimalRoster['qb']) && $optimalRoster['qb'] == 0) {
                $optimalRoster['qb'] = $pts; $fullRoster++;
            } elseif ($superFlexKey && $optimalRoster[$superFlexKey] == 0) {
                $optimalRoster[$superFlexKey] = $pts; $fullRoster++;
            }
        } elseif ($pos === 'RB') {
            if (isset($optimalRoster['rb1']) && $optimalRoster['rb1'] == 0) {
                $optimalRoster['rb1'] = $pts; $fullRoster++;
            } elseif (isset($optimalRoster['rb2']) && $optimalRoster['rb2'] == 0) {
                $optimalRoster['rb2'] = $pts; $fullRoster++;
            } elseif (isset($optimalRoster['rb']) && $optimalRoster['rb'] == 0) {
                $optimalRoster['rb'] = $pts; $fullRoster++;
            } elseif ($flexKey && $optimalRoster[$flexKey] == 0) {
                $optimalRoster[$flexKey] = $pts; $fullRoster++;
            } elseif ($superFlexKey && $optimalRoster[$superFlexKey] == 0) {
                $optimalRoster[$superFlexKey] = $pts; $fullRoster++;
            }
        } elseif ($pos === 'WR') {
            if (isset($optimalRoster['wr1']) && $optimalRoster['wr1'] == 0) {
                $optimalRoster['wr1'] = $pts; $fullRoster++;
            } elseif (isset($optimalRoster['wr2']) && $optimalRoster['wr2'] == 0) {
                $optimalRoster['wr2'] = $pts; $fullRoster++;
            } elseif (isset($optimalRoster['wr3']) && $optimalRoster['wr3'] == 0) {
                $optimalRoster['wr3'] = $pts; $fullRoster++;
            } elseif (isset($optimalRoster['wr4']) && $optimalRoster['wr4'] == 0) {
                $optimalRoster['wr4'] = $pts; $fullRoster++;
            } elseif (isset($optimalRoster['wr']) && $optimalRoster['wr'] == 0) {
                $optimalRoster['wr'] = $pts; $fullRoster++;
            } elseif ($flexKey && $optimalRoster[$flexKey] == 0) {
                $optimalRoster[$flexKey] = $pts; $fullRoster++;
            } elseif ($superFlexKey && $optimalRoster[$superFlexKey] == 0) {
                $optimalRoster[$superFlexKey] = $pts; $fullRoster++;
            }
        } elseif ($pos === 'TE') {
            if (isset($optimalRoster['te']) && $optimalRoster['te'] == 0) {
                $optimalRoster['te'] = $pts; $fullRoster++;
            } elseif ($flexKey && $optimalRoster[$flexKey] == 0) {
                $optimalRoster[$flexKey] = $pts; $fullRoster++;
            } elseif ($superFlexKey && $optimalRoster[$superFlexKey] == 0) {
                $optimalRoster[$superFlexKey] = $pts; $fullRoster++;
            }
        } elseif ($pos === 'K') {
            if (isset($optimalRoster['k']) && $optimalRoster['k'] == 0) {
                $optimalRoster['k'] = $pts; $fullRoster++;
            }
        } elseif ($pos === 'DEF') {
            if (isset($optimalRoster['def1']) && $optimalRoster['def1'] == 0) {
                $optimalRoster['def1'] = $pts; $fullRoster++;
            } elseif (isset($optimalRoster['def2']) && $optimalRoster['def2'] == 0) {
                $optimalRoster['def2'] = $pts; $fullRoster++;
            } elseif (isset($optimalRoster['def']) && $optimalRoster['def'] == 0) {
                $optimalRoster['def'] = $pts; $fullRoster++;
            }
        } elseif (in_array($pos, ['D', 'DL', 'LB', 'DB'])) {
            $posLower = strtolower($pos);
            if (isset($optimalRoster[$posLower.'1']) && $optimalRoster[$posLower.'1'] == 0) {
                $optimalRoster[$posLower.'1'] = $pts; $fullRoster++;
            } elseif (isset($optimalRoster[$posLower.'2']) && $optimalRoster[$posLower.'2'] == 0) {
                $optimalRoster[$posLower.'2'] = $pts; $fullRoster++;
            } elseif (isset($optimalRoster[$posLower]) && $optimalRoster[$posLower] == 0) {
                $optimalRoster[$posLower] = $pts; $fullRoster++;
            }
        }
    }

    return (float)array_sum($optimalRoster);
}

?>