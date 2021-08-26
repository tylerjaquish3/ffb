<?php
    $pageName = "Draft Helper";
    include 'header.php';

    // Ideas for next year
    // Ability to view each team by roster spot
    // Make it easier to add initial set of players, SoS, line, proj points
    // Make it easier to update projections based on FFB UDK
    // Fix sportradar to just update each player (and add rookies stats)

    // For a new year, just update these few items
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
    $allPositions = ['QB','RB','RB','WR','WR','WR','TE','W/R/T','Q/W/R/T','K','DEF','BN','BN','BN','BN','BN','BN'];
    // *********************************************

    $result = mysqli_query($conn, "SELECT count(id) as picks FROM draft_selections");
    while ($row = mysqli_fetch_array($result)) {
        $currentPick = $row['picks'] + 1;
    }

    $pickers = [];
    $allDraftOrder = array_reverse(array_keys($draftOrder));
    for ($x = 0; $x < count($allPositions); $x++) {
        $allDraftOrder = array_reverse($allDraftOrder);
        foreach ($allDraftOrder as $man) {
            $pickers[] = $man;
        }
    }

    $myKey = array_search('Tyler', array_keys($draftOrder));
    $oddRounds = $myKey + 1;
    $evenRounds = 10 - $myKey;
    $allMyPicks = $allMyNextPicks = [];
    for ($x = 0; $x < count($allPositions)+1; $x++) {
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
        for ($x = 1; $x < count($allPositions); $x++) {
            $tendency[$man][$x]['picks'] = [
                'QB' => 0, 'RB' => 0, 'WR' => 0, 'TE' => 0, 'K' => 0, 'DEF' => 0
            ];
            $tendency[$man][$x]['want_pct'] = [
                'QB' => 0, 'RB' => 0, 'WR' => 0, 'TE' => 0, 'K' => 0, 'DEF' => 0
            ];
            $tendency[$man][$x]['need'] = [
                'QB' => 4, 'RB' => 5, 'WR' => 3, 'TE' => 2, 'K' => 0, 'DEF' => 1
            ];
        }
    }
    $result = mysqli_query($conn, "SELECT * FROM draft
        JOIN managers m ON m.id = manager_id
        WHERE YEAR > ($currentYear - 6)
        AND ROUND < ".count($allPositions)."
        ORDER BY YEAR DESC, ROUND asc");
    while ($row = mysqli_fetch_array($result)) {
        if (isset($tendency[$row['name']][$row['round']]['picks'][$row['position']])) {
            $tendency[$row['name']][$row['round']]['picks'][$row['position']]++;
        }
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
        if (isset($tendency[$row['name']][$currentRound]['need'][$row['position']]))
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

?>

<body">

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
                                    echo '<div class="meter"><span class="color-QB" style="height: '.$likelihood['QB'].'%"></span><span class="val">'.$likelihood['QB'].'</span></div>';
                                    echo '<div class="meter"><span class="color-RB" style="height: '.$likelihood['RB'].'%"></span><span class="val">'.$likelihood['RB'].'</span></div>';
                                    echo '<div class="meter"><span class="color-WR" style="height: '.$likelihood['WR'].'%"></span><span class="val">'.$likelihood['WR'].'</span></div>';
                                    echo '<div class="meter"><span class="color-TE" style="height: '.$likelihood['TE'].'%"></span><span class="val">'.$likelihood['TE'].'</span></div>';
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
// var_dump($picker);
// var_dump($currentRd);
//                         var_dump($tendency[$picker][$currentRd]);
                        ?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-xs-12 col-md-10">
                        <div class="card">
                            <div class="card-body">
                                <div class="position-relative">
                                    <div class="card-header">
                                        <h3>
                                        <?php
                                        if (isset($pickers[$currentPick-1])) {
                                            echo 'Current Pick: '.$pickers[$currentPick-1].' ('.$currentPick.')';
                                            echo '&nbsp;|&nbsp';
                                            echo 'My Next Pick: '.$myNextPick;
                                        }
                                        ?>
                                        </h3>
                                        <a data-toggle="modal" data-target="#draft-board" href="#">Draft Board</a>
                                        &nbsp;|&nbsp;
                                        <a data-toggle="modal" data-target="#cheat-sheet" href="#">Cheat Sheet</a>
                                        &nbsp;|&nbsp;
                                        <a data-toggle="modal" data-target="#proj-standings" id="show-standings" href="#">Standings</a>
                                        &nbsp;|&nbsp;
                                        <a data-toggle="modal" data-target="#depth-chart" href="#">Depth Chart</a>
                                        &nbsp;|&nbsp;
                                        <a data-toggle="modal" data-target="#positions" href="#">Positions</a>
                                        &nbsp;|&nbsp;
                                        <a data-toggle="modal" data-target="#defenses" href="#">Defenses</a>
                                        &nbsp;|&nbsp;
                                        <a href="players.php" target="_blank">Players</a>
                                        <!-- &nbsp;|&nbsp;
                                        <a id="hide-te">Hide TE</a>
                                        &nbsp;|&nbsp;
                                        <a id="hide-def">Hide DEF</a>
                                        &nbsp;|&nbsp;
                                        <a id="hide-k">Hide K</a> -->
                                        &nbsp;|&nbsp;
                                        <a id="undoPick">Undo Pick</a>
                                        &nbsp;|&nbsp;
                                        <a id="restartDraft">Restart Draft</a>
                                        &nbsp;|&nbsp;
                                        <a id="scramble">Scramble</a>
                                    </div>
                                    <table class="table table-responsive" id="datatable-players" width="100%">
                                        <thead>
                                            <th>Rank</th>
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
                                            <th>Proj</th>
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
                                                $btnColor = ($myNextPick == 'Now!') ? 'mine' : 'taken';
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
                                                    <td><a class="btn btn-secondary selected-btn <?php echo $btnColor; ?>"><i class="icon-plus"></i></a></td>
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
                    <div class="col-xs-12 col-md-2">
                        <div class="card">
                            <div class="card-body">
                                <div class="position-relative">
                                    <div class="card-header">
                                        <h3>My Team&nbsp;&nbsp;<i class="icon-list" id="flip-team-view"></i></h3>
                                    </div>

                                    <table class="table table-responsive" id="datatable-teamByRound">
                                        <thead>
                                            <th>Rd</th>
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
                                                    <td><?php echo '<a data-toggle="modal" data-target="#player-data" onclick="showPlayerData('.(int)$row[0].')">'.$row['player'].'</a>' ?></td>
                                                    <td><?php echo $row['bye']; ?></td>
                                                    <td><?php echo $row['adp']; ?></td>
                                                    <td><?php echo $row['pick_number']; ?></td>
                                                </tr>
                                            <?php
                                            }
                                            if ($rd < count($allPositions)+1) {
                                                for ($x = $rd+1; $x < count($allPositions)+1; $x++) {
                                                    echo '<tr><td>'.$x.'</td><td></td><td></td><td></td><td></td>';
                                                }
                                            }
                                            $myPlayers = json_encode($myPlayers); ?>
                                        </tbody>
                                    </table>
                                    <table class="table table-responsive" id="datatable-teamByPosition" style="display: none;">
                                        <thead>
                                            <th>Pos</th>
                                            <th>Player</th>
                                            <th>Bye</th>
                                            <th>Pick</th>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $myRoster = [];
                                            $wrt = ['RB','WR','TE'];
                                            $qwrt = ['QB','RB','WR','TE'];
                                            foreach ($allPositions as $pos) {
                                                $myRoster[] = [$pos => null];
                                            }
                                            $result = mysqli_query($conn,
                                                "SELECT * FROM preseason_rankings
                                                JOIN draft_selections ON preseason_rankings.id = draft_selections.ranking_id
                                                WHERE is_mine = 1 ORDER BY pick_number ASC"
                                            );
                                            while ($row = mysqli_fetch_array($result)) {
                                                foreach ($myRoster as $key => &$rosterPos) {
                                                    foreach ($rosterPos as $k => &$pos) {
                                                        $filled = false;
                                                        if ($pos == null && $k == $row['position']) {
                                                            $myRoster[$key] = $row;
                                                            $filled = true;
                                                            break;
                                                        } elseif ($pos == null && $k == 'W/R/T' && in_array($row['position'], $wrt)) {
                                                            $myRoster[$key] = $row;
                                                            $filled = true;
                                                            break;
                                                        } elseif ($pos == null && $k == 'Q/W/R/T' && in_array($row['position'], $qwrt)) {
                                                            $myRoster[$key] = $row;
                                                            $filled = true;
                                                            break;
                                                        } elseif ($pos == null && $k == 'BN') {
                                                            $myRoster[$key] = $row;
                                                            $filled = true;
                                                            break;
                                                        }
                                                    }
                                                    if ($filled) {
                                                        break;
                                                    }
                                                }
                                            }
                                            $count = 0;
                                            foreach ($allPositions as $rosterSpot) {
                                                $row = $myRoster[$count];
                                                if ($row && isset($row['position'])) {
                                                ?>
                                                    <tr class="color-<?php echo $row['position']; ?>">
                                                        <td><?php echo $rosterSpot; ?></td>
                                                        <td><?php echo '<a data-toggle="modal" data-target="#player-data" onclick="showPlayerData('.(int)$row[0].')">'.$row['player'].'</a>' ?></td>
                                                        <td><?php echo $row['bye']; ?></td>
                                                        <td><?php echo $row['pick_number']; ?></td>
                                                    </tr>
                                                <?php
                                                } else { ?>
                                                    <tr>
                                                        <td><?php echo $rosterSpot; ?></td>
                                                        <td></td>
                                                        <td></td>
                                                        <td></td>
                                                    </tr>
                                                <?php
                                                }
                                                $count++;
                                            }?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="postData"></div>
            </div>
        </div>
    </div>

    <script src="/assets/chart.min.js" type="text/javascript"></script>

    <?php include 'modals.php'; ?>

<script type="text/javascript">

    var top5Table;
    var scrambled;
    var scrambledNames;
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
                draftOrder: '<?php echo json_encode($draftOrder); ?>',
                myNextPick: '<?php echo $myNextPick; ?>',
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
        "columnDefs": [
            { "width": "25px", "targets": 8 },
            { "sortable": false, "targets": [8,16,17]},
            { "visible": false, "targets": 17 }
        ],
        "pageLength": 20,
        "order": [
            [0, "asc"]
        ]
    });

    var teamTable = $('#datatable-teamByRound').DataTable({
        "searching": false,
        "paging": false,
        "info": false,
        "sort": false
    });
    var teamByPosTable = $('#datatable-teamByPosition').DataTable({
        "searching": false,
        "paging": false,
        "info": false,
        "sort": false
    });

    $(document).ready(function() {

        var showTeamTable = true;

        $('#flip-team-view').click(function () {
            if (showTeamTable) {
                $('#datatable-teamByRound').hide();
                $('#datatable-teamByPosition').show();
                showTeamTable = false;
            } else {
                $('#datatable-teamByPosition').hide();
                $('#datatable-teamByRound').show();
                showTeamTable = true;
            }
        });

        let currentPick = "<?php echo $currentPick-1; ?>";
        if (currentPick % 10 == 0 && currentPick != 0) {
            $('#proj-standings').modal('show');
        }

        $('#datatable-players').on('click', 'tbody .selected-btn', function () {
            var data_row = playersTable.row($(this).closest('tr')).data();
            pickManager = "<?php echo isset($pickers[$currentPick-1]) ? $pickers[$currentPick-1] : 10; ?>";
            data_row.push(pickManager);
            // console.log(data_row);
            var formData = {data: data_row};

            let mine = '<?php echo $myNextPick; ?>' == 'Now!';
            let good = checkByes(data_row);
            if (mine && !good) {
                if (confirm('Check the byes, bro.')) {
                    saveSelection(formData);
                }
            } else {
                saveSelection(formData);
            }
        });

        $('#scramble').click(function () {
            scrambled = true;
            // Sort by bye week desc
            playersTable.order([4, 'desc']).draw();
            // Hide rank and adp
            playersTable.column(0).visible(false);
            playersTable.column(1).visible(false);

            scrambledNames = ['Blue Adams', 'Brett Favre', 'Joe Namath', 'Fred Flintstone', 'Ken Griffey',
                'Tom Hanks', 'Rosie O\'Donnell', 'Peewee Herman', 'Warren Moon', 'Jason Statham',
                'Chuck Norris', 'Pete Rose', 'Joe Montana', 'Jerry Rice', 'Jerry Springer', 'Natalie Portman',
                'Adam Sandler', 'Larry Bird', 'Bo Jackson', 'Tweety Bird', 'Vinny Testaverde', 'Wayne Gretsky',
                'Ben Bardell', 'Andy\'s Mom', 'Miley Cyrus', 'Bill Clinton', 'John Stockton', 'Hannibal Lecter',
                'Richard Simmons', 'Oprah Winfrey', 'Joseph Smith', 'Mahatma Ghandi', 'Howie Mandel'
            ];
            if ($.fn.DataTable.isDataTable('#datatable-top5') && typeof top5Table !== "undefined") {
                let teams = ['XYZ', 'UFO', 'TBD', 'NFL', 'Sun', 'Spo', 'LGB', 'CIA', 'FBI', 'STD', 'PDA'];
                top5Table.rows().every( function ( rowIdx, tableLoop, rowLoop ) {
                    top5Table.cell(this, 2).data(scrambledNames[Math.floor(Math.random() * scrambledNames.length)]).draw();
                    top5Table.cell(this, 3).data(scrambledNames[Math.floor(Math.random() * scrambledNames.length)]).draw();
                });
            }
            $(this).hide();
        });

        // $('#hide-te').click(function () {
        //     doSearch('TE');
        // });

        // $('#hide-def').click(function () {
        //     doSearch('DEF');
        // });

        // $('#hide-k').click(function () {
        //     doSearch('K');
        // });

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
        top5Table = $('#datatable-top5').DataTable({
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

        $('#datatable-top5').on('click', 'tbody .selected-btn', function () {
            var data_row = top5Table.row($(this).closest('tr')).data();
            pickManager = "<?php echo isset($pickers[$currentPick-1]) ? $pickers[$currentPick-1] : 10; ?>";
            data_row.push(pickManager);
            // console.log(data_row);
            var formData = {data: data_row};

            let mine = '<?php echo $myNextPick; ?>' == 'Now!';
            let good = checkByes(data_row);
            if (mine && !good) {
                if (confirm('Check the byes, bro.')) {
                    saveSelection(formData);
                }
            } else {
                saveSelection(formData);
            }
        });
    }

</script>

<style>

    body {
        padding-top: 0;
    }
    .app-content.container-fluid {
        background: #3E3D3E;
        direction: ltr;
    }

    .card, th, table.dataTable {
        background: #3E3D3E;
        color: #fff;
    }

    table.dataTable.no-footer {
        border-bottom: none;
    }

    td, .card-header {
        color: #3E3D3E;
    }

    a.btn.btn-secondary {
        padding: .2rem .5rem;
    }

    a, a:link, a:visited {
        color: black;
        cursor: pointer;
    }

    a:hover, .card-body label, .dataTables_info, .paginate_button {
        color: white !important;
    }

    table#player-history td {
        color: white;
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

    .color-B {
        background-color: #fa887f;
    }

    .color-G {
        background-color: #8cfa84;
    }

    .color-M {
        background-color: #dffcde;
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
        display: inline-grid;
    }

    .avatars img {
        margin-bottom: -10px;
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
        font-size: 13px;
    }

    span.val {
        margin-top: 0px;
    }

    .modal-content {
        background-color: #3E3D3E;
        color: #fff;
    }

    .row.draft-pos {
        text-align: center;
    }

    #flip-team-view {
        font-size: 15px;
        float: right;
        margin-top: 10px;
        cursor: pointer;
    }

</style>