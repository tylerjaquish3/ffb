<?php

$pageName = "Draft";
include 'header.php';
include 'sidebar.html';

?>

<div class="app-content content container-fluid">
    <div class="content-wrapper">
        <div class="content-body">

            <!-- Tabs Navigation -->
            <div class="row mb-1">
                <div class="col-sm-12">
                    <div class="tab-buttons-container">
                        <button class="tab-button active" id="draft-results-tab" onclick="showCard('draft-results')">Draft Results</button>
                        <button class="tab-button" id="draft-positions-tab" onclick="showCard('draft-positions')">Draft Positions</button>
                        <button class="tab-button" id="best-drafts-tab" onclick="showCard('best-drafts')">Best Drafts</button>
                        <button class="tab-button" id="draft-spots-tab" onclick="showCard('draft-spots')">Draft Spots Chart</button>
                        <button class="tab-button" id="positions-drafted-tab" onclick="showCard('positions-drafted')">Positions by Round</button>
                    </div>
                </div>
            </div>

            <div class="row card-section" id="draft-results">
                <div class="col-sm-12 table-padding">
                    <div class="card">
                        <div class="card-header">
                            <h4 style="float: right">Draft Results</h4>
                        </div>
                        <div class="card-body" style="background: #fff; direction: ltr">
                            <table class="table table-responsive table-striped nowrap" id="datatable-draft">
                                <thead>
                                    <th>Year</th>
                                    <th>Round</th>
                                    <th>Round Pick</th>
                                    <th>Overall Pick</th>
                                    <th>Player</th>
                                    <th>Manager</th>
                                    <th>Position</th>
                                    <th>Points</th>
                                </thead>
                                <tbody>
                                    <?php
                                    foreach ($draftResults as $draft) { ?>
                                        <tr>
                                            <td><?php echo $draft['year']; ?></td>
                                            <td><?php echo $draft['round']; ?></td>
                                            <td><?php echo $draft['round_pick']; ?></td>
                                            <td><?php echo $draft['overall_pick']; ?></td>
                                            <td><?php echo $draft['player']; ?></td>
                                            <td><?php echo $draft['name']; ?></td>
                                            <td><?php echo $draft['position']; ?></td>
                                            <td><?php echo $draft['points'] ? round($draft['points'], 1) : 0; ?></td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row card-section" id="draft-positions" style="display: none;">
                <div class="col-sm-12 col-md-6 table-padding">
                    <div class="card">
                        <div class="card-header">
                            <h4 style="float: right">Draft Positions</h4>
                        </div>
                        <div class="card-body" style="background: #fff; direction: ltr">
                            <table class="table table-responsive table-striped nowrap" id="datatable-misc11">
                                <thead>
                                    <th>Manager</th>
                                    <th>#1 Picks</th>
                                    <th>#10 Picks</th>
                                    <th>Avg. Position</th>
                                </thead>
                                <tbody>
                                    <?php
                                    $result = query("SELECT name, coalesce(pick1, 0) as pick1, coalesce(pick10, 0) as pick10, adp
                                        FROM managers
                                        LEFT JOIN (
                                            SELECT COUNT(id) as pick1, manager_id FROM draft
                                            WHERE overall_pick = 1
                                        GROUP BY manager_id
                                        ) p1 ON p1.manager_id = managers.id

                                        LEFT JOIN (
                                        SELECT COUNT(id) as pick10, manager_id FROM draft
                                        WHERE overall_pick = 10
                                        GROUP BY manager_id
                                        ) p10 ON p10.manager_id = managers.id

                                        LEFT JOIN (
                                        SELECT AVG(overall_pick) as adp, manager_id FROM draft
                                        WHERE round = 1
                                        GROUP BY manager_id
                                        ) average ON average.manager_id = managers.id");
                                    while ($row = fetch_array($result)) { ?>
                                        <tr>
                                            <td><?php echo $row['name']; ?></td>
                                            <td><?php echo $row['pick1']; ?></td>
                                            <td><?php echo $row['pick10']; ?></td>
                                            <td><?php echo round($row['adp'], 1); ?></td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan=4>Number of times with #1 or #10 pick and average draft position</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row card-section" id="best-drafts" style="display: none;">
                <div class="col-sm-12 col-lg-6 table-padding">
                    <div class="card">
                        <div class="card-header">
                            <h4 style="float: right">Best Drafts</h4>
                        </div>
                        <div class="card-body" style="background: #fff; direction: ltr">
                            <table class="table table-responsive table-striped nowrap" id="datatable-bestDrafts">
                                <thead>
                                    <th>Manager</th>
                                    <th>Year</th>
                                    <th>Pick #</th>
                                    <th>Points</th>
                                </thead>
                                <tbody>
                                    <?php
                                    $result = query("SELECT r.manager, r.year, sum(points) as points, min(draft.overall_pick) as pick
                                        FROM rosters r
                                        JOIN draft on r.player = draft.player AND r.year = draft.year
                                        GROUP BY r.manager, r.year
                                        ORDER BY sum(points) DESC");
                                    while ($row = fetch_array($result)) { ?>
                                        <tr>
                                            <td><?php echo $row['manager']; ?></td>
                                            <td><?php echo '<a href="/draft.php?manager='.$row['manager'].'&year='.$row['year'].'">'.$row['year'].'</a>'; ?></td>
                                            <td><?php echo $row['pick']; ?></td>
                                            <td class="text-right"><?php echo number_format($row['points'], 1); ?></td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-sm-12 col-lg-6 table-padding">
                    <div class="card">
                        <div class="card-header">
                            <h4 style="float: right">Total Draft Points</h4>
                        </div>
                        <div class="card-body" style="background: #fff; direction: ltr">
                            <table class="table table-responsive table-striped nowrap" id="datatable-bestDraftTotals">
                                <thead>
                                    <th>Manager</th>
                                    <th>Points</th>
                                    <th>Average</th>
                                </thead>
                                <tbody>
                                    <?php
                                    $result = query("SELECT r.manager, sum(points) as points, count(distinct draft.year) as years
                                        FROM rosters r
                                        JOIN draft on r.player = draft.player AND r.year = draft.year
                                        GROUP BY r.manager
                                        ORDER BY sum(points) DESC");
                                    while ($row = fetch_array($result)) { ?>
                                        <tr>
                                            <td><?php echo $row['manager']; ?></td>
                                            <td class="text-right"><?php echo number_format($row['points'], 0); ?></td>
                                            <td class="text-right"><?php echo number_format($row['points']/$row['years'], 0); ?></td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row card-section" id="draft-spots" style="display: none;">
                <div class="col-sm-12 table-padding">
                    <div class="card">
                        <div class="card-header">
                            <h4 style="float: right">Draft Spots</h4>
                        </div>
                        <div class="card-body chart-block" style="background: #fff; direction: ltr">
                            <canvas id="draftSpotsChart" style="height: 600px"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row card-section" id="positions-drafted" style="display: none;">
                <div class="col-sm-12 table-padding">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">Positions Drafted by Round</h4>
                        </div>
                        <div class="card-body">
                            <div class="card-block chart-block">
                                <div class="row">
                                    <div class="col-sm-4">
                                        <select id="pos_manager_select" class="form-control w-50">
                                            <option value="all" selected>All Managers</option>
                                            <?php
                                            $result = query("SELECT * FROM managers ORDER BY name ASC");
                                            while ($row = fetch_array($result)) {
                                                echo '<option value="'.$row['id'].'">'.$row['name'].'</option>';
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                                
                                <canvas id="posByRoundChart" style="direction: ltr;"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    #datatable-draft input[type=text] {
        width: 100%;
    }
    
    /* Reduce spacing for tab navigation */
    .tab-buttons-container {
        padding: 10px 0 !important;
    }
</style>

<?php include 'footer.php'; ?>

<script type="text/javascript">
    $(document).ready(function() {

        let managerFilter = <?php echo isset($_GET['manager']) ? "'".$_GET['manager']."'" : 'null'; ?>;
        let yearFilter = <?php echo isset($_GET['year']) ? "'".$_GET['year']."'" : 'null'; ?>;

        $('#datatable-draft thead tr')
            .clone(true)
            .addClass('filters')
            .appendTo('#datatable-draft thead');

        $('#datatable-draft').DataTable({
            order: [
                [0, "desc"],
                [3, "asc"]
            ],
            orderCellsTop: true,
            fixedHeader: true,
            initComplete: function () {
                var api = this.api();
    
                // For each column
                api.columns()
                .eq(0)
                .each(function (colIdx) {
                    // Set the header cell to contain the input element
                    var cell = $('.filters th').eq($(api.column(colIdx).header()).index());
                    var title = $(cell).text();
                    $(cell).html('<input type="text" placeholder="filter" />');

                    // On every keypress in this input
                    $('input',$('.filters th').eq($(api.column(colIdx).header()).index()))
                    .off('keyup change')
                    .on('change', function (e) {
                        // Get the search value
                        $(this).attr('title', $(this).val());
                        var regexr = '({search})';
                        // Search the column for that value
                        api
                            .column(colIdx)
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
            }
        });

        $('#datatable-misc11').DataTable({
            searching: false,
            paging: false,
            info: false,
            order: [
                [3, "asc"]
            ]
        });
        
        $('#datatable-bestDrafts').DataTable({
            info: false,
            order: [
                [3, "desc"]
            ]
        });
        
        $('#datatable-bestDraftTotals').DataTable({
            searching: false,
            paging: false,
            info: false,
            order: [
                [2, "desc"]
            ]
        });

        var ctx = $('#draftSpotsChart');
        var years = <?php echo json_encode($draftSpotChart['years']); ?>;
        var yearLabels = years.split(",");
        var teams = <?php echo json_encode($draftSpotChart['spot']); ?>;
        let colors = ["#9c68d9","#a6c6fa","#3cf06e","#f33c47","#c0f6e6","#def89f","#dca130","#ff7f2c","#ecb2b6"," #f87598"];
        let x = 0;
        let dataset = [];
        for (const [key, value] of Object.entries(teams)) {
            let obj = {};
            obj.label = key;
            obj.data = value.split(",");
            obj.backgroundColor = 'rgba(39, 125, 161, 0.1)';
            obj.borderColor = colors[x];
            obj.fill = false;
            dataset.push(obj);
            x++;
        }

        var myBarChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: yearLabels,
                datasets: dataset
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        display: true,
                        title: {
                            display: true,
                            text: 'Draft Position',
                            font: {
                                size: 20
                            }
                        },
                        reverse: true
                    }
                }
            }
        });

        var positionsDraftedChart;
        
        $('#pos_manager_select').change(function () {
            positionsDraftedChart.destroy();
            refreshPositionsDraftedChart();
        });

        function refreshPositionsDraftedChart()
        {
            $.ajax({
                url: 'dataLookup.php',
                data:  {
                    dataType: 'positions-drafted',
                    manager: $('#pos_manager_select').val()
                },
                error: function() {
                    console.log('Error');
                },
                success: function(response) {
                    data = JSON.parse(response);
                
                    var ctx = $('#posByRoundChart');
                    positionsDraftedChart = new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: data.labels,
                            datasets: [{
                                    label: "QB",
                                    data: data.QB,
                                    backgroundColor: '#9c68d9'
                                },{
                                    label: "RB",
                                    data: data.RB,
                                    backgroundColor: '#a6c6fa'
                                },{
                                    label: "WR",
                                    data: data.WR,
                                    backgroundColor: '#3cf06e'
                                },{
                                    label: "TE",
                                    data: data.TE,
                                    backgroundColor: '#f33c47'
                                },{
                                    label: "K",
                                    data: data.K,
                                    backgroundColor: '#f87598'
                                },{
                                    label: "DEF",
                                    data: data.DEF,
                                    backgroundColor: '#ff7f2c'
                                },{
                                    label: "IDP",
                                    data: data.IDP,
                                    backgroundColor: '#c0f6e6'
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: {
                                x: {
                                    stacked: true,
                                    display: true,
                                    title: {
                                        display: true,
                                        text: 'Draft Round',
                                        font: {
                                            size: 20
                                        }
                                    }
                                },
                                y: {
                                    stacked: true,
                                    display: true,
                                    title: {
                                        display: true,
                                        text: 'Selections',
                                        font: {
                                            size: 20
                                        }
                                    }
                                }
                            },
                            plugins: {
                                datalabels: {
                                    // formatter: function(value, context) {
                                    //     return Math.round(value * 10) / 10;
                                    // },
                                    align: 'center',
                                    anchor: 'center',
                                    color: 'white',
                                    font: {
                                        weight: 'bold'
                                    }
                                }
                            }
                        },
                        plugins: [ChartDataLabels]
                    });
                }
            });
        }

        refreshPositionsDraftedChart();

        if (managerFilter && yearFilter) {
            $('#datatable-draft > thead > tr.filters > th:nth-child(6) > input[type=text]').val(managerFilter);
            $('#datatable-draft > thead > tr.filters > th:nth-child(6) > input[type=text]').trigger('keyup');
            $('#datatable-draft > thead > tr.filters > th:nth-child(1) > input[type=text]').val(yearFilter);
            $('#datatable-draft > thead > tr.filters > th:nth-child(1) > input[type=text]').trigger('keyup');
            $('#datatable-draft').DataTable().page.len(25).draw();
        }

        // Initialize the page with Draft Results tab active
        showCard('draft-results');

    });
</script>