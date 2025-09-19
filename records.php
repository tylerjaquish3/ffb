<?php
    $pageName = "Records";
    include 'header.php';
    include 'sidebar.html';
?>
<div class="app-content content">
    <div class="content-wrapper">
        
        <div class="content-body">
            <div class="row">
                <div class="col-sm-6">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">Record Log</h4>
                        </div>
                        <div class="card-body" style="direction: ltr;">
                            <div class="card-block">
                                <form method="get" id="fun-fact-form">
                                    <div class="form-group">
                                        <label for="record-type">Record Type:</label>
                                        <select class="form-control" name="record-type" id="record-type" onchange="updateFunFactOptions()">
                                            <option value="">All Record Types</option>
                                            <?php
                                            $selected_season_type = isset($_GET['record-type']) ? $_GET['record-type'] : '';
                                            ?>
                                            <option value="regular" <?php echo ($selected_season_type == 'regular') ? 'selected' : ''; ?>>Regular Season</option>
                                            <option value="post" <?php echo ($selected_season_type == 'post') ? 'selected' : ''; ?>>Postseason</option>
                                            <option value="current" <?php echo ($selected_season_type == 'current') ? 'selected' : ''; ?>>Current Season</option>
                                            <option value="draft" <?php echo ($selected_season_type == 'draft') ? 'selected' : ''; ?>>Draft</option>
                                            <option value="roster" <?php echo ($selected_season_type == 'roster') ? 'selected' : ''; ?>>Roster</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="fun-fact-id">Fun Fact:</label>
                                        <select class="form-control" name="fun-fact-id" id="fun-fact-id" onchange="this.form.submit()">
                                            <option value="">Select a Fun Fact</option>
                                            <?php
                                            
                                            // Build query based on season type filter
                                            $season_type_filter = '';
                                            if (!empty($selected_season_type)) {
                                                $season_type_filter = " AND f.type = '$selected_season_type'";
                                            }
                                            
                                            // Get only fun facts that have records in record_log, filtered by season type
                                            $query = "SELECT DISTINCT f.id, f.fact, f.type FROM fun_facts f INNER JOIN record_log r ON f.id = r.fun_fact_id WHERE 1=1 $season_type_filter ORDER BY f.sort_order ASC";
                                            $result = query($query);
                                            
                                            // Selected fun fact ID
                                            $selected_fun_fact_id = isset($_GET['fun-fact-id']) ? $_GET['fun-fact-id'] : '';
                                            
                                            // Output options
                                            while ($row = fetch_array($result)) {
                                                $selected = ($selected_fun_fact_id == $row['id']) ? 'selected' : '';
                                                echo "<option value=\"{$row['id']}\" data-type=\"{$row['type']}\" {$selected}>{$row['fact']}</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </form>
                                
                                <!-- OR Section -->
                                <div style="text-align: center; margin: 20px 0;">
                                    <hr style="width: 40%; display: inline-block; margin: 0;">
                                    <span style="margin: 0 10px; font-weight: bold; color: #666;">OR</span>
                                    <hr style="width: 40%; display: inline-block; margin: 0;">
                                </div>
                                
                                <!-- Manager Search Section -->
                                <form method="get" id="manager-search-form">
                                    <div class="form-group">
                                        <label for="manager-id">Search by Manager:</label>
                                        <select class="form-control" name="manager-id" id="manager-id" onchange="this.form.submit()">
                                            <option value="">Select a Manager</option>
                                            <?php
                                            $selected_manager_id = isset($_GET['manager-id']) ? $_GET['manager-id'] : '';
                                            
                                            // Get all managers
                                            $manager_query = "SELECT id, name FROM managers ORDER BY name ASC";
                                            $manager_result = query($manager_query);
                                            
                                            while ($manager_row = fetch_array($manager_result)) {
                                                $selected = ($selected_manager_id == $manager_row['id']) ? 'selected' : '';
                                                echo "<option value=\"{$manager_row['id']}\" {$selected}>{$manager_row['name']}</option>";
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
            <div class="row">
                <div class="col-sm-12">
                    <div class="card">
                        <div class="card-header">
                            <?php
                                // Display records if a fun fact is selected
                                if (!empty($selected_fun_fact_id)) {
                                    // Get the fun fact name
                                    $fun_fact_query = "SELECT fact FROM fun_facts WHERE id = $selected_fun_fact_id";
                                    $fun_fact_result = query($fun_fact_query);
                                    $fun_fact_row = fetch_array($fun_fact_result);
                                    $fun_fact_name = $fun_fact_row ? $fun_fact_row['fact'] : 'Unknown';

                                    echo "<h4 class=\"card-title\">Record History for: {$fun_fact_name}</h4>";
                                } elseif (!empty($selected_manager_id)) {
                                    // Get the manager name
                                    $manager_query = "SELECT name FROM managers WHERE id = $selected_manager_id";
                                    $manager_result = query($manager_query);
                                    $manager_row = fetch_array($manager_result);
                                    $manager_name = $manager_row ? $manager_row['name'] : 'Unknown';

                                    echo "<h4 class=\"card-title\">Records held by: {$manager_name}</h4>";
                                }
                            ?>
                        </div>
                        <div class="card-body" style="direction: ltr;">
                            <div class="card-block">
                                
                                <?php
                                // Display records if a fun fact is selected
                                if (!empty($selected_fun_fact_id)) {
                                    
                                    // Add view toggle buttons
                                    echo '<div class="mb-3">';
                                    echo '<div class="btn-group" role="group" aria-label="View Toggle">';
                                    echo '<button type="button" class="btn btn-outline-primary" id="detailed-view-btn" onclick="showDetailedView()">Detailed View</button>';
                                    echo '<button type="button" class="btn btn-primary" id="summary-view-btn" onclick="showSummaryView()">Summary View</button>';
                                    echo '</div>';
                                    echo '</div>';
                                    
                                    // Query to get all record logs for this fun fact, grouped by year and week
                                    // Concatenate manager names for ties
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
                                                CASE 
                                                    WHEN r.week = 'Quarterfinal' THEN 20
                                                    WHEN r.week = 'Semifinal' THEN 21
                                                    WHEN r.week = 'Final' THEN 22
                                                    ELSE CAST(r.week AS INTEGER)
                                                END ASC
                                    ";
                                    $record_result = query($record_query);
                                    
                                    // Check if there are records
                                    $has_records = false;
                                    $records = [];
                                    while ($row = fetch_array($record_result)) {
                                        $has_records = true;
                                        $records[] = $row;
                                    }
                                    
                                    if ($has_records) {
                                        ?>
                                        
                                        <!-- Detailed View Table -->
                                        <div id="detailed-view" class="table-responsive" style="display: none;">
                                            <table class="table table-bordered table-striped table-hover table-responsive" id="record-logs-table">
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
                                                    <?php 
                                                    // Show records in reverse chronological order for detailed view
                                                    $reversed_records = array_reverse($records);
                                                    foreach ($reversed_records as $record) : ?>
                                                    <tr>
                                                        <td><?php echo $record['year']; ?></td>
                                                        <td><?php echo $record['week']; ?></td>
                                                        <td><?php echo $record['manager_names']; ?></td>
                                                        <td><?php echo $record['value']; ?></td>
                                                        <td><?php echo $record['note']; ?></td>
                                                        <td>
                                                            <?php 
                                                            if ($record['new_leader']) {
                                                                echo '<span class="tag tag-success">NEW LEADER</span>';
                                                            }
                                                            ?>
                                                        </td>
                                                    </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                        
                                        <!-- Summary View Table -->
                                        <div id="summary-view" class="table-responsive">
                                            <table class="table table-bordered table-striped table-hover table-responsive" id="summary-logs-table">
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
                                                    // Process records for summary view
                                                    // Records are in chronological order (oldest first)
                                                    $summary_records = [];
                                                    $current_group = null;
                                                    
                                                    foreach ($records as $record) {
                                                        $manager_names = $record['manager_names'];
                                                        
                                                        // If this is a new leader (different manager) or first record, start a new group
                                                        // For cumulative stats, we group consecutive weeks with the same leader regardless of value changes
                                                        if ($current_group === null || 
                                                            $current_group['manager_names'] !== $manager_names ||
                                                            $record['new_leader'] == 1) {                                                            // Finalize the previous group
                                                            if ($current_group !== null) {
                                                                $summary_records[] = $current_group;
                                                            }
                                                            
                                                            // Start new group
                                                            $current_group = [
                                                                'manager_names' => $manager_names,
                                                                'start_value' => $record['value'],
                                                                'end_value' => $record['value'],
                                                                'note' => $record['note'],
                                                                'start_year' => $record['year'],
                                                                'start_week' => $record['week'],
                                                                'end_year' => $record['year'],
                                                                'end_week' => $record['week'],
                                                                'week_count' => 1,
                                                                'sort_key' => $record['year'] * 100 + ($record['week'] === 'Quarterfinal' ? 20 : ($record['week'] === 'Semifinal' ? 21 : ($record['week'] === 'Final' ? 22 : intval($record['week']))))
                                                            ];
                                                        } else {
                                                            // Continue current group (same manager and value)
                                                            $current_group['end_year'] = $record['year'];
                                                            $current_group['end_week'] = $record['week'];
                                                            $current_group['end_value'] = $record['value'];  // Update end value
                                                            $current_group['week_count']++;
                                                            $current_group['sort_key'] = $record['year'] * 100 + ($record['week'] === 'Quarterfinal' ? 20 : ($record['week'] === 'Semifinal' ? 21 : ($record['week'] === 'Final' ? 22 : intval($record['week']))));
                                                            // Update note if this record has one
                                                            if (!empty($record['note'])) {
                                                                $current_group['note'] = $record['note'];
                                                            }
                                                        }
                                                    }
                                                    
                                                    // Don't forget the last group
                                                    if ($current_group !== null) {
                                                        $summary_records[] = $current_group;
                                                    }
                                                    
                                                    // Sort by sort_key (end date) in descending order to show most recent first
                                                    usort($summary_records, function($a, $b) {
                                                        return $b['sort_key'] - $a['sort_key'];
                                                    });
                                                    
                                                    foreach ($summary_records as $summary) :
                                                        $duration_text = $summary['week_count'] == 1 ? "1 week" : $summary['week_count'] . " weeks";
                                                        $from_text = $summary['start_year'] . " Week " . $summary['start_week'];
                                                        $to_text = $summary['end_year'] . " Week " . $summary['end_week'];
                                                        
                                                        // Show value range if different start/end values
                                                        if ($summary['start_value'] !== $summary['end_value']) {
                                                            $value_text = $summary['start_value'] . " → " . $summary['end_value'];
                                                        } else {
                                                            $value_text = $summary['start_value'];
                                                        }
                                                    ?>
                                                    <tr>
                                                        <td><?php echo $summary['manager_names']; ?></td>
                                                        <td><?php echo $summary['start_year'] . " Week " . $summary['start_week']; ?></td>
                                                        <td><?php echo $summary['end_year'] . " Week " . $summary['end_week']; ?></td>
                                                        <td><strong><?php echo $duration_text; ?></strong></td>
                                                        <td><?php echo $value_text; ?></td>
                                                        <td><?php echo $summary['note']; ?></td>
                                                    </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                        
                                        <?php
                                    } else {
                                        echo "<p>No record history found for this fun fact.</p>";
                                    }
                                } elseif (!empty($selected_manager_id)) {
                                    // Manager search logic
                                    
                                    // Get all fun facts where this manager has been a leader
                                    $manager_records_query = "
                                        SELECT DISTINCT
                                            f.id as fun_fact_id,
                                            f.fact as fun_fact_name,
                                            f.type as season_type
                                        FROM record_log r
                                        LEFT JOIN fun_facts f ON r.fun_fact_id = f.id
                                        WHERE r.manager_id = $selected_manager_id
                                        ORDER BY f.sort_order ASC
                                    ";
                                    $manager_records_result = query($manager_records_query);
                                    
                                    $manager_records = [];
                                    while ($row = fetch_array($manager_records_result)) {
                                        $manager_records[] = $row;
                                    }
                                    
                                    if (!empty($manager_records)) {
                                        ?>
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
                                                    <?php 
                                                    foreach ($manager_records as $fun_fact_record) :
                                                        $fun_fact_id = $fun_fact_record['fun_fact_id'];
                                                        
                                                        // Get all periods where this manager was leader for this fun fact
                                                        $periods_query = "
                                                            SELECT 
                                                                r.year,
                                                                r.week,
                                                                r.value,
                                                                r.note
                                                            FROM record_log r
                                                            WHERE r.fun_fact_id = $fun_fact_id 
                                                            AND r.manager_id = $selected_manager_id
                                                            ORDER BY r.year ASC, 
                                                                    CASE 
                                                                        WHEN r.week = 'Quarterfinal' THEN 20
                                                                        WHEN r.week = 'Semifinal' THEN 21
                                                                        WHEN r.week = 'Final' THEN 22
                                                                        ELSE CAST(r.week AS INTEGER)
                                                                    END ASC
                                                        ";
                                                        $periods_result = query($periods_query);
                                                        
                                                        $periods = [];
                                                        while ($period_row = fetch_array($periods_result)) {
                                                            $periods[] = $period_row;
                                                        }
                                                        
                                                        // Count periods and calculate duration
                                                        $period_count = count($periods);
                                                        
                                                        // Create years/weeks display
                                                        $years_weeks_display = [];
                                                        foreach ($periods as $period) {
                                                            $years_weeks_display[] = $period['year'] . ' Wk' . $period['week'];
                                                        }
                                                        $years_weeks_text = implode(', ', $years_weeks_display);
                                                        
                                                        // Check if current leader
                                                        $current_leader_query = "
                                                            SELECT r.manager_id, m.name 
                                                            FROM record_log r
                                                            LEFT JOIN managers m ON r.manager_id = m.id
                                                            WHERE r.fun_fact_id = $fun_fact_id
                                                            ORDER BY r.year DESC, 
                                                                    CASE 
                                                                        WHEN r.week = 'Quarterfinal' THEN 20
                                                                        WHEN r.week = 'Semifinal' THEN 21
                                                                        WHEN r.week = 'Final' THEN 22
                                                                        ELSE CAST(r.week AS INTEGER)
                                                                    END DESC
                                                            LIMIT 1
                                                        ";
                                                        $current_leader_result = query($current_leader_query);
                                                        $current_leader_row = fetch_array($current_leader_result);
                                                        $is_current_leader = ($current_leader_row && $current_leader_row['manager_id'] == $selected_manager_id);
                                                        
                                                        ?>
                                                        <tr>
                                                            <td>
                                                                <strong><?php echo htmlspecialchars($fun_fact_record['fun_fact_name']); ?></strong>
                                                                <br><small class="text-muted"><?php echo ucfirst($fun_fact_record['season_type']); ?> Season</small>
                                                            </td>
                                                            <td>
                                                                <?php 
                                                                if ($period_count == 1) {
                                                                    echo "1 period";
                                                                } else {
                                                                    echo "$period_count periods";
                                                                }
                                                                ?>
                                                            </td>
                                                            <td>
                                                                <?php if ($is_current_leader) : ?>
                                                                    <span class="badge badge-primary">Current Leader</span>
                                                                <?php endif; ?>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                        <?php
                                    } else {
                                        echo "<p>This manager has never been a leader in any fun fact.</p>";
                                    }
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>

<script>
    $(document).ready(function() {
        // Custom sorting function for week column to handle postseason weeks
        $.fn.dataTable.ext.type.order['week-sort-pre'] = function(data) {
            // Convert postseason weeks to numbers for proper sorting
            switch(data) {
                case 'Quarterfinal':
                    return 20;
                case 'Semifinal':
                    return 21;
                case 'Final':
                    return 22;
                default:
                    // For regular week numbers, parse as integer
                    return parseInt(data) || 0;
            }
        };
        
        // Initialize detailed view DataTable
        $('#record-logs-table').DataTable({
            "order": [[0, "desc"], [1, "desc"]], // Order by Year desc, then Week desc
            "pageLength": 25,
            "lengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
            "searching": true,
            "responsive": true,
            "autoWidth": false,
            "columnDefs": [
                { 
                    "targets": [0], // Year column
                    "type": "num",
                    "className": "text-center"
                },
                { 
                    "targets": [1], // Week column
                    "type": "week-sort", // Use custom sorting type
                    "className": "text-center"
                }
            ],
        });
        
        // Initialize summary view DataTable
        $('#summary-logs-table').DataTable({
            "order": [[2, "asc"]], // Order by To date desc (most recent end date first)
            "pageLength": 25,
            "lengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
            "searching": true,
            "responsive": true,
            "autoWidth": false,
            "columnDefs": [
                { 
                    "targets": [1, 2], // From and To columns
                    "type": "date",
                    "render": function(data, type, row) {
                        if (type === 'display') {
                            return data;
                        }
                        // For sorting, convert "2010 Week 10" to a sortable number like 201010
                        var match = data.match(/(\d{4}) Week (\d+)/);
                        if (match) {
                            return parseInt(match[1]) * 1000 + parseInt(match[2]);
                        }
                        return data;
                    }
                },
                { 
                    "targets": [3], // Duration column
                    "className": "text-center"
                },
                { 
                    "targets": [4], // Value column
                    "className": "text-center"
                }
            ],
        });
        
        // Initialize manager records table
        $('#manager-records-table').DataTable({
            "order": [[0, "asc"]], // Order by Fun Fact name
            "pageLength": 25,
            "lengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
            "searching": true,
            "responsive": true,
            "autoWidth": false,
            "columnDefs": [
                { 
                    "targets": [1], // Duration column
                    "className": "text-center"
                },
                { 
                    "targets": [2], // Current Leader column
                    "className": "text-center"
                }
            ],
        });
    });
    
    function showDetailedView() {
        document.getElementById('detailed-view').style.display = 'block';
        document.getElementById('summary-view').style.display = 'none';
        
        // Update button styles
        document.getElementById('detailed-view-btn').classList.remove('btn-outline-primary');
        document.getElementById('detailed-view-btn').classList.add('btn-primary');
        document.getElementById('summary-view-btn').classList.remove('btn-primary');
        document.getElementById('summary-view-btn').classList.add('btn-outline-primary');
    }
    
    function showSummaryView() {
        document.getElementById('detailed-view').style.display = 'none';
        document.getElementById('summary-view').style.display = 'block';
        
        // Update button styles
        document.getElementById('summary-view-btn').classList.remove('btn-outline-primary');
        document.getElementById('summary-view-btn').classList.add('btn-primary');
        document.getElementById('detailed-view-btn').classList.remove('btn-primary');
        document.getElementById('detailed-view-btn').classList.add('btn-outline-primary');
        
        // Recalculate DataTable column widths for summary view
        $('#summary-logs-table').DataTable().columns.adjust().draw();
    }

    function updateFunFactOptions() {
        const seasonType = document.getElementById('record-type').value;
        const funFactSelect = document.getElementById('fun-fact-id');
        
        // Reset fun fact selection
        funFactSelect.value = '';
        
        // Submit form to refresh with new season type filter
        document.getElementById('fun-fact-form').submit();
    }
</script>
