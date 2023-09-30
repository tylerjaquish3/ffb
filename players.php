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
                                    <table class="table" id="datatable-players">
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
                    for (const row of data) {
                        const thead = document.createElement("thead");
                        for (const key of Object.keys(row)) {
                            const th = document.createElement("th");
                            th.textContent = key.charAt(0).toUpperCase() + key.slice(1);;
                            thead.appendChild(th);
                        }
                        if (count == 1) {
                            table.appendChild(thead);
                        }
                        
                        const tr = document.createElement("tr");
                        for (const key of Object.keys(row)) {
                            const td = document.createElement("td");
                            td.textContent = row[key];
                            tr.appendChild(td);
                        }

                        table.appendChild(tr);
                        count++;
                    }

                    div.removeClass('loading');
                    div.text('');
                    div.append(table);
                }
            } );

            return div; 
        }

        var table = $('#datatable-players').DataTable({
            pageLength: 25,
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
                [1, "asc"]
            ],
            
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

    });
</script>