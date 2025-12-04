<?php

$pageName = "Players";
include 'header.php';
include 'sidebar.php';

?>

<div class="app-content content">
    <div class="content-wrapper">

        <div class="content-body"> 
            <div class="row">
                <div class="col-sm-12 table-padding">
                    <div class="card">
                        <div class="card-header">
                            <h4>All Players</h4>
                        </div>
                        <div class="card-body" style="direction: ltr;">
                            <div class="row">
                                <div class="col-sm-12">
                                    <table class="table table-striped nowrap" id="datatable-players">
                                        <thead>
                                            <th></th>
                                            <th>Player</th>
                                            <th>Points</th>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-12 col-lg-6 table-padding">
                    <div class="card">
                        <div class="card-header">
                            <h4>Top Player Seasons</h4>
                            <span id="seasons-filter-btns">
                                <a type="button">QB</a>
                                &nbsp;|&nbsp;
                                <a type="button">RB</a>
                                &nbsp;|&nbsp;
                                <a type="button">WR</a>
                                &nbsp;|&nbsp;
                                <a type="button">TE</a>
                                &nbsp;|&nbsp;
                                <a type="button">DEF</a>
                                &nbsp;|&nbsp;
                                <a type="button">K</a>
                            </span>
                            &nbsp;|&nbsp;
                            <a type="button" id="seasons-show-all">Show All</a>
                        </div>
                        <div class="card-body" style="direction: ltr;">
                            <div class="row">
                                <div class="col-sm-12">
                                    <table class="table table-striped nowrap table-responsive" id="datatable-playerSeasons">
                                        <thead>
                                            <th>Year</th>
                                            <th>Manager</th>
                                            <th></th>
                                            <th>Player</th>
                                            <th>Team</th>
                                            <th>Position</th>
                                            <th>Points</th>
                                        </thead>
                                        <tbody>
                                        <?php
                                        $result = query(
                                            "SELECT player, year, team, position, sum(points) as points, max(manager) as man
                                            FROM rosters
                                            GROUP BY player, year, team
                                            ORDER BY points DESC
                                            LIMIT 200");
                                        while ($array = fetch_array($result)) { ?>
                                            <tr>
                                                <td><?php echo $array['year']; ?></td>
                                                <td><?php echo '<a href="/profile.php?id='.$array['man'].'">'.$array['man'].'</a>'; ?></td>
                                                <td>
                                                    <?php echo '<a href="/rosters.php?year='.$array['year'].'&week=1&manager='.$array['man'].'">
                                                    <i class="icon-clipboard"></i></a>&nbsp;&nbsp;&nbsp;';
                                                    echo '<a href="/draft.php?year='.$array['year'].'&manager='.$array['man'].'">
                                                    <i class="icon-table"></i></a>'; ?>
                                                </td>
                                                <td><?php echo '<a href="/players.php?player='.$array['player'].'">'.$array['player'].'</a>'; ?></td>
                                                <td><?php echo $array['team']; ?></td>
                                                <td><?php echo $array['position']; ?></td>
                                                <td><?php echo round($array['points'], 1); ?></td>
                                            </tr>
                                        <?php } ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-sm-12 col-lg-6 table-padding">
                    <div class="card">
                        <div class="card-header">
                            <h4>Top Player Weeks</h4>
                            <span id="weeks-filter-btns">
                                <a type="button">QB</a>
                                &nbsp;|&nbsp;
                                <a type="button">RB</a>
                                &nbsp;|&nbsp;
                                <a type="button">WR</a>
                                &nbsp;|&nbsp;
                                <a type="button">TE</a>
                                &nbsp;|&nbsp;
                                <a type="button">DEF</a>
                                &nbsp;|&nbsp;
                                <a type="button">K</a>
                            </span>
                            &nbsp;|&nbsp;
                            <a type="button" id="weeks-show-all">Show All</a>
                        </div>
                        <div class="card-body" style="direction: ltr;">
                            <div class="row">
                                <div class="col-sm-12">
                                    <table class="table table-striped nowrap table-responsive" id="datatable-playerWeeks">
                                        <thead>
                                            <th>Year</th>
                                            <th>Week</th>
                                            <th>Manager</th>
                                            <th></th>
                                            <th>Player</th>
                                            <th>Team</th>
                                            <th>Position</th>
                                            <th>Points</th>
                                        </thead>
                                        <tbody>
                                        <?php
                                        $result = query(
                                            "SELECT player, team, position, week, year, sum(points) as points, max(manager) as man
                                            FROM rosters
                                            GROUP BY player, team, year, week
                                            ORDER BY points DESC
                                            LIMIT 200");
                                        while ($row = fetch_array($result)) { ?>
                                            <tr>
                                                <td><?php echo $row['year']; ?></td>
                                                <td><?php echo $row['week']; ?></td>
                                                <td><?php echo '<a href="/profile.php?id='.$row['man'].'">'.$row['man'].'</a>'; ?></td>
                                                <td>
                                                    <?php echo '<a href="/rosters.php?year='.$row['year'].'&week='.$row['week'].'&manager='.$row['man'].'">
                                                    <i class="icon-clipboard"></i></a>'; ?>
                                                </td>
                                                <td><?php echo '<a href="/players.php?player='.$row['player'].'">'.$row['player'].'</a>'; ?></td>
                                                <td><?php echo $row['team']; ?></td>
                                                <td><?php echo $row['position']; ?></td>
                                                <td><?php echo round($row['points'], 1); ?></td>
                                            </tr>
                                        <?php } ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- New Card: Players Grouped by Manager -->
            <div class="row">
                <div class="col-sm-12 table-padding">
                    <div class="card">
                        <div class="card-header">
                            <h4>Players by Manager</h4>
                        </div>
                        <div class="card-body" style="direction: ltr;">
                            <div class="row">
                                <div class="col-sm-12">
                                    <table class="table table-striped nowrap" id="datatable-players-by-manager">
                                        <thead>
                                            <th>Player</th>
                                            <th>Position</th>
                                            <th>Managers</th>
                                            <th>Seasons</th>
                                            <th>Weeks</th>
                                            <th>Last Season</th>
                                        </thead>
                                        <tbody>
                                        <?php
                                        // Get all managers
                                        $managers = [];
                                        $result = query("SELECT DISTINCT manager FROM rosters");
                                        while ($row = fetch_array($result)) {
                                            $managers[] = $row['manager'];
                                        }
                                        $managerCount = count($managers);

                                        // Get all players, their seasons, managers, last season, positions, and weeks

                                        // Build alias map: canonical name => [all aliases]
                                        $aliasMap = [];
                                        $aliasLookup = [];
                                        $aliasResult = query("SELECT player, alias_1, alias_2, alias_3 FROM player_aliases");
                                        while ($row = fetch_array($aliasResult)) {
                                            $names = array_filter([$row['player'], $row['alias_1'], $row['alias_2'], $row['alias_3']]);
                                            foreach ($names as $name) {
                                                $aliasLookup[$name] = $row['player']; // map every alias to canonical
                                            }
                                            $aliasMap[$row['player']] = $names;
                                        }

                                        $players = [];
                                        $result = query("SELECT player, year, manager, position, week FROM rosters WHERE player != '(Empty)'");
                                        while ($row = fetch_array($result)) {
                                            $p = $row['player'];
                                            $canonical = isset($aliasLookup[$p]) ? $aliasLookup[$p] : $p;
                                            $y = $row['year'];
                                            $m = $row['manager'];
                                            $pos = $row['position'];
                                            $w = $row['week'];
                                            if (!isset($players[$canonical])) {
                                                $players[$canonical] = ['seasons' => [], 'managers' => [], 'positions' => [], 'last_season' => $y, 'weeks' => []];
                                            }
                                            if (!in_array($y, $players[$canonical]['seasons'])) {
                                                $players[$canonical]['seasons'][] = $y;
                                            }
                                            if (!in_array($m, $players[$canonical]['managers'])) {
                                                $players[$canonical]['managers'][] = $m;
                                            }
                                            if (!isset($players[$canonical]['positions'][$pos])) {
                                                $players[$canonical]['positions'][$pos] = 0;
                                            }
                                            $players[$canonical]['positions'][$pos]++;
                                            if ($y > $players[$canonical]['last_season']) {
                                                $players[$canonical]['last_season'] = $y;
                                            }
                                            // Track unique weeks (by year+week)
                                            $weekKey = $y.'-'.$w;
                                            if (!in_array($weekKey, $players[$canonical]['weeks'])) {
                                                $players[$canonical]['weeks'][] = $weekKey;
                                            }
                                        }

                                        // Sort: players owned by all managers at the top
                                        uasort($players, function($a, $b) use ($managerCount) {
                                            $aAll = count($a['managers']) === $managerCount ? 1 : 0;
                                            $bAll = count($b['managers']) === $managerCount ? 1 : 0;
                                            if ($aAll !== $bAll) return $bAll - $aAll;
                                            return count($b['managers']) - count($a['managers']);
                                        });

                                        foreach ($players as $player => $info) {
                                            // Most common position
                                            $pos = '';
                                            if (!empty($info['positions'])) {
                                                arsort($info['positions']);
                                                $pos = array_key_first($info['positions']);
                                            }
                                            echo '<tr>';
                                            echo '<td><a href="/players.php?player='.$player.'">'.$player.'</a></td>';
                                            echo '<td>'.$pos.'</td>';
                                            echo '<td>'.count($info['managers']).'</td>';
                                            echo '<td>'.count($info['seasons']).'</td>';
                                            echo '<td>'.count($info['weeks']).'</td>';
                                            echo '<td>'.$info['last_season'].'</td>';
                                            echo '</tr>';
                                        }
                                        ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    input[type="text"] {
        width: 100%
    }
</style>

<?php include 'footer.php'; ?>

<script type="text/javascript">
    $(document).ready(function() {

        $('#datatable-players-by-manager').DataTable({
            pageLength: 25,
            order: [[2, "desc"]]
        });

        let playerFilter = <?php echo isset($_GET['player']) ? "'".$_GET['player']."'" : 'null'; ?>;

        function format ( rowData ) {
            var div = $('<div/>')
                .addClass( 'loading' )
                .text( 'Loading...' );

            $.ajax( {
                url: '/dataLookup.php',
                data: {
                    dataType: 'player-info',
                    player: rowData.player,
                    year: rowData.year
                },
                dataType: 'json',
                success: function (data) {
                    let count = 1;
                    const table = document.createElement("table");
                    const thead = document.createElement("thead");
                    const tbody = document.createElement("tbody");
                    for (const row of data) {
                        if (count == 1) {
                            for (const key of Object.keys(row)) {
                                const th = document.createElement("th");
                                th.textContent = key.charAt(0).toUpperCase() + key.slice(1);
                                thead.appendChild(th);
                            }
                            table.appendChild(thead);
                        }
                        const tr = document.createElement("tr");
                        for (const key of Object.keys(row)) {
                            const td = document.createElement("td");
                            td.textContent = row[key];
                            tr.appendChild(td);
                        }
                        tbody.appendChild(tr);
                        count++;
                    }
                    table.appendChild(tbody);
                    div.removeClass('loading');
                    div.text('');
                    div.append(table);
                    // Removed DataTable initialization for child table to avoid column count warning
                }
            } );

            return div; 
        }

        var table = $('#datatable-players').DataTable({
            pageLength: 10,
            ajax: {
                url: 'dataLookup.php',
                data: {
                    dataType: 'all-players'
                }
            },
            columns: [
                {
                    className: 'dt-control',
                    orderable: false,
                    data: null,
                    defaultContent: '<i class="icon-plus"></i>'
                },
                { data: "player" },
                { data: "points" }
            ],
            order: [
                [2, "desc"]
            ]
        });

        if (playerFilter) {
            table.search(playerFilter).draw();
        }

        // Add event listener for opening and closing details
        table.on('click', 'td.dt-control', function (e) {
            let tr = e.target.closest('tr');
            let row = table.row(tr);
        
            if (row.child.isShown()) {
                // This row is already open - close it
                row.child.hide();
            }
            else {
                // Open this row
                row.child(format(row.data())).show();
            }
        });

        var playerSeasons = $('#datatable-playerSeasons').DataTable({
            pageLength: 10,
            order: [
                [6, "desc"]
            ]
        });
        
        var playerWeeks = $('#datatable-playerWeeks').DataTable({
            pageLength: 10,
            order: [
                [7, "desc"]
            ]
        });

        $('#weeks-filter-btns a').click(function () {
            let criteria = $(this)[0].outerText;
            playerWeeks.columns([6]).search(criteria, true, true).draw();
        });

        $('#weeks-show-all').click(function () {
            playerWeeks.columns('').search('').draw();
        });

        $('#seasons-filter-btns a').click(function () {
            let criteria = $(this)[0].outerText;
            playerSeasons.columns([5]).search(criteria, true, true).draw();
        });

        $('#seasons-show-all').click(function () {
            playerSeasons.columns('').search('').draw();
        });

    });
</script>