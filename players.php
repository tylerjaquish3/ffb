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
                <div class="col-sm-12">
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
                <div class="col-sm-12">
                    <div class="card">
                        <div class="card-header">
                            <h4>Top 100 Player Seasons</h4>
                        </div>
                        <div class="card-body" style="direction: ltr;">
                            <div class="row">
                                <div class="col-sm-12">
                                    <table class="table table-striped nowrap" id="datatable-playerSeasons">
                                        <thead>
                                            <th>Year</th>
                                            <th>Manager</th>
                                            <th>Player</th>
                                            <th>Points</th>
                                        </thead>
                                        <tbody>
                                        <?php
                                        $result = query(
                                            "SELECT player, year, sum(points) as points, max(manager) as man
                                            FROM rosters
                                            GROUP BY player, year
                                            ORDER BY points DESC
                                            LIMIT 100");
                                        while ($array = fetch_array($result)) { ?>
                                            <tr>
                                                <td><?php echo $array['year']; ?></td>
                                                <td><?php echo $array['man']; ?></td>
                                                <td><?php echo $array['player']; ?></td>
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
            </div>
            <div class="row">
                <div class="col-sm-12">
                    <div class="card">
                        <div class="card-header">
                            <h4>Top 100 Player Weeks</h4>
                        </div>
                        <div class="card-body" style="direction: ltr;">
                            <div class="row">
                                <div class="col-sm-12">
                                    <table class="table table-striped nowrap" id="datatable-playerWeeks">
                                        <thead>
                                            <th>Year</th>
                                            <th>Week</th>
                                            <th>Manager</th>
                                            <th></th>
                                            <th>Player</th>
                                            <th>Points</th>
                                        </thead>
                                        <tbody>
                                        <?php
                                        $result = query(
                                            "SELECT player, week, year, sum(points) as points, max(manager) as man
                                            FROM rosters
                                            GROUP BY player, year, week
                                            ORDER BY points DESC
                                            LIMIT 100");
                                        while ($row = fetch_array($result)) { ?>
                                            <tr>
                                                <td><?php echo $row['year']; ?></td>
                                                <td><?php echo $row['week']; ?></td>
                                                <td><?php echo $row['man']; ?></td>
                                                <td>
                                                    <?php echo '<a href="/rosters.php?year='.$row['year'].'&week='.$row['week'].'&manager='.$row['man'].'">
                                                    <i class="icon-clipboard"></i></a>'; ?>
                                                </td>
                                                <td><?php echo '<a href="/players.php?player='.$row['player'].'">'.$row['player'].'</a>'; ?></td>
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

        $('#datatable-playerSeasons').DataTable({
            pageLength: 10,
            order: [
                [3, "desc"]
            ]
        });
        
        $('#datatable-playerWeeks').DataTable({
            pageLength: 10,
            order: [
                [5, "desc"]
            ]
        });

    });
</script>