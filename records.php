<?php
    $pageName = "Records";
    include 'header.php';
    include 'sidebar.html';
?>
<div class="app-content content">
    <div class="content-wrapper">
        
        <div class="content-body">
            <div class="row">
                <div class="col-sm-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">Record Log</h4>
                        </div>
                        <div class="card-body" style="direction: ltr;">
                            <div class="card-block">
                                <form method="get" id="fun-fact-form">
                                    <div class="form-group">
                                        <label for="fun-fact-id">Fun Fact:</label>
                                        <select class="form-control" name="fun-fact-id" id="fun-fact-id" onchange="this.form.submit()">
                                            <option value="">Select a Fun Fact</option>
                                            <?php
                                            
                                            // Get only fun facts that have records in record_log
                                            $query = "SELECT DISTINCT f.id, f.fact FROM fun_facts f INNER JOIN record_log r ON f.id = r.fun_fact_id ORDER BY f.id ASC";
                                            $result = query($query);
                                            
                                            // Selected fun fact ID
                                            $selected_fun_fact_id = isset($_GET['fun-fact-id']) ? $_GET['fun-fact-id'] : '';
                                            
                                            // Output options
                                            while ($row = fetch_array($result)) {
                                                $selected = ($selected_fun_fact_id == $row['id']) ? 'selected' : '';
                                                echo "<option value=\"{$row['id']}\" {$selected}>{$row['fact']}</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </form>
                                
                                <?php
                                // Display records if a fun fact is selected
                                if (!empty($selected_fun_fact_id)) {
                                    // Get the fun fact name
                                    $fun_fact_query = "SELECT fact FROM fun_facts WHERE id = $selected_fun_fact_id";
                                    $fun_fact_result = query($fun_fact_query);
                                    $fun_fact_row = fetch_array($fun_fact_result);
                                    $fun_fact_name = $fun_fact_row ? $fun_fact_row['fact'] : 'Unknown';
                                    
                                    echo "<h3>Record History for: {$fun_fact_name}</h3>";
                                    
                                    // Query to get all record logs for this fun fact, grouped by year and week
                                    // Concatenate manager names for ties
                                    $record_query = "
                                        SELECT 
                                            r.year,
                                            r.week,
                                            GROUP_CONCAT(m.name) as manager_names,
                                            r.value,
                                            r.note,
                                            MAX(r.new_leader) as new_leader
                                        FROM record_log r
                                        LEFT JOIN managers m ON r.manager_id = m.id
                                        WHERE r.fun_fact_id = $selected_fun_fact_id
                                        GROUP BY r.year, r.week, r.value
                                        ORDER BY r.year DESC, r.week DESC
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
                                        <div class="table-responsive">
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
                                                    <?php foreach ($records as $record) : ?>
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
                                        
                                        <?php
                                    } else {
                                        echo "<p>No record history found for this fun fact.</p>";
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
                },
                // { 
                //     "targets": [3], // Value column
                //     "type": "num",
                //     "className": "text-center"
                // },
                // {
                //     "targets": [5], // Status column
                //     "orderable": false,
                //     "className": "text-center"
                // }
            ],
        });
    });
</script>
