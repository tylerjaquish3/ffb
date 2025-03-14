<?php

$pageName = "Trade Finder";
include 'header.php';
include 'sidebar.html';


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

                                    $targets = [];
                                    $result = draft_query("SELECT p.name as player, proj_points, m.name, positions.name as position 
                                        FROM league_player_details lpd
                                        JOIN players p on p.id = lpd.player_id
                                        JOIN draft_selections ds ON ds.player_id = p.id
                                        JOIN positions on positions.id = p.position_id
                                        JOIN league_managers m ON m.id = ds.manager_id
                                        WHERE manager_id != 10 and lpd.league_id = 1 and m.league_id = 1
                                        and ds.year = $season and lpd.year = $season");
                                    while ($row = fetch_array($result)) {

                                        $pts = 0;
                                        if (isset($playerPts[$row['player']])) {
                                            $pts = $playerPts[$row['player']];
                                        }
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

                                    // Look up all the actual points
                                    $playerPts = [];
                                    $result = query("SELECT player, SUM(points) as pts FROM rosters 
                                        WHERE YEAR = $season
                                        GROUP BY player");
                                    while ($row = fetch_array($result)) {
                                        $name = $row['player'];
                                        $playerPts[$name] = round($row['pts'], 1);
                                    }

                                    $result = draft_query("SELECT p.name as player, proj_points, m.name, positions.name as position 
                                    FROM league_player_details lpd
                                    JOIN players p on p.id = lpd.player_id
                                    JOIN draft_selections ds ON ds.player_id = p.id
                                    JOIN positions on positions.id = p.position_id
                                    JOIN league_managers m ON m.id = ds.manager_id
                                    WHERE manager_id = 10 and lpd.league_id = 1 and m.league_id = 1 
                                    and ds.year = $season and lpd.year = $season");
                                    while ($row = fetch_array($result)) {

                                        $pts = 0;
                                        if (isset($playerPts[$row['player']])) {
                                            $pts = $playerPts[$row['player']];
                                        }
                                        $proj = round(($row['proj_points']/14) * $weeks, 1);

                                        echo '<tr><td>'.$row['player'].'</td><td>'.$proj.'</td><td>'.$pts.'</td><td>'.($proj-$pts).'</td><td>'.$row['name'].'</td></tr>';
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