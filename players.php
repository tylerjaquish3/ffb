<?php

$pageName = "Players";
include 'header.php';
include 'sidebar.html';

if (file_exists("players_cache_file.json")) {
    $players = json_decode(file_get_contents("players_cache_file.json"));
} else {
    $result = query( "SELECT * FROM rosters");
    while ($row = fetch_array($result)) {
        $row['roster'] = '<a href="/rosters.php?year='.$row["year"].'&week='.$row["week"].'&manager='.$row['manager'].'"><i class="icon-clipboard"></i></a>';
        $row['points'] = number_format($row['points'], 2);
        $row['projected'] = number_format($row['projected'], 2);

        $players[] = $row;
    }
    $content = new \stdClass();
    $content->data = $players;

    file_put_contents("players_cache_file.json", json_encode($content));
}

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
                                            <th>Year</th>
                                            <th>Week</th>
                                            <th>Position</th>
                                            <th>Roster Spot</th>
                                            <th>Player</th>
                                            <th>Manager</th>
                                            <th>Roster</th>
                                            <th>Projected</th>
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

        // Wait a second in case it needs to cache
        setTimeout(function() {

            $('#datatable-players thead tr')
                .clone(true)
                .addClass('filters')
                .appendTo('#datatable-players thead');

            $('#datatable-players').DataTable({
                pageLength: 50,
                ajax: "players_cache_file.json",
                columns: [
                    { data: "year" },
                    { data: "week" },
                    { data: "position" },
                    { data: "roster_spot" },
                    { data: "player" },
                    { data: "manager" },
                    { data: "roster", sortable: false },
                    { data: "projected" },
                    { data: "points" }
                ],
                order: [
                    [0, "desc"]
                ],
                orderCellsTop: true,
                fixedHeader: true,
                initComplete: function () {
                    var api = this.api();
        
                    // For each column
                    api.columns().eq(0).each(function (colIdx) {
                        // Set the header cell to contain the input element
                        var cell = $('.filters th').eq(
                            $(api.column(colIdx).header()).index()
                        );
                        var title = $(cell).text();
                        if (title == 'Roster') {
                            $(cell).html('');
                        } else {
                            $(cell).html('<input type="text" placeholder="Filter" />');
                        }
    
                        // On every keypress in this input
                        $('input',$('.filters th').eq($(api.column(colIdx).header()).index()))
                        .off('keyup change')
                        .on('change', function (e) {
                            // Get the search value
                            $(this).attr('title', $(this).val());
                            var regexr = '({search})';

                            var cursorPosition = this.selectionStart;
                            // Search the column for that value
                            api.column(colIdx)
                            .search(
                                this.value != ''
                                    ? regexr.replace('{search}', '(((' + this.value + ')))')
                                    : '',
                                this.value != '',
                                this.value == ''
                            )
                            .draw();
                        })
                        .on('keyup', function (e) {
                            e.stopPropagation();
                            $(this).trigger('change');
                        });
                    });
                },
            });
        }, 1000);

    });
</script>