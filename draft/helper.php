<?php
    $pageName = "Draft Helper";
    include 'header.php';

    $currentYear = 2021;
    $draftOrder = [
        'Ben'       => 'ben-min.jpg',
        'Cole'      => 'cole-min.jpg',
        'Gavin'     => 'gavin-min.jpg',
        'Cameron'   => 'cam-min.jpg',
        'Justin'    => 'justin-min.jpg',
        'Tyler'     => 'tyler-min.jpg',
        'Matt'      => 'matt-min.jpg',
        'Everett'   => 'everett-min.jpg',
        'AJ'        => 'aj-min.jpg',
        'Andy'      => 'andy-min.jpg',
    ];

    ob_start();
    $result = mysqli_query($conn, "SELECT count(id) as picks FROM draft_selections");
    while ($row = mysqli_fetch_array($result)) {
        $currentPick = $row['picks'] + 1;
    }

    $pickers = [];
    $allDraftOrder = array_reverse(array_keys($draftOrder));
    for ($x = 0; $x < 30; $x++) {
        $allDraftOrder = array_reverse($allDraftOrder);
        foreach ($allDraftOrder as $man) {
            $pickers[] = $man;
        }
    }

    $myKey = array_search('Tyler', array_keys($draftOrder));
    $oddRounds = $myKey + 1;
    $evenRounds = 10 - $myKey;
    $allMyPicks = $allMyNextPicks = [];
    for ($x = 0; $x < 30; $x++) {
        if ($x % 2 == 0) {
            // Even round
            $allMyPicks[] = $x * 10 + $oddRounds;
            $allMyNextPicks[] = $x * 10 + $oddRounds - $currentPick;
        } else {
            $allMyPicks[] = $x * 10 + $evenRounds;
            $allMyNextPicks[] = $x * 10 + $evenRounds - $currentPick;
        }
    }

    foreach ($allMyPicks as $pick) {
        if ($pick >= $currentPick) {
            $myNextPick = $pick - $currentPick;

            if ($myNextPick == 0) {
                $myNextPick = 'Now!';
            }

            break;
        }
        if ($pick < $currentPick) {
            unset($allMyNextPicks[0]);
            $allMyNextPicks = array_values($allMyNextPicks);
        }
    }

    // Determine tendency/need for each manager
    foreach ($draftOrder as $man => $avatar) {
        for ($x = 1; $x < 23; $x++) {
            $tendency[$man][$x]['picks'] = [
                'QB' => 0, 'RB' => 0, 'WR' => 0, 'TE' => 0, 'DEF' => 0, 'LB' => 0
            ];
            $tendency[$man][$x]['want_pct'] = [
                'QB' => 0, 'RB' => 0, 'WR' => 0, 'TE' => 0, 'DEF' => 0, 'LB' => 0
            ];
            $tendency[$man][$x]['need'] = [
                'QB' => 4, 'RB' => 5, 'WR' => 3, 'TE' => 2, 'DEF' => 1
            ];
        }
    }
    $result = mysqli_query($conn, "SELECT * FROM draft
        JOIN managers m ON m.id = manager_id
        WHERE YEAR > ($currentYear - 6)
        AND ROUND < 8
        ORDER BY YEAR DESC, ROUND asc");
    while ($row = mysqli_fetch_array($result)) {
        $tendency[$row['name']][$row['round']]['picks'][$row['position']]++;
    }

    foreach ($tendency as $name => $round) {
        foreach ($round as $rd => $stuff) {
            foreach ($stuff['picks'] as $pos => $val) {
                // Divide by 5 because looking at last 5 years of data
                // Multiply by 80% because needs are worth more than wants (tendencies)
                $tendency[$name][$rd]['want_pct'][$pos] = ($val / 5)*.8;
            }
        }
    }

    $currentRound = ($currentPick % 10 == 0) ? floor($currentPick/10) : floor($currentPick/10) + 1;
    $result = mysqli_query($conn, "SELECT name, position FROM draft_selections
        JOIN managers m ON m.id = manager_id
        JOIN preseason_rankings pr ON pr.id = ranking_id");
    while ($row = mysqli_fetch_array($result)) {
        $tendency[$row['name']][$currentRound]['need'][$row['position']]--;
    }

    foreach ($tendency as $name => $round) {
        foreach ($round as $rd => $stuff) {
            foreach ($stuff['need'] as $pos => $val) {
                // Number 11 is based on weighted number of positions
                $tendency[$name][$rd]['need_pct'][$pos] = $val < 0 ? 0 : ($val / 11);
            }
        }
    }
// var_dump($tendency['Justin'][$currentRound]);die;

    ob_flush();
?>

<body data-open="click" data-menu="vertical-menu" data-col="2-columns" class="vertical-layout vertical-menu 2-columns fixed-navbar">

    <!-- navbar-fixed-top-->
    <nav class="header-navbar navbar navbar-with-menu navbar-fixed-top navbar-semi-dark navbar-shadow">
        <div class="navbar-wrapper">
            <div class="navbar-header">
                <ul class="nav navbar-nav">
                    <li class="nav-item mobile-menu hidden-md-up float-xs-left"><a class="nav-link nav-menu-main menu-toggle hidden-xs"><i class="icon-menu5 font-large-1"></i></a></li>
                    <li class="nav-item">
                        <h2>Suntown FFB</h2>
                    </li>
                    <li class="nav-item hidden-md-up float-xs-right"><a data-toggle="collapse" data-target="#navbar-mobile" class="nav-link open-navbar-container"><i class="icon-ellipsis pe-2x icon-icon-rotate-right-right"></i></a></li>
                </ul>
            </div>
            <div class="navbar-container content container-fluid">
                <div id="navbar-mobile">
                    <h2>&nbsp;<?php echo $currentYear; ?> Draft Helper &nbsp;</h2>
                </div>
            </div>
        </div>
    </nav>

    <div class="app-content container-fluid">
        <div class="content-wrapper">
            <div class="content-body">
                <div class="row" id="pick-avatars">
                    <div class="col-xs-12">
                        <?php
                        ob_start();
                        $limit = 0;
                        foreach ($pickers as $index => $picker) {
                            if ($index+1 >= $currentPick && $limit < 15) {
                                $pk = $index+1;
                                $currentRd = ($pk % 10 == 0) ? floor($pk/10) : floor($pk/10) + 1;
                                echo '<div class="avatars">';
                                $img = $draftOrder[$picker];
                                echo '<img src="/images/'.$img.'" width="100px" height="100px">';
                                $limit++;

                                $likelihood = [];
                                if (isset($tendency[$picker][$currentRd]['want_pct'])) {
                                    $needs = $tendency[$picker][$currentRd]['need_pct'];
                                    $wants = $tendency[$picker][$currentRd]['want_pct'];
                                    arsort($needs);
                                    arsort($wants);

                                    foreach ($needs as $pos => $nd) {
                                        foreach ($wants as $pos2 => $wt) {
                                            if ($pos == $pos2) {
                                                $likelihood[$pos] = round(($nd + $wt) * 50, 0);
                                            }
                                        }
                                    }
                                }
                                if (isset($likelihood['QB'])) {
                                    echo '<br><div style="display: inline-flex">';
                                    echo '<div class="meter"><span class="color-QB" style="height: '.$likelihood['QB'].'%">'.$likelihood['QB'].'</span></div>';
                                    echo '<div class="meter"><span class="color-RB" style="height: '.$likelihood['RB'].'%">'.$likelihood['RB'].'</span></div>';
                                    echo '<div class="meter"><span class="color-WR" style="height: '.$likelihood['WR'].'%">'.$likelihood['WR'].'</span></div>';
                                    echo '<div class="meter"><span class="color-TE" style="height: '.$likelihood['TE'].'%">'.$likelihood['TE'].'</span></div>';
                                    echo '</div>';
                                } else {
                                    echo '<br><div style="display: inline-flex">';
                                    echo '<div class="meter"><span class="color-QB" style="height: 0%"></span></div>';
                                    echo '</div>';
                                }

                                echo '</div>';
                            }
                        }
                        ob_flush();
                        ?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-xs-12 col-md-9">
                        <div class="card">
                            <div class="card-body">
                                <div class="position-relative">
                                    <div class="card-header">
                                        <h3>
                                            Current Pick: <?php echo $pickers[$currentPick-1].' ('.$currentPick.')'; ?>
                                            &nbsp;|&nbsp;
                                            My Next Pick: <?php echo $myNextPick; ?>
                                        </h3>
                                        <a data-toggle="modal" data-target="#draft-board" href="#">Draft Board</a>
                                        &nbsp;|&nbsp;
                                        <a data-toggle="modal" data-target="#cheat-sheet" href="#">Cheat Sheet</a>
                                        &nbsp;|&nbsp;
                                        <a data-toggle="modal" data-target="#proj-standings" id="show-standings" href="#">Standings</a>
                                        &nbsp;|&nbsp;
                                        <a data-toggle="modal" data-target="#depth-chart" href="#">Depth Chart</a>
                                        &nbsp;|&nbsp;
                                        <a href="players.php" target="_blank">Players</a>
                                        &nbsp;|&nbsp;
                                        <a id="hide-te">Hide TE</a>
                                        &nbsp;|&nbsp;
                                        <a id="hide-def">Hide DEF</a>
                                        &nbsp;|&nbsp;
                                        <a id="hide-k">Hide K</a>
                                        &nbsp;|&nbsp;
                                        <a id="undoPick">Undo Pick</a>
                                        &nbsp;|&nbsp;
                                        <a id="restartDraft">Restart Draft</a>
                                    </div>
                                    <table class="table table-responsive" id="datatable-players" width="100%">
                                        <thead>
                                            <th>My Rank</th>
                                            <th>ADP</th>
                                            <th>Player</th>
                                            <th>Team</th>
                                            <th>Bye</th>
                                            <th>SoS</th>
                                            <th>Line</th>
                                            <th>Tier</th>
                                            <th></th>
                                            <th>GP</th>
                                            <th>Pts</th>
                                            <th>Pts/Gm</th>
                                            <th>Yds</th>
                                            <th>TDs</th>
                                            <th>Rec</th>
                                            <th>Proj Pts</th>
                                            <th></th>
                                            <th>Pos</th>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $count = 0;
                                            $result = mysqli_query($conn,
                                                "SELECT * FROM preseason_rankings pr
                                                LEFT JOIN draft_selections ON pr.id = draft_selections.ranking_id
                                                LEFT JOIN player_data pd ON pd.preseason_ranking_id = pr.id AND pd.type = 'REG' AND pd.year = ($currentYear-1)
                                                WHERE ranking_id IS NULL
                                                ORDER BY my_rank ASC"
                                            );
                                            while ($row = mysqli_fetch_array($result)) {

                                                $sosColor = ($row['sos'] > 25) ? 'bad' : ($row['sos'] < 7 ? 'good' : '');
                                                $lineColor = ($row['position'] != 'DEF') ? (($row['line'] > 25) ? 'bad' : ($row['line'] < 7 ? 'good' : '')) : '';

                                                $count++;
                                                if (in_array($count, $allMyNextPicks)) {
                                                    $myRank = $row['my_rank']+2;
                                                    echo '<tr class="color-black"><td>'.$myRank.'</td><td></td><td></td><td></td><td></td><td></td><td></td>
                                                    <td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>';
                                                }
                                            ?>
                                                <tr class="color-<?php echo $row['position']; ?>">
                                                    <td><?php echo $row['my_rank']; ?></td>
                                                    <td><?php echo $row['adp']; ?></td>
                                                    <td><?php echo '<a data-toggle="modal" data-target="#player-data" onclick="showPlayerData('.(int)$row[0].')">'.$row['player'].'</a>'; ?></td>
                                                    <td><?php echo $row['team']; ?></td>
                                                    <td><?php echo $row['bye']; ?></td>
                                                    <td class="color-<?php echo $sosColor; ?>"><?php echo $row['sos']; ?></td>
                                                    <td class="color-<?php echo $lineColor; ?>"><?php echo ($row['position'] != 'DEF') ? $row['line'] : ''; ?></td>
                                                    <td><?php echo $row['tier']; ?></td>
                                                    <td><a class="btn btn-secondary taken">Taken</a><a class="btn btn-secondary mine">Mine!</a></td>
                                                    <td><?php echo $row['games_played']; ?></td>
                                                    <td><?php echo $row['points']; ?></td>
                                                    <td><?php echo $row['games_played'] > 0 ? round($row['points'] / $row['games_played'], 1) : null; ?></td>
                                                    <td><?php echo $row['pass_yards'] + $row['rush_yards'] + $row['rec_yards']; ?></td>
                                                    <td><?php echo $row['pass_touchdowns']+$row['rush_touchdowns']+$row['rec_touchdowns']; ?></td>
                                                    <td><?php echo $row['rec_receptions']; ?></td>
                                                    <td><?php echo $row['proj_points']; ?></td>
                                                    <td><?php echo desigIcon($row['designation'], $row['notes'] ? true : false); ?></td>
                                                    <td><?php echo $row['position']; ?></td>
                                                </tr>
                                            <?php } ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xs-12 col-md-3">
                        <div class="card">
                            <div class="card-body">
                                <div class="position-relative">
                                    <div class="card-header">
                                        <h3>My Team</h3>
                                    </div>

                                    <table class="table table-responsive" id="datatable-team">
                                        <thead>
                                            <th>Rd</th>
                                            <th>Pos</th>
                                            <th>Player</th>
                                            <th>Bye</th>
                                            <th>ADP</th>
                                            <th>Pick</th>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $myPlayers = [];
                                            $rd = 0;
                                            $result = mysqli_query(
                                                $conn,
                                                "SELECT * FROM preseason_rankings
                                                JOIN draft_selections ON preseason_rankings.id = draft_selections.ranking_id
                                                WHERE is_mine = 1 ORDER BY pick_number ASC"
                                            );
                                            while ($row = mysqli_fetch_array($result)) {
                                                $myPlayers[] = (int)$row['bye'];
                                                $rd++;
                                                ?>
                                                <tr class="color-<?php echo $row['position']; ?>">
                                                    <td><?php echo $rd; ?></td>
                                                    <td><?php echo $row['position']; ?></td>
                                                    <td><?php echo '<a data-toggle="modal" data-target="#player-data" onclick="showPlayerData('.(int)$row[0].')">'.$row['player'].'</a>' ?></td>
                                                    <td><?php echo $row['bye']; ?></td>
                                                    <td><?php echo $row['adp']; ?></td>
                                                    <td><?php echo $row['pick_number']; ?></td>
                                                </tr>
                                            <?php
                                            }
                                            if ($rd < 18) {
                                                for ($x = $rd+1; $x < 18; $x++) {
                                                    echo '<tr><td>'.$x.'</td><td></td><td></td><td></td><td></td><td></td>';
                                                }
                                            }
                                            $myPlayers = json_encode($myPlayers); ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="postData">

                </div>
            </div>
        </div>
    </div>

    <?php include 'modals.php'; ?>

<script type="text/javascript">

    var hasScrolled = false;
    $(window).scroll(function(){

        if (!hasScrolled) {
            getPostData();
            hasScrolled = true;
        }
    });

    function getPostData() {
        $.ajax({
            url : 'fetchMore.php',
            method: 'POST',
            dataType: 'text',
            data: {
                currentYear: "<?php echo $currentYear; ?>",
                draftOrder: '<?php echo json_encode($draftOrder); ?>'
            },
            cache: false,
            success: function(response) {
                $("#postData").append(response);
                moreDataJs();
            }
        });
    }

    let myPlayers = "<?php echo $myPlayers; ?>";
    myPlayers = JSON.parse(myPlayers);

    var playersTable = $('#datatable-players').DataTable({
        "autoWidth": true,
        "pageLength": 20,
        "order": [
            [0, "asc"]
        ]
    });

    playersTable.columns(17).visible(false);
    playersTable.columns.adjust().draw();

    var teamTable = $('#datatable-team').DataTable({
        "searching": false,
        "paging": false,
        "info": false,
        "sort": false
    });

    $(document).ready(function() {

        var standingsTable = $('#datatable-standings').DataTable({
            "searching": false,
            "paging": false,
            "info": false,
            "order": [
                [7, "desc"]
            ]
        });
        var depthTable = $('#datatable-depth').DataTable({
            "searching": false,
            "paging": false,
            "info": false,
            "autoWidth": false,
            "order": [
                [0, "asc"]
            ]
        });

        $('#pick-avatars').removeClass('hidden');

        let currentPick = "<?php echo $currentPick-1; ?>";
        if (currentPick % 10 == 0 && currentPick != 0) {
            $('#proj-standings').modal('show');
        }

        $('#datatable-players').on('click', 'tbody .taken', function () {
            var data_row = playersTable.row($(this).closest('tr')).data();
            pickManager = "<?php echo $pickers[$currentPick-1]; ?>";
            data_row.push(pickManager);
            data_row.push('taken');
            console.log(data_row);
            var formData = {data: data_row};

            saveSelection(formData);
        });

        $('#datatable-players').on('click', 'tbody .mine', function () {
            var data_row = playersTable.row($(this).closest('tr')).data();
            pickManager = "<?php echo $pickers[$currentPick-1]; ?>";
            data_row.push(pickManager);
            data_row.push('mine');
            console.log(data_row);
            var formData = {data: data_row};

            let good = checkByes(data_row);
            if (!good) {
                if (confirm('Check the byes, bro.')) {
                    saveSelection(formData);
                }
            } else {
                saveSelection(formData);
            }
        });

        $('#hide-te').click(function () {
            doSearch('TE');
        });

        $('#hide-def').click(function () {
            doSearch('DEF');
        });

        $('#hide-k').click(function () {
            doSearch('K');
        });

        var posArray = [];
        function doSearch(pos) {
            posArray.push(pos);
            let regex = '^(';

            posArray.forEach(function (item) {
                regex += '(?!'+item+')';
            });
            regex += '.)*$';

            playersTable.columns([17]).search(regex, true, false).draw();
        }

        $('#undoPick').click(function () {
            if (confirm('Are you sure?')) {
                $.ajax({
                    url: 'updateSelected.php',
                    type: "POST",
                    data: {request: 'undo'},
                    async: false,
                    dataType: 'json',
                    success: function (response) {
                        if (response.type == 'error') {
                            alert('error');
                        } else {
                            location.reload();
                        }
                    }
                });
            }
        });

        $('#restartDraft').click(function () {
            if (confirm('Are you sure?')) {
                $.ajax({
                    url: 'updateSelected.php',
                    type: "POST",
                    data: {request: 'restart'},
                    async: false,
                    dataType: 'json',
                    success: function (response) {
                        if (response.type == 'error') {
                            alert('error');
                        } else {
                            location.reload();
                        }
                    }
                });
            }
        });
    });

    function checkByes(data)
    {
        let players = myPlayers.length;
        if (players) {
            let count = myPlayers.filter(x => x == data[4]).length;
            if (count/players > .2) {
                return false;
            }
        }

        return true;
    }

    function saveSelection(formData)
    {
        $.ajax({
            url: 'updateSelected.php',
            type: "POST",
            data: formData,
            async: false,
            dataType: 'json',
            success: function (response) {
                if (response.type == 'error') {
                    alert('error');
                } else {
                    location.reload();
                }
            }
        });
    }

    function moreDataJs()
    {
        var top5Table = $('#datatable-top5').DataTable({
            "searching": false,
            "paging": false,
            "info": false,
            "sort": false
        });
        var turnTable = $('#datatable-turn').DataTable({
            "searching": false,
            "paging": false,
            "info": false,
            "sort": false
        });
        var rollingTable = $('#datatable-rolling-list').DataTable({
            "searching": false,
            "paging": false,
            "info": false,
            "order": [
                [0, "desc"]
            ]
        });

        $('#new-player-btn').click(function () {
            $.ajax({
                url: 'updateSelected.php',
                type: "POST",
                data: {newname: $('#new-player').val()},
                async: false,
                dataType: 'json',
                success: function (response) {
                    if (response.type == 'error') {
                        alert('error');
                    } else {
                        location.reload();
                    }
                }
            });
        });

        $('#datatable-top5').on('click', 'tbody .taken', function () {
            var data_row = top5Table.row($(this).closest('tr')).data();
            pickManager = "<?php echo $pickers[$currentPick-1]; ?>";
            data_row.push(pickManager);
            data_row.push('taken');
            console.log(data_row);
            var formData = {data: data_row};

            saveSelection(formData);
        });

        $('#datatable-top5').on('click', 'tbody .mine', function () {
            var data_row = top5Table.row($(this).closest('tr')).data();
            pickManager = "<?php echo $pickers[$currentPick-1]; ?>";
            data_row.push(pickManager);
            data_row.push('mine');
            console.log(data_row);
            var formData = {data: data_row};

            let good = checkByes(data_row);
            if (!good) {
                if (confirm('Check the byes, bro.')) {
                    saveSelection(formData);
                }
            } else {
                saveSelection(formData);
            }
        });
    }

    function showPlayerData(id)
    {
        $.ajax({
            type : 'post',
            url : 'updateSelected.php',
            data :  {
                request: 'player_data',
                id: id
            },
            success : function(data){
                let table = '<table id="player-history"><thead>';
                table += '<th>Year</th><th>Team</th><th>GP</th>';
                table += '<th>Pass Att</th><th>Comp</th><th>Pass Yds</th><th>Pass TDs</th><th>Int</th>';
                table += '<th>Rush Att</th><th>Rush Yds</th><th>Rush TDs</th>';
                table += '<th>Tar</th><th>Rec</th><th>Rec Yds</th><th>Rec TDs</th><th>Fumbles</th>';
                table += '<th>Pts</th><th>Pts/Gm</th>';
                table += '</thead><tbody>';

                data = JSON.parse(data);
                // Loop through data to add rows
                data.forEach(function (item, index) {

                    if (index == 0) {
                        let header = '<h4>'+item.player+' ('+item.position + ' | ' + item.team+')</h4>';
                        $('#player-header').html(header);
                        $('#player-notes').val(item.notes);
                        $('#player-id').val(item.id);
                    } else {

                        let points = (item.pass_yards*.04)+(item.pass_touchdowns*4)+(item.rush_yards*.1)+(item.pass_touchdowns*6);
                        points += (item.rec_yards*.1)+(item.rec_touchdowns*6)+(item.rec_receptions*.5);
                        points -= (item.pass_interceptions*2)+(item.fumbles*3);
                        let ppg = (points/item.games_played).toFixed(1);
                        table += '<tr>'+
                            '<td>'+item.year+'</td>'+
                            '<td>'+item.team_abbr+'</td>'+
                            '<td>'+item.games_played+'</td>'+
                            '<td>'+item.pass_attempts+'</td>'+
                            '<td>'+item.pass_completions+'</td>'+
                            '<td>'+item.pass_yards+'</td>'+
                            '<td>'+item.pass_touchdowns+'</td>'+
                            '<td>'+item.pass_interceptions+'</td>'+
                            '<td>'+item.rush_attempts+'</td>'+
                            '<td>'+item.rush_yards+'</td>'+
                            '<td>'+item.rush_touchdowns+'</td>'+
                            '<td>'+item.rec_targets+'</td>'+
                            '<td>'+item.rec_receptions+'</td>'+
                            '<td>'+item.rec_yards+'</td>'+
                            '<td>'+item.rec_touchdowns+'</td>'+
                            '<td>'+item.fumbles+'</td>'+
                            '<td>'+points.toFixed(0)+'</td>'+
                            '<td>'+ppg+'</td>';
                    }
                });

                table += '</tbody></table>';
                $('#fetched-data').html(table);
            }
        });
    }

    $('#save-note').click(function() {
        $.ajax({
            type : 'post',
            url : 'updateSelected.php',
            data :  {
                request: 'notes',
                id: $('#player-id').val(),
                notes: $('#player-notes').val()
            },
            success : function(data){
                $('#confirm').html('Saved!');
                location.reload();
            }
        });
    });
</script>

<style>
    .app-content.container-fluid {
        background: #3E3D3E;
        direction: ltr;
    }

    .card, th, table.dataTable {
        background: #3E3D3E;
        color: #fff;
    }

    td, .card-header {
        color: #3E3D3E;
    }

    a.btn.btn-secondary {
        padding: .2rem 1rem;
    }

    a, a:link, a:visited {
        color: black;
    }

    a:hover, .card-body label, .dataTables_info {
        color: white !important;
    }

    .strike {
        text-decoration: line-through;
        color: gray;
    }

    .taken {
        background-color: #fa887f;
    }

    .mine {
        background-color: #8cfa84;
    }

    #datatable-board td {
        padding: 15px;
        font-weight: bold;
    }

    #datatable-board .sub {
        font-weight: 400;
        font-size: 16px;
    }

    .good-pick {
        color: green;
    }

    .bad-pick {
        color: red;
    }

    table#player-history td, th {
        padding: 10px 15px;
    }

    table.dataTable tbody th, table.dataTable tbody td {
        padding: 2px 10px;
    }

    div#pick-avatars {
        text-align: center;
    }

    .hidden {
        display: none;
    }

    .avatars {
        display: inline-block;
    }

    span.subtext {
        font-size: 10px;
    }

    .meter {
        height: 40px;
        width: 20px;
        background: #3E3D3E;
        color: #fff;
    }

    .meter span {
        display: block;
        font-size: 11px;
    }

    .modal-content {
        background-color: #3E3D3E;
        color: #fff;
    }
</style>