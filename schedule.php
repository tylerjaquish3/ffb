<?php

$pageName = "Schedule";
include 'header.php';
include 'sidebar.php';

if (isset($_GET['id'])) {
    $selectedSeason = $_GET['id'];
} else {
    $result = query("SELECT DISTINCT year FROM regular_season_matchups ORDER BY year DESC LIMIT 1");
    while ($row = fetch_array($result)) {
        $selectedSeason = $row['year'];
    }
}

$managerColors = [
    1  => "#9c68d9",
    2  => "#a6c6fa",
    3  => "#3cf06e",
    4  => "#f33c47",
    5  => "#c0f6e6",
    6  => "#def89f",
    7  => "#dca130",
    8  => "#ff7f2c",
    9  => "#2dd4bf",
    10 => "#f87598",
];

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
                        <button class="tab-button" id="playoff-calculator-tab" onclick="showCard('playoff-calculator')">
                            Playoff Calculator
                        </button>
                        <button class="tab-button" id="head-to-head-tab" onclick="showCard('head-to-head')">
                            Head to Head Count
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="row card-section" id="matchups">
                <div class="col-sm-12" id="manager-filter-bar">
                    <div class="manager-filter-legend">
                        <?php
                        $managerResult = query("SELECT id, name FROM managers ORDER BY id");
                        while ($managerRow = fetch_array($managerResult)) {
                            $mid   = $managerRow['id'];
                            $mname = htmlspecialchars($managerRow['name']);
                            $color = $managerColors[$mid] ?? '#9c68d9';
                            echo '<span class="manager-chip" data-mid="' . $mid . '" data-manager="' . $mname . '" style="background:' . $color . ';">' . $mname . '</span>';
                        }
                        ?>
                    </div>
                </div>
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
                                                <tr data-m1="<?php echo htmlspecialchars($matchup['manager1_name']); ?>" data-m2="<?php echo htmlspecialchars($matchup['manager2_name']); ?>">
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
                                        <option value="">-- Select a Manager --</option>
                                        <?php
                                        $result = query("SELECT id, name FROM managers ORDER BY name ASC");
                                        while ($row = fetch_array($result)) {
                                            echo '<option value="' . $row['id'] . '">' . htmlspecialchars($row['name']) . '</option>';
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
                                                <th>Actual Record</th>
                                                <th>Win Diff</th>
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
            
            <!-- Playoff Calculator Tab -->
            <div class="row card-section" id="playoff-calculator" style="display: none;">
                <div class="col-sm-12 table-padding">
                    <div class="card">
                        <div class="card-header">
                            <h4 id="playoff-calculator-title">Playoff Calculator</h4>
                        </div>
                        <div class="card-body" style="direction: ltr;">
                            <div id="playoff-calculator-results">
                                <div class="text-center initial-message">
                                    <p>Analyzing playoff scenarios and calculating chances...</p>
                                </div>
                                <div class="table-responsive" style="display: none;">
                                    <table id="playoff-calculator-table" class="table table-striped table-bordered">
                                        <thead class="thead-dark">
                                            <tr>
                                                <th>Rank</th>
                                                <th>Manager</th>
                                                <th>Current Record</th>
                                                <th>Remaining Opp. Record</th>
                                                <th>Remaining Opp. Points</th>
                                                <th>Playoff Chances</th>
                                                <th>Best Case Record</th>
                                                <th>Worst Case Record</th>
                                            </tr>
                                        </thead>
                                        <tbody id="playoff-calculator-tbody">
                                            <!-- Data will be inserted here via AJAX -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Head to Head Tab -->
            <!-- Head to Head Tab -->
            <div class="row card-section" id="head-to-head" style="display: none;">
                <div class="col-sm-12 col-lg-6 table-padding">
                    <div class="card">
                        <div class="card-header">
                            <h4>Head to Head Matchup Counts by Week</h4>
                        </div>
                        <div class="card-body" style="direction: ltr;">
                            <?php
                            $h2hData = getHeadToHeadByWeek();
                            $managers = $h2hData['managers'];
                            $h2hFlat = [];
                            foreach ($h2hData['weeklyGrid'] as $wk => $wGrid) {
                                for ($i = 0; $i < count($managers); $i++) {
                                    for ($j = $i + 1; $j < count($managers); $j++) {
                                        $count = $wGrid[$managers[$i]][$managers[$j]];
                                        $h2hFlat[] = [$wk, $managers[$i], $managers[$j], $count];
                                    }
                                }
                            }
                            ?>
                            <div class="table-responsive">
                                <table id="h2h-datatable" class="table table-striped table-bordered">
                                    <thead class="thead-dark">
                                        <tr>
                                            <th>Week</th>
                                            <th>Manager 1</th>
                                            <th>Manager 2</th>
                                            <th>Matchups</th>
                                        </tr>
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
        
        // No auto-load on tab click — user must select a manager first
        
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
        
        // Handle playoff calculator tab
        $('#playoff-calculator-tab').click(function() {
            // Load playoff calculator data if not already loaded
            const rowCount = $('#playoff-calculator-tbody tr').length;
            if (rowCount === 0) {
                loadPlayoffCalculatorData();
            } 
        });
        
        function loadPlayoffCalculatorData() {
            
            // Show loading indicator
            $('#playoff-calculator-results .initial-message').html('<div class="text-center"><i class="fa fa-spinner fa-spin fa-2x"></i><p>Calculating playoff scenarios...</p></div>');
            $('#playoff-calculator-results .initial-message').show();
            $('#playoff-calculator-results .table-responsive').hide();
            
            // Fetch playoff calculator data via AJAX
            $.ajax({
                url: 'dataLookup.php',
                type: 'GET',
                cache: false,
                data: {
                    dataType: 'playoff-calculator',
                    year: selectedSeason,
                    _t: new Date().getTime() // Cache busting timestamp
                },
                success: function(response) {
                    try {
                        const data = JSON.parse(response);
                        
                        // Clear the table body
                        $('#playoff-calculator-tbody').empty();
                        
                        // Populate the table with data
                        if (data.data && data.data.length > 0) {
                            
                            // Update title with remaining games count (all managers should have the same count)
                            const remainingGames = data.data[0].remaining_games;
                            const gamesText = remainingGames === 1 ? 'game' : 'games';
                            $('#playoff-calculator-title').text('Playoff Calculator (' + remainingGames + ' ' + gamesText + ' remaining)');
                            
                            data.data.forEach(function(manager) {
                                const playoffClass = manager.playoff_percentage >= 90 ? 'table-success' : 
                                                   manager.playoff_percentage >= 50 ? 'table-warning' : 
                                                   manager.playoff_percentage > 0 ? 'table-info' : 'table-danger';
                                
                                const row = '<tr class="' + playoffClass + '">' +
                                    '<td>' + manager.current_rank + '</td>' +
                                    '<td><a href="/profile.php?id=' + manager.manager_name + '">' + manager.manager_name + '</a></td>' +
                                    '<td>' + manager.current_wins + '-' + manager.current_losses + '</td>' +
                                    '<td>' + (manager.opponent_record || 'N/A') + '</td>' +
                                    '<td>' + (manager.opponent_points || 'N/A') + '</td>' +
                                    '<td><strong>' + manager.playoff_percentage + '%</strong></td>' +
                                    '<td>' + manager.best_case_record + '</td>' +
                                    '<td>' + manager.worst_case_record + '</td>' +
                                    '</tr>';
                                $('#playoff-calculator-tbody').append(row);
                            });
                            
                            // Hide loading message and show table
                            $('#playoff-calculator-results .initial-message').hide();
                            $('#playoff-calculator-results .table-responsive').show();
                            
                            // Initialize DataTable if not already initialized
                            if (!$.fn.DataTable.isDataTable('#playoff-calculator-table')) {
                                $('#playoff-calculator-table').DataTable({
                                    searching: false,
                                    paging: false,
                                    info: false,
                                    order: [[0, 'asc']] // Sort by rank
                                });
                            }
                        } else {
                            console.log('No data available');
                            $('#playoff-calculator-results .initial-message').html('<div class="alert alert-info">No playoff data available for this season.</div>');
                        }
                    } catch (e) {
                        console.error('Error parsing playoff calculator data:', e);
                        console.log('Raw response:', response);
                        $('#playoff-calculator-results .initial-message').html('<div class="alert alert-danger">Error parsing playoff calculator data: ' + e.message + '</div>');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', status, error);
                    console.log('XHR object:', xhr);
                    console.log('Response text:', xhr.responseText);
                    $('#playoff-calculator-results .initial-message').html('<div class="alert alert-danger">Error loading playoff calculator data: ' + status + ' - ' + error + '</div>');
                }
            });
        }
        
        // Head to Head tab — initialize DataTable on first click
        $('#head-to-head-tab').click(function() {
            if (!$.fn.DataTable.isDataTable('#h2h-datatable')) {
                $('#h2h-datatable').DataTable({
                    data: <?php echo json_encode($h2hFlat); ?>,
                    columns: [
                        { title: 'Week' },
                        { title: 'Manager 1' },
                        { title: 'Manager 2' },
                        { title: 'Matchups' }
                    ],
                    order: [[3, 'desc']],
                    pageLength: 25
                });
            }
        });

        // Handle mock schedule manager selection
        $('#schedule-manager-select').change(function() {
            const selectedManagerId = $(this).val();
            if (!selectedManagerId) {
                $('#mock-schedule-results .table-responsive').hide();
                $('#mock-schedule-results .initial-message').html('<p>Select a manager\'s schedule to see how other managers would perform with that schedule.</p>').show();
                return;
            }

            // Show loading indicator
            $('#mock-schedule-results .initial-message').html('<div class="text-center"><i class="fa fa-spinner fa-spin fa-2x"></i><p>Loading results...</p></div>').show();
            $('#mock-schedule-results .table-responsive').hide();

            $.ajax({
                url: baseUrl + 'dataLookup.php',
                type: 'GET',
                cache: false,
                data: {
                    dataType: 'mockSchedule',
                    year: selectedSeason,
                    scheduleManagerId: selectedManagerId,
                    _t: new Date().getTime()
                },
                success: function(response) {
                    const rows = typeof response === 'string' ? JSON.parse(response) : response;
                    let html = '';
                    rows.forEach(function(m) {
                        const rowClass = m.is_schedule_owner ? ' class="table-primary"' : '';
                        const badge = m.is_schedule_owner ? ' <span class="badge badge-primary">Original Schedule</span>' : '';
                        const actualRecord = m.actual_wins + '-' + m.actual_losses;
                        let diffText = m.win_diff > 0 ? '+' + m.win_diff : String(m.win_diff);
                        let diffStyle = '';
                        if (m.win_diff > 0) {
                            diffStyle = ' style="color:#28a745;font-weight:700;"';
                        } else if (m.win_diff < 0) {
                            diffStyle = ' style="color:#dc3545;font-weight:700;"';
                        }
                        html += '<tr' + rowClass + '>';
                        html += '<td>' + m.rank + '</td>';
                        html += '<td>' + m.manager_name + badge + '</td>';
                        html += '<td>' + m.mock_wins + '-' + m.mock_losses + '</td>';
                        html += '<td>' + m.win_pct + '%</td>';
                        html += '<td>' + Number(m.total_points).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + '</td>';
                        html += '<td>' + actualRecord + '</td>';
                        html += '<td' + diffStyle + '>' + diffText + '</td>';
                        html += '</tr>';
                    });
                    $('#mock-schedule-tbody').html(html);
                    $('#mock-schedule-results .initial-message').hide();
                    $('#mock-schedule-results .table-responsive').show();
                },
                error: function() {
                    $('#mock-schedule-results .initial-message').html('<div class="alert alert-danger">Error loading mock schedule data.</div>').show();
                    $('#mock-schedule-results .table-responsive').hide();
                }
            });
        });
        // Manager chip filter — Matchups tab
        const managerColors = <?php echo json_encode($managerColors); ?>;
        let activeManagerFilter = null;

        function hexToRgba(hex, alpha) {
            const m = /^#?([0-9a-f]{6})$/i.exec(hex.trim());
            if (!m) return 'rgba(255,220,80,' + alpha + ')';
            const n = parseInt(m[1], 16);
            return 'rgba(' + ((n >> 16) & 255) + ',' + ((n >> 8) & 255) + ',' + (n & 255) + ',' + alpha + ')';
        }

        function clearMatchupHighlight() {
            activeManagerFilter = null;
            document.querySelectorAll('.manager-chip').forEach(c => c.classList.remove('selected', 'faded'));
            document.querySelectorAll('tr[data-m1]').forEach(function(tr) {
                tr.querySelectorAll('td').forEach(td => td.style.removeProperty('background-color'));
                tr.style.removeProperty('opacity');
            });
        }

        function applyMatchupHighlight(manager, mid) {
            const hlColor = hexToRgba(managerColors[mid] || '#9c68d9', 0.3);
            document.querySelectorAll('tr[data-m1]').forEach(function(tr) {
                const m1 = tr.dataset.m1;
                const m2 = tr.dataset.m2;
                if (m1 === manager || m2 === manager) {
                    tr.querySelectorAll('td').forEach(td => td.style.setProperty('background-color', hlColor, 'important'));
                    tr.style.removeProperty('opacity');
                } else {
                    tr.querySelectorAll('td').forEach(td => td.style.removeProperty('background-color'));
                    tr.style.setProperty('opacity', '0.25');
                }
            });
        }

        $(document).on('click', '.manager-chip', function() {
            const manager = $(this).data('manager');
            const mid     = parseInt($(this).data('mid'), 10);

            if (activeManagerFilter === manager) {
                clearMatchupHighlight();
                return;
            }
            activeManagerFilter = manager;
            document.querySelectorAll('.manager-chip').forEach(c => {
                const isSelf = c.dataset.manager === manager;
                c.classList.toggle('selected', isSelf);
                c.classList.toggle('faded',    !isSelf);
            });
            applyMatchupHighlight(manager, mid);
        });
    });

</script>
