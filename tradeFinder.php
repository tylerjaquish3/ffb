<?php

$pageName = "Trade Finder";
include 'header.php';
include 'sidebar.html';

// Check if environment is set to production
if (isset($APP_ENV) && $APP_ENV === 'production') {
    header("Location: 404.php");
    exit;
}

// Look up how many weeks of data there are
$weeks = 0;
$result = query("SELECT distinct week FROM rosters WHERE year = $season");
while ($row = fetch_array($result)) {
    $weeks++;
}

// Look up all the actual points
$posPts = [];
$result = query("SELECT position, manager, SUM(points) as pts 
    FROM rosters 
    WHERE year = $season
    GROUP BY manager, position");
while ($row = fetch_array($result)) {
    $posPts[$row['manager']][$row['position']] = round($row['pts'], 1);
}

function getRanksByPos(array $teams, string $pos)
{
    usort($teams, function($a, $b) use ($pos) {
        return $b[$pos] <=> $a[$pos];
    });

    $x = 1;
    foreach ($teams as &$team) {
        $team[$pos.'rank'] = $x;
        $x++;
    }

    return $teams;
}

function printFinderRow($team, $targets, $pos)
{
    if ($team[$pos.'rank'] > 5) { 
        echo '<td>Needs '.$pos.' ('.$team[$pos.'rank'].')'; 
    } else {
        if ($team['man'] == 'Tyler') {
            echo '<td>Rank: '.$team[$pos.'rank'];
        } else {
            echo '<td>';
        }
    }
    foreach ($targets as $target) {
        if ($target['owner'] == $team['man'] && $target['pos'] == $pos) {
            echo '<br>'.$target['player'];
        }
    }
    echo '</td>';
}

// Function to get points for a player, trying both name and alias
function getPlayerPoints($draftPlayerName, $playerPts, $aliasLookup) {
    // Try direct name match first
    if (isset($playerPts[$draftPlayerName])) {
        return $playerPts[$draftPlayerName];
    }
    
    // Try to find by alias lookup (draft name -> roster name)
    foreach ($aliasLookup as $alias => $draftName) {
        if ($draftName === $draftPlayerName && isset($playerPts[$alias])) {
            return $playerPts[$alias];
        }
    }
    
    // Try reverse - see if this roster name matches any draft alias
    if (isset($aliasLookup[$draftPlayerName])) {
        $actualDraftName = $aliasLookup[$draftPlayerName];
        if (isset($playerPts[$actualDraftName])) {
            return $playerPts[$actualDraftName];
        }
    }
    
    return 0;
}
?>

<div class="app-content content container-fluid">
    <div class="content-wrapper">
        <div class="content-header row"></div>

        <div class="content-body">
            <div class="row">

                <div class="col-sm-12 col-md-6 table-padding">
                    <div class="card">
                        <div class="card-header">
                            <h4>Players to Target</h4>
                            <span id="count"></span>
                        </div>
                        <div class="card-body" style="background: #fff; direction: ltr">
                            <table class="stripe row-border order-column" id="datatable-results">
                                <thead>
                                    <th>Player</th>
                                    <th>Projected</th>
                                    <th>Points</th>
                                    <th>Diff</th>
                                    <th>Owner</th>
                                </thead>
                                <tbody>
                                    <?php
                                    // Look up all the actual points
                                    $playerPts = [];
                                    $result = query("SELECT player, SUM(points) as pts FROM rosters 
                                        WHERE year = $season
                                        GROUP BY player");
                                    while ($row = fetch_array($result)) {
                                        $name = $row['player'];
                                        $playerPts[$name] = round($row['pts'], 1);
                                    }

                                    // Build player name/alias lookup from draft database
                                    $playerNameLookup = [];
                                    $aliasLookup = [];
                                    $result = draft_query("SELECT name, alias FROM players WHERE alias IS NOT NULL AND alias != ''");
                                    while ($row = fetch_array($result)) {
                                        $playerNameLookup[$row['name']] = $row['name']; // name to name
                                        $aliasLookup[$row['alias']] = $row['name']; // alias to name
                                        $playerNameLookup[$row['alias']] = $row['name']; // alias to name (for reverse lookup)
                                    }

                                    $targets = [];
                                    $result = draft_query("SELECT p.name as player, p.alias, proj_points, m.name, positions.name as position 
                                        FROM league_player_details lpd
                                        JOIN players p on p.id = lpd.player_id
                                        JOIN draft_selections ds ON ds.player_id = p.id
                                        JOIN positions on positions.id = p.position_id
                                        JOIN league_managers m ON m.id = ds.manager_id
                                        WHERE manager_id != 10 and lpd.league_id = 1 and m.league_id = 1
                                        and ds.year = $season and lpd.year = $season");
                                    while ($row = fetch_array($result)) {

                                        $pts = getPlayerPoints($row['player'], $playerPts, $aliasLookup);
                                        $proj = round(($row['proj_points']/14) * $weeks, 1);
                                        if ($proj - $pts > (7*$weeks)) {
                                            $targets[] = [
                                                'player' => $row['player'],
                                                'pos' => $row['position'],
                                                'owner' => $row['name']
                                            ];
                                            echo '<tr><td>'.$row['player'].'</td><td>'.$proj.'</td><td>'.$pts.'</td><td>'.($proj-$pts).'</td><td>'.$row['name'].'</td></tr>';
                                        }
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-sm-12 col-md-6 table-padding">
                    <div class="card">
                        <div class="card-header">
                            <h4>My Trade Candidates</h4>
                            <span id="count"></span>
                        </div>
                        <div class="card-body" style="background: #fff; direction: ltr">
                            <table class="stripe row-border order-column" id="datatable-mine">
                                <thead>
                                    <th>Player</th>
                                    <th>Projected</th>
                                    <th>Points</th>
                                    <th>Diff</th>
                                    <th>Bye</th>
                                </thead>
                                <tbody>
                                    <?php
                                    // Look up how many weeks of data there are
                                    $weeks = 0;
                                    $result = query("SELECT distinct week FROM rosters 
                                        WHERE YEAR = $season");
                                    while ($row = fetch_array($result)) {
                                        $weeks++;
                                    }

                                    // Look up all the actual points (reuse from above if exists)
                                    if (!isset($playerPts)) {
                                        $playerPts = [];
                                        $result = query("SELECT player, SUM(points) as pts FROM rosters 
                                            WHERE YEAR = $season
                                            GROUP BY player");
                                        while ($row = fetch_array($result)) {
                                            $name = $row['player'];
                                            $playerPts[$name] = round($row['pts'], 1);
                                        }
                                    }

                                    // Reuse the alias lookup from above if exists
                                    if (!isset($aliasLookup)) {
                                        $playerNameLookup = [];
                                        $aliasLookup = [];
                                        $result = draft_query("SELECT name, alias FROM players WHERE alias IS NOT NULL AND alias != ''");
                                        while ($row = fetch_array($result)) {
                                            $playerNameLookup[$row['name']] = $row['name'];
                                            $aliasLookup[$row['alias']] = $row['name'];
                                            $playerNameLookup[$row['alias']] = $row['name'];
                                        }
                                    }

                                    // Get Tyler's current roster from the latest week
                                    $result = query("SELECT DISTINCT player FROM rosters 
                                        WHERE manager = 'Tyler' AND year = $season 
                                        AND week = (SELECT MAX(week) FROM rosters WHERE year = $season)");
                                    
                                    $tylerPlayers = [];
                                    while ($row = fetch_array($result)) {
                                        $tylerPlayers[] = $row['player'];
                                    }

                                    // Now get projection data for Tyler's players from draft database
                                    $playerProjections = [];
                                    if (!empty($tylerPlayers)) {
                                        $playerNames = "'" . implode("','", array_map('addslashes', $tylerPlayers)) . "'";
                                        $aliasNames = [];
                                        
                                        // Also check for aliases that might match Tyler's players
                                        foreach ($tylerPlayers as $player) {
                                            if (isset($aliasLookup[$player])) {
                                                $aliasNames[] = $aliasLookup[$player];
                                            }
                                        }
                                        
                                        $allNames = $playerNames;
                                        if (!empty($aliasNames)) {
                                            $aliasNamesStr = "'" . implode("','", array_map('addslashes', $aliasNames)) . "'";
                                            $allNames = $playerNames . "," . $aliasNamesStr;
                                        }

                                        $result = draft_query("SELECT p.name as player, p.alias, proj_points, positions.name as position 
                                            FROM league_player_details lpd
                                            JOIN players p on p.id = lpd.player_id
                                            JOIN positions on positions.id = p.position_id
                                            WHERE lpd.league_id = 1 and lpd.year = $season 
                                            AND (p.name IN ($allNames) OR p.alias IN ($playerNames))");
                                        
                                        while ($row = fetch_array($result)) {
                                            $playerProjections[$row['player']] = [
                                                'proj_points' => $row['proj_points'],
                                                'position' => $row['position'],
                                                'alias' => $row['alias']
                                            ];
                                            // Also index by alias if it exists
                                            if (!empty($row['alias'])) {
                                                $playerProjections[$row['alias']] = [
                                                    'proj_points' => $row['proj_points'],
                                                    'position' => $row['position'],
                                                    'alias' => $row['alias']
                                                ];
                                            }
                                        }
                                    }

                                    // Display Tyler's trade candidates
                                    foreach ($tylerPlayers as $player) {
                                        $pts = getPlayerPoints($player, $playerPts, $aliasLookup);
                                        
                                        // Try to get projection data
                                        $projData = null;
                                        if (isset($playerProjections[$player])) {
                                            $projData = $playerProjections[$player];
                                        } else {
                                            // Try by alias lookup
                                            foreach ($aliasLookup as $alias => $draftName) {
                                                if ($alias === $player && isset($playerProjections[$draftName])) {
                                                    $projData = $playerProjections[$draftName];
                                                    break;
                                                }
                                            }
                                        }
                                        
                                        if ($projData) {
                                            $proj = round(($projData['proj_points']/14) * $weeks, 1);
                                            echo '<tr><td>'.$player.'</td><td>'.$proj.'</td><td>'.$pts.'</td><td>'.($proj-$pts).'</td><td>'.$projData['position'].'</td></tr>';
                                        } else {
                                            // If no projection data found, still show the player
                                            echo '<tr><td>'.$player.'</td><td>N/A</td><td>'.$pts.'</td><td>N/A</td><td>--</td></tr>';
                                        }
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-sm-12 col-md-6 table-padding">
                    <div class="card">
                        <div class="card-header">
                            <h4>Points by Position</h4>
                            <span id="count"></span>
                        </div>
                        <div class="card-body" style="background: #fff; direction: ltr">
                            <table class="stripe row-border order-column" id="datatable-teamPosPts">
                                <thead>
                                    <th>Owner</th>
                                    <th>QB</th>
                                    <th>RB</th>
                                    <th>WR</th>
                                    <th>TE</th>
                                    <th>K</th>
                                    <th>DEF</th>
                                </thead>
                                <tbody>
                                    <?php
                                    foreach ($posPts as $man => $pts) { 
                                        echo '<tr><td>'.$man.'</td><td>'.$pts['QB'].'</td><td>'.$pts['RB'].'</td><td>'.$pts['WR'].'</td><td>'.$pts['TE'].'</td><td>'.$pts['K'].'</td><td>'.$pts['DEF'].'</td></tr>';
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-sm-12 table-padding">
                    <div class="card">
                        <div class="card-header">
                            <h4>Trade Finder</h4>
                            <span id="count"></span>
                        </div>
                        <div class="card-body" style="background: #fff; direction: ltr">
                            <table class="stripe row-border order-column" id="datatable-tradeFinder">
                                <thead>
                                    <th>Manager</th>
                                    <th>QB</th>
                                    <th>RB</th>
                                    <th>WR</th>
                                    <th>TE</th>
                                    <th>K</th>
                                    <th>DEF</th>
                                </thead>
                            <?php 
                            foreach ($posPts as $man => $pos) {
                                $teams[] = [
                                    'man' => $man,
                                    'QB' => $pos['QB'],
                                    'RB' => $pos['RB'],
                                    'WR' => $pos['WR'],
                                    'TE' => $pos['TE'],
                                    'K' => $pos['K'],
                                    'DEF' => $pos['DEF']
                                ];
                            }
                            $positions = ['QB', 'RB', 'WR', 'TE', 'K', 'DEF'];
                            foreach ($positions as $pos) {
                                $teams = getRanksByPos($teams, $pos);
                            }
                            
                            foreach ($teams as $team) {
                                echo '<tr><td>'.$team['man'].'</td>';
                                
                                foreach ($positions as $pos) {
                                    printFinderRow($team, $targets, $pos);
                                }

                                echo '</tr>';
                            }
                            ?>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-sm-12 table-padding">
                    <div class="card">
                        <div class="card-body" style="background: #fff; direction: ltr">
                            <?php
                            // Analyze possible trades
                            
                            // First, determine Tyler's strengths and weaknesses
                            $tylerNeeds = [];
                            $tylerStrengths = [];
                            $tylerData = null;
                            
                            foreach ($teams as $team) {
                                if ($team['man'] == 'Tyler') {
                                    $tylerData = $team;
                                    foreach ($positions as $pos) {
                                        $rank = $team[$pos.'rank'];
                                        if ($rank > 7) { // Bottom 3 teams (need help)
                                            $tylerNeeds[] = $pos;
                                        } elseif ($rank <= 3) { // Top 3 teams (strength)
                                            $tylerStrengths[] = $pos;
                                        }
                                    }
                                    break;
                                }
                            }
                            
                            // Build Tyler's player data for trade analysis
                            $tylerPlayersByPosition = [];
                            foreach ($tylerPlayers as $player) {
                                if (isset($playerProjections[$player])) {
                                    $pos = $playerProjections[$player]['position'];
                                    $pts = getPlayerPoints($player, $playerPts, $aliasLookup);
                                    $proj = round(($playerProjections[$player]['proj_points']/14) * $weeks, 1);
                                    $tylerPlayersByPosition[$pos][] = [
                                        'name' => $player,
                                        'points' => $pts,
                                        'proj' => $proj,
                                        'diff' => $proj - $pts
                                    ];
                                }
                            }
                            
                            // Sort Tyler's players by performance within each position
                            foreach ($tylerPlayersByPosition as $pos => $players) {
                                usort($tylerPlayersByPosition[$pos], function($a, $b) {
                                    return $b['diff'] <=> $a['diff']; // Best performers first
                                });
                            }
                            
                            // Find trade opportunities
                            $tradeOpportunities = [];
                            
                            foreach ($teams as $team) {
                                if ($team['man'] == 'Tyler') continue;
                                
                                $managerName = $team['man'];
                                $managerNeeds = [];
                                $managerStrengths = [];
                                
                                // Find this manager's needs and strengths
                                foreach ($positions as $pos) {
                                    $rank = $team[$pos.'rank'];
                                    if ($rank > 7) { // They need help
                                        $managerNeeds[] = $pos;
                                    } elseif ($rank <= 3) { // They are strong
                                        $managerStrengths[] = $pos;
                                    }
                                }
                                
                                // Find players Tyler wants from this manager
                                $availablePlayers = [];
                                foreach ($targets as $target) {
                                    if ($target['owner'] == $managerName) {
                                        $availablePlayers[$target['pos']][] = $target['player'];
                                    }
                                }
                                
                                $managerTrades = [];
                                
                                // STRATEGY 1: Perfect mutual benefit trades
                                foreach ($tylerStrengths as $tylerStrong) {
                                    if (in_array($tylerStrong, $managerNeeds)) {
                                        foreach ($managerStrengths as $managerStrong) {
                                            if (in_array($managerStrong, $tylerNeeds)) {
                                                if (isset($tylerPlayersByPosition[$tylerStrong]) && isset($availablePlayers[$managerStrong])) {
                                                    $managerTrades[] = [
                                                        'tyler_gives' => array_slice($tylerPlayersByPosition[$tylerStrong], -1, 1), // Worst performer
                                                        'tyler_gets' => array_slice($availablePlayers[$managerStrong], 0, 1),
                                                        'reasoning' => "Perfect match: Tyler's {$tylerStrong} strength for {$managerName}'s {$managerStrong} strength",
                                                        'type' => 'mutual'
                                                    ];
                                                }
                                            }
                                        }
                                    }
                                }
                                
                                // STRATEGY 2: Tyler wants specific players (regardless of position match)
                                foreach ($availablePlayers as $pos => $players) {
                                    // Find what Tyler could offer that this manager needs
                                    foreach ($managerNeeds as $neededPos) {
                                        if (isset($tylerPlayersByPosition[$neededPos]) && !empty($tylerPlayersByPosition[$neededPos])) {
                                            $managerTrades[] = [
                                                'tyler_gives' => array_slice($tylerPlayersByPosition[$neededPos], 0, 1), // Best performer Tyler can offer
                                                'tyler_gets' => array_slice($players, 0, 1),
                                                'reasoning' => "Tyler wants {$pos}, offers needed {$neededPos} to {$managerName}",
                                                'type' => 'targeted'
                                            ];
                                        }
                                    }
                                    
                                    // Also consider trading from Tyler's strengths for players he wants
                                    foreach ($tylerStrengths as $strongPos) {
                                        if (isset($tylerPlayersByPosition[$strongPos]) && count($tylerPlayersByPosition[$strongPos]) > 1) {
                                            $managerTrades[] = [
                                                'tyler_gives' => array_slice($tylerPlayersByPosition[$strongPos], -1, 1), // Trade depth
                                                'tyler_gets' => array_slice($players, 0, 1),
                                                'reasoning' => "Tyler trades {$strongPos} depth for wanted {$pos} from {$managerName}",
                                                'type' => 'depth'
                                            ];
                                        }
                                    }
                                }
                                
                                // STRATEGY 3: 2-for-1 upgrades (Tyler's two weaker players for one stronger)
                                foreach ($tylerNeeds as $neededPos) {
                                    if (isset($availablePlayers[$neededPos]) && !empty($availablePlayers[$neededPos])) {
                                        // Find positions where Tyler has multiple players to offer
                                        foreach ($tylerPlayersByPosition as $pos => $players) {
                                            if (count($players) >= 2 && ($pos != $neededPos || count($players) >= 3)) {
                                                $managerTrades[] = [
                                                    'tyler_gives' => array_slice($players, -2, 2), // Two worst performers
                                                    'tyler_gets' => array_slice($availablePlayers[$neededPos], 0, 1),
                                                    'reasoning' => "Tyler upgrades: trades 2 {$pos}s for better {$neededPos} from {$managerName}",
                                                    'type' => 'upgrade'
                                                ];
                                            }
                                        }
                                    }
                                }
                                
                                // Add the best trades for this manager (limit to prevent spam)
                                foreach ($managerTrades as &$trade) {
                                    $trade['manager'] = $managerName; // Ensure manager name is included
                                }
                                $tradeOpportunities = array_merge($tradeOpportunities, array_slice($managerTrades, 0, 2));
                            }
                            
                            // Display header with count
                            echo '<div class="card-header border-0 pb-0">';
                            echo '<h4>Possible Trades <small class="text-muted">(' . count($tradeOpportunities) . ' suggestions)</small></h4>';
                            echo '<span class="text-muted">AI-generated trade suggestions based on positional needs and available players</span>';
                            echo '</div>';
                            echo '<hr class="mt-2 mb-4">';
                            
                            // Display trade opportunities
                            if (empty($tradeOpportunities)) {
                                echo '<div class="alert alert-info">No clear trade opportunities found based on current data. Try updating your roster or projections.</div>';
                            } else {
                                // Group trades by type for better organization
                                $tradesByType = [];
                                foreach ($tradeOpportunities as $trade) {
                                    $tradesByType[$trade['type']][] = $trade;
                                }
                                
                                $typeLabels = [
                                    'mutual' => 'Perfect Mutual Benefit Trades',
                                    'targeted' => 'Target Acquisition Trades', 
                                    'depth' => 'Depth-for-Upgrade Trades',
                                    'upgrade' => '2-for-1 Upgrade Trades'
                                ];
                                
                                $typeColors = [
                                    'mutual' => '#28a745',
                                    'targeted' => '#007bff', 
                                    'depth' => '#ffc107',
                                    'upgrade' => '#17a2b8'
                                ];
                                
                                foreach ($typeLabels as $type => $label) {
                                    if (isset($tradesByType[$type])) {
                                        echo '<h6 class="mb-3" style="color: ' . $typeColors[$type] . ';"><i class="fa fa-star"></i> ' . $label . '</h6>';
                                        foreach ($tradesByType[$type] as $trade) {
                                            echo '<div class="card mb-3" style="border-left: 4px solid ' . $typeColors[$type] . ';">';
                                            echo '<div class="card-body">';
                                            echo '<h6 class="card-title">Trade with ' . htmlspecialchars($trade['manager']) . '</h6>';
                                            
                                            echo '<div class="row">';
                                            echo '<div class="col-md-5">';
                                            echo '<strong class="text-danger">Tyler gives:</strong><br>';
                                            foreach ($trade['tyler_gives'] as $player) {
                                                echo '• ' . htmlspecialchars($player['name']) . 
                                                     ' <small>(' . $player['points'] . ' pts, proj: ' . $player['proj'] . ', diff: ' . 
                                                     ($player['diff'] >= 0 ? '+' : '') . $player['diff'] . ')</small><br>';
                                            }
                                            echo '</div>';
                                            
                                            echo '<div class="col-md-2 text-center">';
                                            echo '<i class="fa fa-exchange fa-2x" style="color: ' . $typeColors[$type] . '; margin-top: 20px;"></i>';
                                            echo '</div>';
                                            
                                            echo '<div class="col-md-5">';
                                            echo '<strong class="text-success">Tyler gets:</strong><br>';
                                            foreach ($trade['tyler_gets'] as $player) {
                                                echo '• ' . htmlspecialchars($player) . '<br>';
                                            }
                                            echo '</div>';
                                            echo '</div>';
                                            
                                            echo '<div class="mt-2">';
                                            echo '<small class="text-muted"><strong>Strategy:</strong> ' . htmlspecialchars($trade['reasoning']) . '</small>';
                                            echo '</div>';
                                            
                                            echo '</div>';
                                            echo '</div>';
                                        }
                                        echo '<hr class="my-4">';
                                    }
                                }
                            }
                            ?>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>

<script type="text/javascript">

    $('#datatable-results').DataTable({
        "searching": false,
        "paging": false,
        "info": false,
        "order": [
            [3, "desc"],
        ]
    });

    $('#datatable-mine').DataTable({
        "searching": false,
        "paging": false,
        "info": false,
        "order": [
            [3, "asc"],
        ]
    });

    $('#datatable-teamPosPts').DataTable({
        "searching": false,
        "paging": false,
        "info": false,
        "order": [
            [1, "asc"],
        ]
    });

    $('#datatable-tradeFinder').DataTable({
        "searching": false,
        "paging": false,
        "info": false,
        "order": [
            [0, "asc"],
        ]
    });

</script>

<style>
   
</style>