<?php
    $pageName = "Records";
    include 'header.php';
    include 'sidebar.php';

    $selected_season_type  = isset($_GET['record-type'])  ? $_GET['record-type']  : '';
    $selected_fun_fact_id  = isset($_GET['fun-fact-id'])  ? $_GET['fun-fact-id']  : '';
    $selected_manager_id   = isset($_GET['manager-id'])   ? $_GET['manager-id']   : '';

    // Build the new-leaders streak data once, at the top
    $nl_query = "
        SELECT
            rl.fun_fact_id,
            ff.fact,
            ff.type,
            rl.manager_id,
            m.name as manager_name,
            rl.value,
            rl.year,
            rl.week
        FROM record_log rl
        JOIN fun_facts ff ON rl.fun_fact_id = ff.id
        JOIN managers m ON rl.manager_id = m.id
        ORDER BY rl.fun_fact_id ASC, rl.year DESC,
            CASE WHEN rl.week = 'Quarterfinal' THEN 20 WHEN rl.week = 'Semifinal' THEN 21 WHEN rl.week = 'Final' THEN 22 ELSE CAST(rl.week AS INTEGER) END DESC
    ";
    $nl_result = query($nl_query);

    $nl_streaks  = [];
    $nl_prev_fid = null;
    $nl_cur_mgr  = null;
    $nl_done     = false;

    while ($nl_row = fetch_array($nl_result)) {
        $fid = $nl_row['fun_fact_id'];
        if ($fid !== $nl_prev_fid) {
            $nl_prev_fid = $fid;
            $nl_cur_mgr  = $nl_row['manager_id'];
            $nl_done     = false;
            $nl_streaks[$fid] = [
                'fun_fact_id'   => $fid,
                'fact'          => $nl_row['fact'],
                'type'          => $nl_row['type'],
                'manager_name'  => $nl_row['manager_name'],
                'current_value' => $nl_row['value'],
                'since_year'    => $nl_row['year'],
                'since_week'    => $nl_row['week'],
                'streak'        => 1,
            ];
        } elseif (!$nl_done) {
            if ($nl_row['manager_id'] == $nl_cur_mgr) {
                $nl_streaks[$fid]['streak']++;
                $nl_streaks[$fid]['since_year'] = $nl_row['year'];
                $nl_streaks[$fid]['since_week'] = $nl_row['week'];
            } else {
                $nl_done = true;
            }
        }
    }
    usort($nl_streaks, fn($a, $b) => $a['streak'] - $b['streak']);

    $type_badge_classes = [
        'regular' => 'badge-primary',
        'post'    => 'badge-warning',
        'current' => 'badge-success',
        'draft'   => 'badge-info',
        'roster'  => 'badge-secondary',
    ];
