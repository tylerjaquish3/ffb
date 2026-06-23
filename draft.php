<?php

$pageName = "Draft";
include 'header.php';
include 'sidebar.php';

?>

<div class="app-content content">
    <div class="content-wrapper">
        <div class="content-body">

            <!-- Tabs Navigation -->
            <div class="row mb-1">
                <div class="col-sm-12">
                    <div class="tab-buttons-container">
                        <button class="tab-button active" id="draft-results-tab" onclick="showCard('draft-results')">Draft Results</button>
                        <button class="tab-button" id="draft-positions-tab" onclick="showCard('draft-positions')">Draft Positions</button>
                        <button class="tab-button" id="best-drafts-tab" onclick="showCard('best-drafts')">Best Drafts</button>
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
                                            <td><a href="/players.php?player=<?php echo urlencode($draft['player']); ?>"><?php echo $draft['player']; ?></a></td>
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
                <div class="col-sm-12 table-padding">
                    <div class="card">
                        <div class="card-header">
                            <h4 style="float: right">Draft Position vs. Finish</h4>
                        </div>
                        <div class="card-body chart-block" style="background: #fff; direction: ltr">
                            <canvas id="draftPositionVsFinishChart" style="height: 500px"></canvas>
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
                                    <th># Picks</th>
                                    <th>Avg Per Pick</th>
                                </thead>
                                <tbody>
                                    <?php
                                    $draftPicksResult = query("SELECT m.name as manager, d.year, count(*) as picks,
                                        MAX(CASE WHEN d.round = 1 THEN d.round_pick END) as draft_slot
                                        FROM draft d
                                        JOIN managers m ON d.manager_id = m.id
                                        GROUP BY m.name, d.year");
                                    $draftPicksByManagerYear = [];
                                    while ($p = fetch_array($draftPicksResult)) {
                                        $draftPicksByManagerYear[$p['manager']][$p['year']] = [
                                            'picks' => $p['picks'],
                                            'slot'  => $p['draft_slot'],
                                        ];
                                    }

                                    $result = query("SELECT r.manager, r.year, sum(r.points) as points
                                        FROM rosters r
                                        WHERE EXISTS (
                                            SELECT 1 FROM draft d WHERE d.year = r.year AND d.player = r.player
                                        ) OR EXISTS (
                                            SELECT 1 FROM draft d
                                            JOIN player_aliases pa ON d.player = pa.player OR d.player = pa.alias_1 OR d.player = pa.alias_2 OR d.player = pa.alias_3
                                            WHERE d.year = r.year AND (r.player = pa.player OR r.player = pa.alias_1 OR r.player = pa.alias_2 OR r.player = pa.alias_3)
                                        )
                                        GROUP BY r.manager, r.year
                                        ORDER BY points DESC");
                                    while ($row = fetch_array($result)) {
                                        $draftInfo = $draftPicksByManagerYear[$row['manager']][$row['year']] ?? ['picks' => 0, 'slot' => ''];
                                        $picks = $draftInfo['picks'];
                                        $avgPerPick = $picks > 0 ? $row['points'] / $picks : 0;
                                    ?>
                                        <tr>
                                            <td><?php echo $row['manager']; ?></td>
                                            <td><?php echo '<a href="/draft.php?manager='.$row['manager'].'&year='.$row['year'].'">'.$row['year'].'</a>'; ?></td>
                                            <td><?php echo $draftInfo['slot']; ?></td>
                                            <td class="text-right"><?php echo number_format($row['points'], 1); ?></td>
                                            <td class="text-right"><?php echo $picks; ?></td>
                                            <td class="text-right"><?php echo number_format($avgPerPick, 1); ?></td>
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
                                    <th># Drafts</th>
                                    <th>Average</th>
                                    <th># Picks</th>
                                    <th>Avg Per Pick</th>
                                </thead>
                                <tbody>
                                    <?php
                                    $picksResult = query("SELECT m.name as manager, count(*) as picks
                                        FROM draft d
                                        JOIN managers m ON d.manager_id = m.id
                                        GROUP BY m.name");
                                    $picksByManager = [];
                                    while ($p = fetch_array($picksResult)) {
                                        $picksByManager[$p['manager']] = $p['picks'];
                                    }

                                    $result = query("SELECT manager, sum(points) as points, count(distinct year) as years
                                        FROM (
                                            SELECT r.manager, r.year, sum(r.points) as points
                                            FROM rosters r
                                            JOIN draft d ON d.year = r.year AND r.player = d.player
                                            GROUP BY r.manager, r.year
                                            UNION
                                            SELECT r.manager, r.year, sum(r.points) as points
                                            FROM rosters r
                                            JOIN draft d ON d.year = r.year
                                            JOIN player_aliases pa ON d.player = pa.player
                                                OR d.player = pa.alias_1
                                                OR d.player = pa.alias_2
                                                OR d.player = pa.alias_3
                                            WHERE r.player = pa.player OR
                                                  r.player = pa.alias_1 OR
                                                  r.player = pa.alias_2 OR
                                                  r.player = pa.alias_3
                                            GROUP BY r.manager, r.year
                                        ) combined
                                        GROUP BY manager
                                        ORDER BY sum(points) DESC");
                                    while ($row = fetch_array($result)) {
                                        $picks = $picksByManager[$row['manager']] ?? 0;
                                        $avgPerPick = $picks > 0 ? $row['points'] / $picks : 0;
                                    ?>
                                        <tr>
                                            <td><?php echo $row['manager']; ?></td>
                                            <td class="text-right"><?php echo number_format($row['points'], 0); ?></td>
                                            <td class="text-right"><?php echo $row['years']; ?></td>
                                            <td class="text-right"><?php echo number_format($row['points']/$row['years'], 0); ?></td>
                                            <td class="text-right"><?php echo $picks; ?></td>
                                            <td class="text-right"><?php echo number_format($avgPerPick, 1); ?></td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
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
                [1, "desc"]
            ]
        });

        var ctx = $('#draftSpotsChart');
        var years = <?php echo json_encode($draftSpotChart['years']); ?>;
        var yearLabels = years.split(",");
        var teams = <?php echo json_encode($draftSpotChart['spot']); ?>;
        let colors = ["#9c68d9","#a6c6fa","#3cf06e","#f33c47","#c0f6e6","#def89f","#dca130","#ff7f2c","#2dd4bf"," #f87598"];
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

        var draftPosFinishData = <?php echo json_encode($draftPositionVsFinish); ?>;

        var ctxPvF = $('#draftPositionVsFinishChart');
        new Chart(ctxPvF, {
            type: 'scatter',
            data: {
                datasets: [
                    {
                        label: 'Season Result',
                        data: draftPosFinishData.scatter,
                        backgroundColor: 'rgba(76, 175, 80, 0.35)',
                        borderColor: 'rgba(76, 175, 80, 0.35)',
                        pointRadius: 6,
                        pointHoverRadius: 8,
                        type: 'scatter'
                    },
                    {
                        label: 'Avg Finish',
                        data: draftPosFinishData.averages,
                        borderColor: 'rgb(255, 179, 179)',
                        backgroundColor: 'rgb(255, 179, 179)',
                        pointRadius: 5,
                        pointHoverRadius: 7,
                        showLine: true,
                        borderWidth: 2,
                        fill: false,
                        type: 'scatter'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                var pt = context.raw;
                                if (pt.manager) {
                                    return pt.manager + ' ' + pt.year + ' — pick #' + pt.x + ', finished #' + pt.y;
                                }
                                return 'Avg finish: ' + pt.y;
                            }
                        }
                    },
                    legend: { display: true }
                },
                scales: {
                    x: {
                        display: true,
                        title: {
                            display: true,
                            text: 'Draft Position',
                            font: { size: 16 }
                        },
                        min: 0.5,
                        max: 10.5,
                        ticks: {
                            stepSize: 1,
                            callback: function(val) {
                                if (Number.isInteger(val) && val >= 1 && val <= 10) return '#' + val;
                                return null;
                            }
                        },
                        afterBuildTicks: function(axis) {
                            axis.ticks = [1,2,3,4,5,6,7,8,9,10].map(function(v) { return {value: v}; });
                        }
                    },
                    y: {
                        display: true,
                        reverse: true,
                        title: {
                            display: true,
                            text: 'Finish Position',
                            font: { size: 16 }
                        },
                        min: 0.5,
                        max: 10.5,
                        ticks: {
                            stepSize: 1,
                            callback: function(val) {
                                if (Number.isInteger(val) && val >= 1 && val <= 10) return '#' + val;
                                return null;
                            }
                        },
                        afterBuildTicks: function(axis) {
                            axis.ticks = [1,2,3,4,5,6,7,8,9,10].map(function(v) { return {value: v}; });
                        }
                    }
                }
            }
        });

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