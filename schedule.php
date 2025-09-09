<?php

$pageName = "Schedule";
include 'header.php';
include 'sidebar.html';

if (isset($_GET['id'])) {
    $selectedSeason = $_GET['id'];
} else {
    $result = query("SELECT DISTINCT year FROM finishes ORDER BY year DESC LIMIT 1");
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
                            <p>Strength of schedule data will be implemented here. This will show the hardest to easiest schedule based on standings each week.</p>
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
                            <p>Mock schedule data will be implemented here. This will show how each manager would perform if they played other managers' schedules.</p>
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
        
        $('#year-select').change(function() {
            window.location = baseUrl + 'schedule.php?id=' + $('#year-select').val();
        });
        
        // Initialize the page with Matchups tab active
        setTimeout(function() {
            if (typeof showCard === 'function') {
                showCard('matchups');
            }
        }, 100);
    });
</script>