?>
<div class="app-content content">
    <div class="content-wrapper">
        <div class="content-body">
            <div class="row">
                <div class="col-sm-12">

                    <!-- Tab nav -->
                    <div class="tab-buttons-container">
                        <button class="tab-button active" id="pane-new-leaders-tab" onclick="showCard('pane-new-leaders')">
                            New Leaders
                        </button>
                        <button class="tab-button" id="pane-record-log-tab" onclick="showCard('pane-record-log')">
                            Record Log
                        </button>
                    </div>

                    <div>

                        <!-- ── Tab 1: New Leaders ── -->
                        <div class="row card-section" id="pane-new-leaders">
                            <div class="card" style="border-top-left-radius: 0;">
                                <div class="card-header">
                                    <h4 class="card-title">Most Recently Changed Records</h4>
                                </div>
                                <div class="card-body" style="direction: ltr;">
                                    <div class="table-responsive">
                                        <table class="table table-striped table-hover" id="new-leaders-table">
                                            <thead>
                                                <tr>
                                                    <th>Fun Fact</th>
                                                    <th>Type</th>
                                                    <th>Current Leader</th>
                                                    <th>Weeks Held</th>
                                                    <th>Held Since</th>
                                                    <th>Current Value</th>
                                                    <th></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($nl_streaks as $nl): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($nl['fact']); ?></td>
                                                    <td>
                                                        <span class="badge <?php echo $type_badge_classes[$nl['type']] ?? 'badge-secondary'; ?>">
                                                            <?php echo ucfirst($nl['type']); ?>
                                                        </span>
                                                    </td>
                                                    <td><?php echo htmlspecialchars($nl['manager_name']); ?></td>
                                                    <td data-order="<?php echo $nl['streak']; ?>"><?php echo $nl['streak']; ?></td>
                                                    <td><?php echo $nl['since_year'] . ' Wk ' . $nl['since_week']; ?></td>
                                                    <td><?php echo htmlspecialchars($nl['current_value']); ?></td>
                                                    <td>
                                                        <a href="records.php?fun-fact-id=<?php echo $nl['fun_fact_id']; ?>" class="btn btn-sm btn-outline-primary">History</a>
                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- ── Tab 2: Record Log ── -->
                        <div class="card-section" id="pane-record-log" style="display: none;">

                            <!-- Filters -->
                            <div class="row" style="margin-top: 0;">
                                <div class="col-sm-6">
                                    <div class="card">
                                        <div class="card-header">
                                            <h4 class="card-title">Filters</h4>
                                        </div>
                                        <div class="card-body" style="direction: ltr;">
                                            <div class="card-block">
                                                <form method="get" id="fun-fact-form">
                                                    <div class="form-group">
                                                        <label for="record-type">Record Type:</label>
                                                        <select class="form-control" name="record-type" id="record-type" onchange="updateFunFactOptions()">
                                                            <option value="">All Record Types</option>
                                                            <option value="regular" <?php echo ($selected_season_type == 'regular') ? 'selected' : ''; ?>>Regular Season</option>
                                                            <option value="post"    <?php echo ($selected_season_type == 'post')    ? 'selected' : ''; ?>>Postseason</option>
                                                            <option value="current" <?php echo ($selected_season_type == 'current') ? 'selected' : ''; ?>>Current Season</option>
                                                            <option value="draft"   <?php echo ($selected_season_type == 'draft')   ? 'selected' : ''; ?>>Draft</option>
                                                            <option value="roster"  <?php echo ($selected_season_type == 'roster')  ? 'selected' : ''; ?>>Roster</option>
                                                        </select>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="fun-fact-id">Fun Fact:</label>
                                                        <select class="form-control" name="fun-fact-id" id="fun-fact-id" onchange="this.form.submit()">
                                                            <option value="">Select a Fun Fact</option>
                                                            <?php
                                                            $season_type_filter = '';
                                                            if (!empty($selected_season_type)) {
                                                                $season_type_filter = " AND f.type = '$selected_season_type'";
                                                            }
                                                            $ff_query  = "SELECT DISTINCT f.id, f.fact, f.type FROM fun_facts f INNER JOIN record_log r ON f.id = r.fun_fact_id WHERE 1=1 $season_type_filter ORDER BY f.sort_order ASC";
                                                            $ff_result = query($ff_query);
                                                            while ($ff_row = fetch_array($ff_result)) {
                                                                $sel = ($selected_fun_fact_id == $ff_row['id']) ? 'selected' : '';
                                                                echo "<option value=\"{$ff_row['id']}\" data-type=\"{$ff_row['type']}\" {$sel}>{$ff_row['fact']}</option>";
                                                            }
                                                            ?>
                                                        </select>
                                                    </div>
                                                </form>

                                                <div style="text-align: center; margin: 20px 0;">
                                                    <hr style="width: 40%; display: inline-block; margin: 0;">
                                                    <span style="margin: 0 10px; font-weight: bold; color: #666;">OR</span>
                                                    <hr style="width: 40%; display: inline-block; margin: 0;">
                                                </div>

                                                <form method="get" id="manager-search-form">
                                                    <div class="form-group">
                                                        <label for="manager-id">Search by Manager:</label>
                                                        <select class="form-control" name="manager-id" id="manager-id" onchange="this.form.submit()">
                                                            <option value="">Select a Manager</option>
                                                            <?php
                                                            $mgr_q = "SELECT id, name FROM managers ORDER BY name ASC";
                                                            $mgr_r = query($mgr_q);
                                                            while ($mgr_row = fetch_array($mgr_r)) {
                                                                $sel = ($selected_manager_id == $mgr_row['id']) ? 'selected' : '';
                                                                echo "<option value=\"{$mgr_row['id']}\" {$sel}>{$mgr_row['name']}</option>";
                                                            }
                                                            ?>
                                                        </select>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Results -->
                            <div class="row">
                                <div class="col-sm-12">
                                    <div class="card">
                                        <div class="card-header">
                                            <?php
                                            if (!empty($selected_fun_fact_id)) {
                                                $ff_name_q   = "SELECT fact FROM fun_facts WHERE id = $selected_fun_fact_id";
                                                $ff_name_r   = query($ff_name_q);
                                                $ff_name_row = fetch_array($ff_name_r);
                                                $ff_name     = $ff_name_row ? $ff_name_row['fact'] : 'Unknown';
                                                echo "<h4 class=\"card-title\">Record History for: {$ff_name}</h4>";
                                            } elseif (!empty($selected_manager_id)) {
                                                $mgr_name_q   = "SELECT name FROM managers WHERE id = $selected_manager_id";
                                                $mgr_name_r   = query($mgr_name_q);
                                                $mgr_name_row = fetch_array($mgr_name_r);
                                                $mgr_name     = $mgr_name_row ? $mgr_name_row['name'] : 'Unknown';
                                                echo "<h4 class=\"card-title\">Records held by: {$mgr_name}</h4>";
                                            } else {
                                                echo "<h4 class=\"card-title\">Results</h4>";
                                            }
                                            ?>
                                        </div>
                                        <div class="card-body" style="direction: ltr;">
                                            <div class="card-block">
                                                <?php
                                                if (!empty($selected_fun_fact_id)) {

                                                    echo '<div class="mb-1">';
                                                    echo '<div class="btn-group" role="group">';
                                                    echo '<button type="button" class="btn btn-outline-primary" id="detailed-view-btn" onclick="showDetailedView()">Detailed View</button>';
                                                    echo '<button type="button" class="btn btn-primary"         id="summary-view-btn"  onclick="showSummaryView()">Summary View</button>';
                                                    echo '</div>';
                                                    echo '</div>';

                                                    $record_query = "
                                                        SELECT
                                                            r.year,
                                                            r.week,
                                                            GROUP_CONCAT(m.name) as manager_names,
                                                            r.value,
                                                            r.note,
                                                            MAX(r.new_leader) as new_leader,
                                                            MIN(r.manager_id) as manager_id
                                                        FROM record_log r
                                                        LEFT JOIN managers m ON r.manager_id = m.id
                                                        WHERE r.fun_fact_id = $selected_fun_fact_id
                                                        GROUP BY r.year, r.week, r.value
                                                        ORDER BY r.year ASC,
                                                            CASE WHEN r.week = 'Quarterfinal' THEN 20 WHEN r.week = 'Semifinal' THEN 21 WHEN r.week = 'Final' THEN 22 ELSE CAST(r.week AS INTEGER) END ASC
                                                    ";
                                                    $record_result = query($record_query);

                                                    $records = [];
                                                    while ($row = fetch_array($record_result)) {
                                                        $records[] = $row;
                                                    }

                                                    if (!empty($records)) { ?>

                                                        <!-- Detailed View -->
                                                        <div id="detailed-view" class="table-responsive" style="display: none;">
                                                            <table class="table table-bordered table-striped table-hover" id="record-logs-table">
                                                                <thead>
                                                                    <tr>
                                                                        <th>Year</th>
                                                                        <th>Week</th>
                                                                        <th>Manager(s)</th>
                                                                        <th>Value</th>
                                                                        <th>Note</th>
                                                                        <th>Status</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    <?php foreach (array_reverse($records) as $record): ?>
                                                                    <tr>
                                                                        <td><?php echo $record['year']; ?></td>
                                                                        <td><?php echo $record['week']; ?></td>
                                                                        <td><?php echo $record['manager_names']; ?></td>
                                                                        <td><?php echo $record['value']; ?></td>
                                                                        <td><?php echo $record['note']; ?></td>
                                                                        <td><?php if ($record['new_leader']) echo '<span class="tag tag-success">NEW LEADER</span>'; ?></td>
                                                                    </tr>
                                                                    <?php endforeach; ?>
                                                                </tbody>
                                                            </table>
                                                        </div>

                                                        <!-- Summary View -->
                                                        <div id="summary-view" class="table-responsive">
                                                            <table class="table table-bordered table-striped table-hover" id="summary-logs-table">
                                                                <thead>
                                                                    <tr>
                                                                        <th>Manager(s)</th>
                                                                        <th>From</th>
                                                                        <th>To</th>
                                                                        <th>Duration</th>
                                                                        <th>Value</th>
                                                                        <th>Note</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    <?php
                                                                    $summary_records = [];
                                                                    $current_group   = null;

                                                                    foreach ($records as $record) {
                                                                        $manager_names = $record['manager_names'];
                                                                        if ($current_group === null ||
                                                                            $current_group['manager_names'] !== $manager_names ||
                                                                            $record['new_leader'] == 1) {
                                                                            if ($current_group !== null) $summary_records[] = $current_group;
                                                                            $current_group = [
                                                                                'manager_names' => $manager_names,
                                                                                'start_value'   => $record['value'],
                                                                                'end_value'     => $record['value'],
                                                                                'note'          => $record['note'],
                                                                                'start_year'    => $record['year'],
                                                                                'start_week'    => $record['week'],
                                                                                'end_year'      => $record['year'],
                                                                                'end_week'      => $record['week'],
                                                                                'week_count'    => 1,
                                                                                'sort_key'      => $record['year'] * 100 + ($record['week'] === 'Quarterfinal' ? 20 : ($record['week'] === 'Semifinal' ? 21 : ($record['week'] === 'Final' ? 22 : intval($record['week']))))
                                                                            ];
                                                                        } else {
                                                                            $current_group['end_year']   = $record['year'];
                                                                            $current_group['end_week']   = $record['week'];
                                                                            $current_group['end_value']  = $record['value'];
                                                                            $current_group['week_count']++;
                                                                            $current_group['sort_key']   = $record['year'] * 100 + ($record['week'] === 'Quarterfinal' ? 20 : ($record['week'] === 'Semifinal' ? 21 : ($record['week'] === 'Final' ? 22 : intval($record['week']))));
                                                                            if (!empty($record['note'])) $current_group['note'] = $record['note'];
                                                                        }
                                                                    }
                                                                    if ($current_group !== null) $summary_records[] = $current_group;

                                                                    usort($summary_records, fn($a, $b) => $b['sort_key'] - $a['sort_key']);

                                                                    foreach ($summary_records as $summary):
                                                                        $duration_text = $summary['week_count'] . ' week' . ($summary['week_count'] != 1 ? 's' : '');
                                                                        $value_text    = $summary['start_value'] !== $summary['end_value']
                                                                            ? $summary['start_value'] . ' → ' . $summary['end_value']
                                                                            : $summary['start_value'];
                                                                    ?>
                                                                    <tr>
                                                                        <td><?php echo $summary['manager_names']; ?></td>
                                                                        <td><?php echo $summary['start_year'] . ' Week ' . $summary['start_week']; ?></td>
                                                                        <td><?php echo $summary['end_year']   . ' Week ' . $summary['end_week']; ?></td>
                                                                        <td><strong><?php echo $duration_text; ?></strong></td>
                                                                        <td><?php echo $value_text; ?></td>
                                                                        <td><?php echo $summary['note']; ?></td>
                                                                    </tr>
                                                                    <?php endforeach; ?>
                                                                </tbody>
                                                            </table>
                                                        </div>

                                                    <?php } else {
                                                        echo '<p>No record history found for this fun fact.</p>';
                                                    }

                                                } elseif (!empty($selected_manager_id)) {

                                                    $mgr_rec_q = "
                                                        SELECT DISTINCT f.id as fun_fact_id, f.fact as fun_fact_name, f.type as season_type
                                                        FROM record_log r
                                                        LEFT JOIN fun_facts f ON r.fun_fact_id = f.id
                                                        WHERE r.manager_id = $selected_manager_id
                                                        ORDER BY f.sort_order ASC
                                                    ";
                                                    $mgr_rec_r = query($mgr_rec_q);
                                                    $manager_records = [];
                                                    while ($row = fetch_array($mgr_rec_r)) $manager_records[] = $row;

                                                    if (!empty($manager_records)) { ?>
                                                        <div class="table-responsive">
                                                            <table class="table table-bordered table-striped table-hover" id="manager-records-table">
                                                                <thead>
                                                                    <tr>
                                                                        <th>Fun Fact</th>
                                                                        <th>Duration as Leader</th>
                                                                        <th></th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    <?php foreach ($manager_records as $ff_rec):
                                                                        $ff_id = $ff_rec['fun_fact_id'];

                                                                        $periods_q = "
                                                                            SELECT r.year, r.week, r.value, r.note
                                                                            FROM record_log r
                                                                            WHERE r.fun_fact_id = $ff_id AND r.manager_id = $selected_manager_id
                                                                            ORDER BY r.year ASC,
                                                                                CASE WHEN r.week = 'Quarterfinal' THEN 20 WHEN r.week = 'Semifinal' THEN 21 WHEN r.week = 'Final' THEN 22 ELSE CAST(r.week AS INTEGER) END ASC
                                                                        ";
                                                                        $periods_r = query($periods_q);
                                                                        $periods   = [];
                                                                        while ($pr = fetch_array($periods_r)) $periods[] = $pr;
                                                                        $period_count = count($periods);

                                                                        $cur_leader_q = "
                                                                            SELECT r.manager_id FROM record_log r
                                                                            WHERE r.fun_fact_id = $ff_id
                                                                            ORDER BY r.year DESC,
                                                                                CASE WHEN r.week = 'Quarterfinal' THEN 20 WHEN r.week = 'Semifinal' THEN 21 WHEN r.week = 'Final' THEN 22 ELSE CAST(r.week AS INTEGER) END DESC
                                                                            LIMIT 1
                                                                        ";
                                                                        $cur_leader_r   = query($cur_leader_q);
                                                                        $cur_leader_row = fetch_array($cur_leader_r);
                                                                        $is_cur_leader  = ($cur_leader_row && $cur_leader_row['manager_id'] == $selected_manager_id);
                                                                    ?>
                                                                    <tr>
                                                                        <td>
                                                                            <strong><?php echo htmlspecialchars($ff_rec['fun_fact_name']); ?></strong>
                                                                            <br><small class="text-muted"><?php echo ucfirst($ff_rec['season_type']); ?> Season</small>
                                                                        </td>
                                                                        <td><?php echo $period_count === 1 ? '1 period' : "$period_count periods"; ?></td>
                                                                        <td><?php if ($is_cur_leader) echo '<span class="badge badge-primary">Current Leader</span>'; ?></td>
                                                                    </tr>
                                                                    <?php endforeach; ?>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    <?php } else {
                                                        echo '<p>This manager has never been a leader in any fun fact.</p>';
                                                    }
                                                } else {
                                                    echo '<p class="text-muted">Select a Fun Fact or Manager above to view record history.</p>';
                                                }
                                                ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div><!-- /pane-record-log -->

                    </div><!-- /tab wrapper -->
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>

