<?php

$pageName = "Players";
include 'header.php';
include 'sidebar.html';

?>

<div class="app-content content container-fluid">
    <div class="content-wrapper">
        <div class="content-header row"></div>
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

        let playerFilter = <?php echo isset($_GET['player']) ? "'".$_GET['player']."'" : 'null'; ?>;

        setTimeout(function() {
            if (playerFilter) {
                $('#datatable-players_filter > label > input[type=search]').val(playerFilter);
                $('#datatable-players_filter > label > input[type=search]').trigger('keyup');
                // Also expand the search results
                $('#datatable-players > tbody > tr > td.dt-control').trigger('click');
            }
        }, 1000);

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
                                th.textContent = key.charAt(0).toUpperCase() + key.slice(1);;
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

                    // Make the table into a datatable
                    $(table).DataTable({
                        paging: false
                    });
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