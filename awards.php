<?php

$pageName = "Awards";
include 'header.php';
include 'sidebar.php';

$type = 'all';
if (isset($_GET['id'])) {
    $type = $_GET['id'];
} 
if (isset($_GET['new_leader'])) {
    $new_leader = $_GET['new_leader'];
} else {
    $new_leader = 0;
}
?>

<div class="app-content content">
    <div class="content-wrapper">

        <div class="content-body">
            <div class="row">
                <div class="col-sm-6">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">Filters</h4>
                        </div>
                        <div class="card-body" style="direction: ltr;">
                            <div class="card-block">
                                <div class="form-group">
                                    <label for="type-select">Record Type:</label>
                                    <select class="form-control" id="type-select">
                                        <option value="all" <?php if ($type == 'all') { echo 'selected'; } ?>>All Record Types</option>
                                        <option value="regular" <?php if ($type == 'regular') { echo 'selected'; } ?>>Regular Season</option>
                                        <option value="post" <?php if ($type == 'post') { echo 'selected'; } ?>>Postseason</option>
                                        <option value="current" <?php if ($type == 'current') { echo 'selected'; } ?>>Current Season</option>
                                        <option value="draft" <?php if ($type == 'draft') { echo 'selected'; } ?>>Draft</option>
                                        <option value="roster" <?php if ($type == 'roster') { echo 'selected'; } ?>>Roster</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <div class="form-check">
                                        <input type="checkbox" id="new_leader" <?php echo $new_leader == 1 ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="new_leader">New Leader Only</label>
                                    </div>
                                </div>
                                <a href="/records.php">Go To Record Log</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-12 table-padding">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">Fun Facts</h4>
                        </div>
                        <div class="card-body" style="background: #fff; direction: ltr;">
                            <?php
                            // Collect awards grouped by manager
                            $managers_awards = [];
                            
                            for ($x = 1; $x < 11; $x++) {
                                $manager_result = query("SELECT * FROM managers WHERE id = $x");
                                while ($manager_row = fetch_array($manager_result)) {
                                    $manager_name = $manager_row['name'];
                                    $managers_awards[$manager_name] = [];
                                    
                                    // Get positive awards
                                    $query = "SELECT * FROM manager_fun_facts mff
                                        JOIN fun_facts ff ON mff.fun_fact_id = ff.id
                                        JOIN managers ON managers.id = mff.manager_id
                                        WHERE is_positive = 1 AND manager_id = $x";

                                    if ($new_leader) {
                                        $query .= " AND new_leader = 1";
                                    }
                                    if ($type != 'all') {
                                        $query .= " AND type = '$type'";
                                    }
                                    
                                    $query .= " ORDER BY ff.sort_order";
                                    
                                    $result = query($query);
                                    while ($row = fetch_array($result)) {
                                        $value = $row['value'];
                                        if (isfloat($row['value']) && isDecimal($row['value'])) {
                                            $value = number_format($row['value'], 2, '.', ',');
                                        }
                                        
                                        $managers_awards[$manager_name][] = [
                                            'fact' => $row['fact'],
                                            'value' => $value,
                                            'note' => $row['note'],
                                            'new_leader' => $row['new_leader'],
                                            'is_positive' => true
                                        ];
                                    }
                                    
                                    // Get negative awards
                                    $query = "SELECT * FROM manager_fun_facts mff
                                        JOIN fun_facts ff ON mff.fun_fact_id = ff.id
                                        JOIN managers ON managers.id = mff.manager_id
                                        WHERE is_positive = 0 AND manager_id = $x";
                                        
                                    if ($new_leader) {
                                        $query .= " AND new_leader = 1";
                                    }
                                    if ($type != 'all') {
                                        $query .= " AND type = '$type'";
                                    }
                                    
                                    $query .= " ORDER BY ff.sort_order";
                                    
                                    $result = query($query);
                                    while ($row = fetch_array($result)) { 
                                        $value = $row['value'];
                                        if (isfloat($row['value']) && isDecimal($row['value'])) {
                                            $value = number_format($row['value'], 2, '.', ',');
                                        }
                                        
                                        $managers_awards[$manager_name][] = [
                                            'fact' => $row['fact'],
                                            'value' => $value,
                                            'note' => $row['note'],
                                            'new_leader' => $row['new_leader'],
                                            'is_positive' => false
                                        ];
                                    }
                                }
                            }
                            
                            // Display awards grouped by manager
                            foreach ($managers_awards as $manager_name => $awards) {
                                if (!empty($awards)) {
                                    echo '<div class="manager-section">';
                                    echo '<div class="manager-header">';
                                    echo '<h3><a href="profile.php?id=' . urlencode($manager_name) . '">' . htmlspecialchars($manager_name) . '</a></h3>';
                                    echo '<div class="award-count">' . count($awards) . ' award' . (count($awards) != 1 ? 's' : '') . '</div>';
                                    echo '</div>';
                                    
                                    echo '<div class="awards-grid">';
                                    foreach ($awards as $award) {
                                        $award_class = $award['is_positive'] ? 'award-badge positive' : 'award-badge negative';
                                        $new_leader_icon = $award['new_leader'] ? '<i class="icon-warning award-new-icon" title="New Leader"></i>' : '';
                                        
                                        echo '<div class="' . $award_class . '">';
                                        echo '<div class="award-header-badge">';
                                        echo '</div>';
                                        echo '<div class="award-title">' . htmlspecialchars($award['fact']) . '</div>';
                                        echo '<div class="award-value">' . htmlspecialchars($award['value']) . ' ' . $new_leader_icon . '</div>';
                                        if (!empty($award['note'])) {
                                            echo '<div class="award-note">' . htmlspecialchars($award['note']) . '</div>';
                                        }
                                        echo '</div>';
                                    }
                                    echo '</div>';
                                    echo '</div>';
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

<?php include 'footer.php'; ?>

<script type="text/javascript">

    let baseUrl = "<?php echo $BASE_URL; ?>";

    $('#type-select').change(function() {
        newLeader = $('#new_leader').is(':checked') ? 1 : 0;
        window.location = baseUrl+'awards.php?id='+$('#type-select').val()+'&new_leader='+newLeader;
    });

    $('#new_leader').change(function() {
        newLeader = $('#new_leader').is(':checked') ? 1 : 0;
        window.location = baseUrl+'awards.php?id='+$('#type-select').val()+'&new_leader='+newLeader;
    });

</script>