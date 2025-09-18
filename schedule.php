<?php

$pageName = "Schedule";
include 'header.php';
include 'sidebar.html';

if (isset($_GET['id'])) {
    $selectedSeason = $_GET['id'];
} else {
    $result = query("SELECT DISTINCT year FROM regular_season_matchups ORDER BY year DESC LIMIT 1");
    while ($row = fetch_array($result)) {
        $selectedSeason = $row['year'];
    }
}

?>

<div class="app-content content">
    <div class="content-wrapper">
        <div class="content-body"> 
            <div class="row" style="direction: ltr;">
                <div class="col-sm-12 d-md-none">
                    <h5 style="margin-top: 5px; color: #fff;">Choose Season</h5>
                </div>
                <div class="col-sm-12 col-md-4">
                    <select id="year-select" class="form-control">
                        <?php
                        // Get years from both regular_season_matchups and schedule tables
                        $result = query("SELECT DISTINCT year FROM (
                            SELECT year FROM regular_season_matchups
                            UNION
                            SELECT year FROM schedule
                        ) ORDER BY year DESC");
                        
                        while ($row = fetch_array($result)) {
                            if ($row['year'] == $selectedSeason) {
                                echo '<option selected value="'.$row['year'].'">'.$row['year'].'</option>';
                            } else {
                                echo '<option value="'.$row['year'].'">'.$row['year'].'</option>';
                            }
                        }
                        ?>
                    </select>
                </div>
            </div>
            
            <!-- Tabs Navigation -->
            <div class="row mb-1">
                <div class="col-sm-12">
                    <div class="tab-buttons-container">
                        <button class="tab-button active" id="matchups-tab" onclick="showCard('matchups')">
                            Matchups
                        </button>
                        <button class="tab-button" id="strength-of-schedule-tab" onclick="showCard('strength-of-schedule')">
                            Strength of Schedule
                        </button>
                        <button class="tab-button" id="mock-schedule-tab" onclick="showCard('mock-schedule')">
                            Mock Schedule
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="row card-section" id="matchups">
                <?php if(!empty($scheduleData)): ?>
                <?php foreach($scheduleData as $weekData): ?>
                    <div class="col-sm-12 col-md-6 table-padding">
                        <div class="card">
                            <div class="card-header">
                                <h4><a href="/newsletter.php?year=<?php echo $weekData['year']; ?>&week=<?php echo $weekData['week']; ?>">Week <?php echo $weekData['week']; ?></a>
                                    <?php if (isset($weekData['date_range'])): ?>
                                    <small> | <?php echo $weekData['date_range']; ?></small>
                                    <?php endif; ?>
                                </h4>
                            </div>
                            <div class="card-body" style="direction: ltr;">
                                <div class="row">
                                    <div class="col-sm-12">
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Manager 1</th>
                                                    <th>Manager 2</th>
                                                    <th>Score</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach($weekData['matchups'] as $matchup): ?>
                                                <tr>
                                                    <td>
                                                        <?php 
                                                        if ($matchup['is_completed'] && $matchup['manager1_score'] > $matchup['manager2_score']) {
                                                            echo '<span class="badge badge-primary">' . $matchup['manager1_name'] . '</span>';
                                                        } elseif ($matchup['is_completed']) {
                                                            echo '<span class="badge badge-secondary">' . $matchup['manager1_name'] . '</span>';
                                                        } else {
                                                            echo $matchup['manager1_name'];
                                                        }
                                                        ?>
                                                    </td>
                                                    <td>
                                                        <?php 
                                                        if ($matchup['is_completed'] && $matchup['manager2_score'] > $matchup['manager1_score']) {
                                                            echo '<span class="badge badge-primary">' . $matchup['manager2_name'] . '</span>';
                                                        } elseif ($matchup['is_completed']) {
                                                            echo '<span class="badge badge-secondary">' . $matchup['manager2_name'] . '</span>';
                                                        } else {
                                                            echo $matchup['manager2_name'];
                                                        }
                                                        ?>
                                                    </td>
                                                    <td>
                                                        <?php 
                                                        if ($matchup['is_completed']) {
                                                            $score1 = number_format($matchup['manager1_score'], 2);
                                                            $score2 = number_format($matchup['manager2_score'], 2);
                                                            $scoreText = $score1 . ' - ' . $score2;
                                                            echo '<a href="/rosters.php?year=' . $weekData['year'] . '&week=' . $weekData['week'] . '&manager=' . $matchup['manager1_name'] . '">' . $scoreText . '</a>';
                                                        } else {
                                                            echo '<span class="badge badge-warning">Upcoming</span>';
                                                        }
                                                        ?>
                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-sm-12 table-padding">
                        <div class="card">
                            <div class="card-header">
                                <h4>No Schedule Data Available</h4>
                            </div>
                            <div class="card-body" style="direction: ltr;">
                                <p>No schedule information is available for this season.</p>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Strength of Schedule Tab -->
            <div class="row card-section" id="strength-of-schedule" style="display: none;">
                <div class="col-sm-12 table-padding">
                    <div class="card">
                        <div class="card-header">
                            <h4>Strength of Schedule</h4>
                        </div>
                        <div class="card-body" style="direction: ltr;">
                            <div class="table-responsive">
                                <table id="strength-of-schedule-table" class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Rank</th>
                                            <th>Manager</th>
                                            <th>Opponent Record</th>
                                            <th>Opponent Points</th>
                                            <th>Avg. Opponent PPG</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $sosData = getStrengthOfSchedule($selectedSeason);
                                        $currentYear = date('Y');
                                        $isCurrentSeason = ($selectedSeason == $currentYear);
                                        
                                        foreach ($sosData as $data) {
                                            
                                            echo '<tr>';
                                            echo '<td>' . $data['rank'] . '</td>';
                                            echo '<td><a href="/profile.php?id=' . $data['name'] . '">' . $data['name'] . '</a></td>';
                                            echo '<td>' . $data['opponent_record'] . '</td>';
                                            echo '<td>' . number_format($data['opponent_points'], 2) . '</td>';
                                            echo '<td>' . number_format($data['avg_opponent_points'], 2) . '</td>';
                                            echo '</tr>';
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Mock Schedule Tab -->
            <div class="row card-section" id="mock-schedule" style="display: none;">
                <div class="col-sm-12 table-padding">
                    <div class="card">
                        <div class="card-header">
                            <h4>Mock Schedule</h4>
                        </div>
                        <div class="card-body" style="direction: ltr;">
                            <div class="row mb-3">
                                <div class="col-sm-12 col-md-6">
                                    <select id="schedule-manager-select" class="form-control">
                                        <?php
                                        // Get managers from the managers table
                                        $result = query("SELECT id, manager_name FROM managers ORDER BY id");
                                        if (!$result) {
                                            // Fallback if managers table doesn't exist or has a different structure
                                            $result = query("SELECT DISTINCT manager1_id as id FROM regular_season_matchups WHERE year = $selectedSeason ORDER BY manager1_id");
                                        }
                                        
                                        while ($row = fetch_array($result)) {
                                            $managerId = isset($row['id']) ? $row['id'] : $row['manager1_id'];
                                            $managerName = isset($row['manager_name']) ? $row['manager_name'] : getManagerName($managerId);
                                            echo '<option value="'.$managerId.'">'.$managerName.'</option>';
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                            <div id="mock-schedule-results">
                                <div class="text-center initial-message">
                                    <p>Select a manager's schedule to see how other managers would perform with that schedule.</p>
                                </div>
                                <div class="table-responsive" style="display: none;">
                                    <table id="mock-schedule-table" class="table table-striped table-bordered">
                                        <thead class="thead-dark">
                                            <tr>
                                                <th>Rank</th>
                                                <th>Manager</th>
                                                <th>Mock Record</th>
                                                <th>Win %</th>
                                                <th>Total Points</th>
                                            </tr>
                                        </thead>
                                        <tbody id="mock-schedule-tbody">
                                            <!-- Data will be inserted here via AJAX -->
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

<?php include 'footer.php'; ?>

<script>
    $(document).ready(function() {
        let baseUrl = "<?php echo $BASE_URL; ?>";
        let selectedSeason = "<?php echo $selectedSeason; ?>";
        
        $('#year-select').change(function() {
            window.location = baseUrl + 'schedule.php?id=' + $('#year-select').val();
        });
        
        // Initialize the page with Matchups tab active
        setTimeout(function() {
            if (typeof showCard === 'function') {
                showCard('matchups');
            }
        }, 100);
        
        // Auto-select the first manager when the Mock Schedule tab is clicked
        $('#mock-schedule-tab').click(function() {
            if ($('#mock-schedule-tbody').is(':empty')) {
                setTimeout(function() {
                    $('#schedule-manager-select').trigger('change');
                }, 100);
            }
        });
        
        // Initialize strength of schedule DataTable
        $('#strength-of-schedule-tab').click(function() {
            // Initialize only once
            if (!$.fn.DataTable.isDataTable('#strength-of-schedule-table')) {
                $('#strength-of-schedule-table').DataTable({
                    searching: false,
                    paging: false,
                    info: false
                });
            }
        });
        
        // Handle mock schedule manager selection
        $('#schedule-manager-select').change(function() {
            const selectedManagerId = $(this).val();
            const selectedManagerName = $(this).find("option:selected").text();
            
            // Show loading indicator
            $('.initial-message').html('<div class="text-center"><i class="fa fa-spinner fa-spin fa-2x"></i><p>Loading results...</p></div>');
            
            // Fetch mock schedule data via AJAX
            $.ajax({
                url: baseUrl + 'dataLookup.php',
                type: 'GET',
                cache: false,
                data: {
                    dataType: 'mockSchedule',
                    year: selectedSeason,
                    scheduleManagerId: selectedManagerId,
                    _t: new Date().getTime() // Cache busting timestamp
                },
                success: function(response) {
                    // Hide the initial message and show the table
                    $('.initial-message').hide();
                    $('.table-responsive').show();
                    $('#mock-schedule-explanation').show();
                    
                    // Update the table body with the data
                    $('#mock-schedule-tbody').html(response);
                },
                error: function() {
                    $('.initial-message').html('<div class="alert alert-danger">Error loading mock schedule data.</div>');
                    $('.initial-message').show();
                    $('.table-responsive').hide();
                    $('#mock-schedule-explanation').hide();
                }
            });
        });
    });
</script>
