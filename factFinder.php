<?php

$pageName = "Fact Finder";
include 'header.php';

// Check if in production environment - if so, redirect to 404
if ($APP_ENV === 'production') {
    header('Location: /404.php');
    exit();
}

include 'sidebar.php';

// Data is loaded in functions.php based on pageName
$selectedYear = $factFinderData['selectedYear'];
$selectedWeek = $factFinderData['selectedWeek'];

?>

<div class="app-content content">
    <div class="content-wrapper">

        <div class="content-body">

            <!-- Filter Card -->
            <div class="row">
                <div class="col-sm-6">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">Filters</h4>
                        </div>
                        <div class="card-body" style="direction: ltr;">
                            <div class="card-block">
                                <form method="GET" action="factFinder.php" id="filter-form">
                                    <div class="form-group">
                                        <label for="week">Week (<?php echo $selectedYear; ?> Season):</label>
                                        <select name="week" id="week" class="form-control" onchange="this.form.submit()">
                                            <option value="">Select Week</option>
                                            <?php foreach ($availableWeeks as $weekNum): ?>
                                                <option value="<?php echo $weekNum; ?>" 
                                                    <?php echo ($selectedWeek == $weekNum) ? 'selected' : ''; ?>>
                                                    Week <?php echo $weekNum; ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    
                                    <?php if ($selectedWeek): ?>
                                        <div class="form-group">
                                            <a href="factFinder.php" class="btn btn-secondary">Clear Filters</a>
                                        </div>
                                    <?php endif; ?>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabs Navigation -->
            <?php if ($selectedWeek): ?>
            <div class="row mb-1">
                <div class="col-sm-12">
                    <div class="tab-buttons-container">
                        <button class="tab-button active" id="overview-tab" onclick="showCard('overview')">
                            Overview
                        </button>
                        <button class="tab-button" id="matchups-tab" onclick="showCard('matchups')">
                            Matchups
                        </button>
                        <button class="tab-button" id="records-tab" onclick="showCard('records')">
                            Records
                        </button>
                        <button class="tab-button" id="points-tab" onclick="showCard('points')">
                            Points
                        </button>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Content Area -->
            <?php if ($selectedWeek): ?>
            
            <!-- Overview Tab -->
            <div class="row card-section" id="overview">
                <div class="col-sm-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">
                                Overview - <?php echo $selectedYear; ?> Week <?php echo $selectedWeek; ?>
                            </h4>
                        </div>
                        <div class="card-body">
                            <div class="card-block" style="direction: ltr">
                                <p>Overview data for <?php echo $selectedYear; ?> Week <?php echo $selectedWeek; ?> will be displayed here.</p>
                                <p class="text-muted">This section will show general information and summary statistics.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Matchups Tab -->
            <div class="row card-section" id="matchups" style="display: none;">
                <div class="col-sm-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">
                                Week <?php echo $selectedWeek; ?> Matchups - Fun Facts & Head-to-Head History
                            </h4>
                        </div>
                        <div class="card-body">
                            <div class="card-block" style="direction: ltr">
                                <?php if (!empty($currentMatchups)): ?>
                                    <?php foreach ($currentMatchups as $matchup): ?>
                                        <div class="row mb-4">
                                            <div class="col-md-6">
                                                <div class="card bg-light">
                                                    <div class="card-body text-center">
                                                        <h5 class="card-title">
                                                            <?php echo $matchup['manager1_name']; ?> 
                                                            <span class="text-muted">vs</span> 
                                                            <?php echo $matchup['manager2_name']; ?>
                                                        </h5>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="card">
                                                    <div class="card-header">
                                                        <h6 class="card-title mb-0">Head-to-Head Fun Facts</h6>
                                                    </div>
                                                    <div class="card-body">
                                                        <?php 
                                                        $facts = getHeadToHeadFacts(
                                                            $matchup['manager1_id'], 
                                                            $matchup['manager2_id'],
                                                            $matchup['manager1_name'],
                                                            $matchup['manager2_name']
                                                        );
                                                        if (!empty($facts)): ?>
                                                            <ul class="list-unstyled mb-0">
                                                                <?php foreach ($facts as $fact): ?>
                                                                    <li class="mb-2">
                                                                        <i class="fa fa-info-circle text-info mr-2"></i>
                                                                        <?php echo $fact; ?>
                                                                    </li>
                                                                <?php endforeach; ?>
                                                            </ul>
                                                        <?php else: ?>
                                                            <p class="text-muted mb-0">No historical data available for this matchup.</p>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <p class="text-muted">No matchups found for Week <?php echo $selectedWeek; ?> of the <?php echo $selectedYear; ?> season.</p>
                                    <p class="text-muted">This may be because the schedule hasn't been set for this week yet.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Records Tab -->
            <div class="row card-section" id="records" style="display: none;">
                <div class="col-sm-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">
                                Records - <?php echo $selectedYear; ?> Week <?php echo $selectedWeek; ?>
                            </h4>
                        </div>
                        <div class="card-body">
                            <div class="card-block" style="direction: ltr">
                                <p>Records data for <?php echo $selectedYear; ?> Week <?php echo $selectedWeek; ?> will be displayed here.</p>
                                <p class="text-muted">This section will show records set or broken during this week.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Points Tab -->
            <div class="row card-section" id="points" style="display: none;">
                <div class="col-sm-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">
                                Points - <?php echo $selectedYear; ?> Week <?php echo $selectedWeek; ?>
                            </h4>
                        </div>
                        <div class="card-body">
                            <div class="card-block" style="direction: ltr">
                                <p>Points data for <?php echo $selectedYear; ?> Week <?php echo $selectedWeek; ?> will be displayed here.</p>
                                <p class="text-muted">This section will show scoring statistics and point distributions.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <?php elseif ($selectedWeek): ?>
            <div class="row">
                <div class="col-sm-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="card-block" style="direction: ltr">
                                <p class="text-muted">Please select a week to view data, or the schedule may not be available for this week yet.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php else: ?>
            <div class="row">
                <div class="col-sm-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="card-block" style="direction: ltr">
                                <h5>Welcome to the Fact Finder</h5>
                                <p class="text-muted">
                                    Select a week to explore head-to-head matchup history and fun facts for the 
                                    <strong><?php echo $selectedYear; ?></strong> season.
                                </p>
                                <p class="text-muted">
                                    Each matchup will display interesting historical data including:
                                </p>
                                <ul class="text-muted">
                                    <li>All-time head-to-head records</li>
                                    <li>Highest-scoring games between managers</li>
                                    <li>Biggest margins of victory</li>
                                    <li>Recent trends and dominance</li>
                                    <li>Playoff meeting history</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

        </div>
    </div>
</div>

<script>
// Show/hide tab content
function showCard(cardId) {
    // Hide all card sections
    const cardSections = document.querySelectorAll('.card-section');
    cardSections.forEach(function(section) {
        section.style.display = 'none';
    });
    
    // Remove active class from all tab buttons
    const tabButtons = document.querySelectorAll('.tab-button');
    tabButtons.forEach(function(button) {
        button.classList.remove('active');
    });
    
    // Show the selected card section
    const selectedCard = document.getElementById(cardId);
    if (selectedCard) {
        selectedCard.style.display = 'flex';
    }
    
    // Add active class to the clicked tab button
    const activeTab = document.getElementById(cardId + '-tab');
    if (activeTab) {
        activeTab.classList.add('active');
    }
}
</script>

<?php include 'footer.php'; ?>
