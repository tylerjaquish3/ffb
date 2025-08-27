<?php

// Include functions for database access
include_once 'functions.php';

// Initialize variables
$recap = '';
$preview = '';
$editYear = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');
$editWeek = isset($_GET['week']) ? (int)$_GET['week'] : 1;

// Handle form submission BEFORE any HTML output
if ($_POST) {
    $editYear = (int)$_POST['year'];
    $editWeek = (int)$_POST['week'];
    $recap = isset($_POST['recap']) ? $_POST['recap'] : '';
    $preview = isset($_POST['preview']) ? $_POST['preview'] : '';
    
    if (isset($_POST['save'])) {
        // Check if record exists
        $existingQuery = query("SELECT id FROM newsletters WHERE year = $editYear AND week = $editWeek");
        $existingRow = fetch_array($existingQuery);
        
        if ($existingRow) {
            // Update existing record
            $updateQuery = "UPDATE newsletters SET recap = '" . SQLite3::escapeString($recap) . "', preview = '" . SQLite3::escapeString($preview) . "' WHERE year = $editYear AND week = $editWeek";
            query($updateQuery);
        } else {
            // Insert new record
            $insertQuery = "INSERT INTO newsletters (year, week, recap, preview) VALUES ($editYear, $editWeek, '" . SQLite3::escapeString($recap) . "', '" . SQLite3::escapeString($preview) . "')";
            query($insertQuery);
        }
        
        // Redirect to newsletter page with the saved year and week
        header("Location: newsletter.php?year=$editYear&week=$editWeek");
        exit;
    }
}

// Fetch existing content if any
$contentQuery = query("SELECT recap, preview FROM newsletters WHERE year = $editYear AND week = $editWeek");
$contentRow = fetch_array($contentQuery);
if ($contentRow) {
    $recap = $contentRow['recap'] ?? '';
    $preview = $contentRow['preview'] ?? '';
}

// Now include the HTML output files
$pageName = "Edit Newsletter";
include 'header.php';

// Check if environment is set to production
if (isset($APP_ENV) && $APP_ENV === 'production') {
    header("Location: 404.php");
    exit;
}

include 'sidebar.html';

?>

<div class="app-content content container-fluid">
    <div class="content-wrapper">
        <div class="content-header row"></div>

        <div class="content-body">
            
            <!-- Year and Week Selection -->
            <div class="row" style="direction: ltr;">
                <div class="col-sm-12">
                    <div class="card">
                        <div class="card-header">
                            <h4>Edit Newsletter Content</h4>
                        </div>
                        <div class="card-body" style="background: #fff;">
                            <form method="GET" id="yearWeekForm">
                                <div class="row">
                                    <div class="col-sm-12 col-md-3">
                                        <label for="year-select">Season:</label>
                                        <select id="year-select" name="year" class="form-control" onchange="updateURL()">
                                            <?php
                                            $currentYear = date('Y');
                                            $result = query("SELECT DISTINCT year FROM rosters ORDER BY year DESC");
                                            $yearsInDB = array();
                                            
                                            // Collect all years from database
                                            while ($row = fetch_array($result)) {
                                                $yearsInDB[] = $row['year'];
                                            }
                                            
                                            // Add current year if not in database
                                            if (!in_array($currentYear, $yearsInDB)) {
                                                array_unshift($yearsInDB, $currentYear);
                                            }
                                            
                                            // Sort years in descending order
                                            rsort($yearsInDB);
                                            
                                            // Display options
                                            foreach ($yearsInDB as $year) {
                                                $selected = ($year == $editYear) ? 'selected' : '';
                                                echo '<option value="'.$year.'" '.$selected.'>'.$year.'</option>';
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="col-sm-12 col-md-3">
                                        <label for="week-select">Week:</label>
                                        <select id="week-select" name="week" class="form-control" onchange="updateURL()">
                                            <?php
                                            // Generate weeks 1-18 (typical NFL season length)
                                            for ($i = 1; $i <= 18; $i++) {
                                                $selected = ($i == $editWeek) ? 'selected' : '';
                                                echo '<option value="'.$i.'" '.$selected.'>Week '.$i.'</option>';
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Edit Form -->
            <form method="POST" action="editNewsletter.php">
                <input type="hidden" name="year" value="<?php echo $editYear; ?>">
                <input type="hidden" name="week" value="<?php echo $editWeek; ?>">
                
                <div class="row">
                    <div class="col-sm-12 col-lg-6">
                        <div class="card">
                            <div class="card-header">
                                <h4>Week <?php echo $editWeek; ?> Recap</h4>
                            </div>
                            <div class="card-body" style="background: #fff;">
                                <textarea name="recap" class="form-control" rows="20" style="direction: ltr;" placeholder="Enter the recap content for Week <?php echo $editWeek; ?>..."><?php echo htmlspecialchars($recap); ?></textarea>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-sm-12 col-lg-6">
                        <div class="card">
                            <div class="card-header">
                                <h4>Week <?php echo $editWeek; ?> Preview</h4>
                            </div>
                            <div class="card-body" style="background: #fff;">
                                <textarea name="preview" class="form-control" rows="20" style="direction: ltr;" placeholder="Enter the preview content for Week <?php echo $editWeek; ?>..."><?php echo htmlspecialchars($preview); ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-sm-12">
                        <div class="card">
                            <div class="card-body" style="background: #fff; text-align: center;">
                                <button type="submit" name="save" class="btn btn-success btn-lg" style="margin-right: 10px;">
                                    <i class="icon-checkmark"></i> Save Changes
                                </button>
                                <button type="button" class="btn btn-secondary btn-lg" onclick="window.location.href='newsletter.php'">
                                    <i class="icon-cross"></i> Cancel
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
            
        </div>
    </div>
</div>

<script>
function updateURL() {
    const year = document.getElementById('year-select').value;
    const week = document.getElementById('week-select').value;
    window.location.href = 'editNewsletter.php?year=' + year + '&week=' + week;
}
</script>

<?php include 'footer.php'; ?>