<script>
$(document).ready(function() {

    // Auto-switch to Record Log tab when a filter is active in the URL
    <?php if (!empty($selected_fun_fact_id) || !empty($selected_manager_id)): ?>
    showCard('pane-record-log');
    <?php endif; ?>

    // Adjust new-leaders DataTable when its tab becomes visible
    document.getElementById('pane-new-leaders-tab').addEventListener('click', function() {
        setTimeout(function() { $('#new-leaders-table').DataTable().columns.adjust().draw(); }, 50);
    });

    // Custom week sort for DataTables
    $.fn.dataTable.ext.type.order['week-sort-pre'] = function(data) {
        switch(data) {
            case 'Quarterfinal': return 20;
            case 'Semifinal':    return 21;
            case 'Final':        return 22;
            default:             return parseInt(data) || 0;
        }
    };

    // New Leaders DataTable — pre-sorted by streak ASC via data-order attribute
    $('#new-leaders-table').DataTable({
        "order":      [[3, "asc"]],
        "pageLength": 15,
        "lengthMenu": [[10, 15, 25, 50, -1], [10, 15, 25, 50, "All"]],
        "searching":  true,
        "responsive": true,
        "autoWidth":  false,
        "columnDefs": [
            { "targets": [3], "className": "text-center" },
            { "targets": [6], "orderable": false, "className": "text-center" }
        ],
    });

    // Detailed view DataTable
    $('#record-logs-table').DataTable({
        "order":      [[0, "desc"], [1, "desc"]],
        "pageLength": 25,
        "lengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
        "searching":  true,
        "responsive": true,
        "autoWidth":  false,
        "columnDefs": [
            { "targets": [0], "type": "num",       "className": "text-center" },
            { "targets": [1], "type": "week-sort", "className": "text-center" }
        ],
    });

    // Summary view DataTable
    $('#summary-logs-table').DataTable({
        "order":      [[2, "asc"]],
        "pageLength": 25,
        "lengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
        "searching":  true,
        "responsive": true,
        "autoWidth":  false,
        "columnDefs": [
            {
                "targets": [1, 2],
                "render": function(data, type, row) {
                    if (type === 'display') return data;
                    var m = data.match(/(\d{4}) Week (\d+)/);
                    return m ? parseInt(m[1]) * 1000 + parseInt(m[2]) : data;
                }
            },
            { "targets": [3], "className": "text-center" },
            { "targets": [4], "className": "text-center" }
        ],
    });

    // Manager records DataTable
    $('#manager-records-table').DataTable({
        "order":      [[0, "asc"]],
        "pageLength": 25,
        "lengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
        "searching":  true,
        "responsive": true,
        "autoWidth":  false,
        "columnDefs": [
            { "targets": [1], "className": "text-center" },
            { "targets": [2], "className": "text-center" }
        ],
    });
});

function showDetailedView() {
    document.getElementById('detailed-view').style.display = 'block';
    document.getElementById('summary-view').style.display  = 'none';
    document.getElementById('detailed-view-btn').classList.replace('btn-outline-primary', 'btn-primary');
    document.getElementById('summary-view-btn').classList.replace('btn-primary', 'btn-outline-primary');
}

function showSummaryView() {
    document.getElementById('detailed-view').style.display = 'none';
    document.getElementById('summary-view').style.display  = 'block';
    document.getElementById('summary-view-btn').classList.replace('btn-outline-primary', 'btn-primary');
    document.getElementById('detailed-view-btn').classList.replace('btn-primary', 'btn-outline-primary');
    $('#summary-logs-table').DataTable().columns.adjust().draw();
}

function updateFunFactOptions() {
    document.getElementById('fun-fact-id').value = '';
    document.getElementById('fun-fact-form').submit();
}
</script>
