<?php
// Start session before any output
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include functions for database access
include_once 'functions.php';

// Initialize variables
$recap = '';
$preview = '';
$editYear = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');
$editWeek = isset($_GET['week']) ? (int)$_GET['week'] : 1;

// Only process newsletter form if 'save' is set
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save'])) {
    $editYear = isset($_POST['year']) ? (int)$_POST['year'] : $editYear;
    $editWeek = isset($_POST['week']) ? (int)$_POST['week'] : $editWeek;
    $recap = isset($_POST['recap']) ? $_POST['recap'] : '';
    $preview = isset($_POST['preview']) ? $_POST['preview'] : '';
    $metadataImagePath = '';

    // Handle file upload if present
    if (isset($_FILES['metadata_image']) && $_FILES['metadata_image']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['metadata_image']['tmp_name'];
        $fileName = $_FILES['metadata_image']['name'];
        $fileSize = $_FILES['metadata_image']['size'];
        $fileType = $_FILES['metadata_image']['type'];
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowedExts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (in_array($fileExt, $allowedExts)) {
            $newFileName = "newsletter_{$editYear}_wk{$editWeek}.{$fileExt}";
            $destPath = "images/newsletter_metadata/" . $newFileName;
            if (move_uploaded_file($fileTmpPath, $destPath)) {
                $metadataImagePath = '/' . $destPath;
            }
        }
    }

    // Save newsletter
    // Check if record exists
    $existingQuery = query("SELECT id FROM newsletters WHERE year = $editYear AND week = $editWeek");
    $existingRow = fetch_array($existingQuery);
    if ($existingRow) {
        // Update existing record
        $updateQuery = "UPDATE newsletters SET recap = '" . SQLite3::escapeString($recap) . "', preview = '" . SQLite3::escapeString($preview) . "'";
        if ($metadataImagePath) {
            $updateQuery .= ", metadata_image = '" . SQLite3::escapeString($metadataImagePath) . "'";
        }
        $updateQuery .= " WHERE year = $editYear AND week = $editWeek";
        query($updateQuery);
    } else {
        // Insert new record
        $insertQuery = "INSERT INTO newsletters (year, week, recap, preview";
        $insertValues = "$editYear, $editWeek, '" . SQLite3::escapeString($recap) . "', '" . SQLite3::escapeString($preview) . "'";
        if ($metadataImagePath) {
            $insertQuery .= ", metadata_image";
            $insertValues .= ", '" . SQLite3::escapeString($metadataImagePath) . "'";
        }
        $insertQuery .= ") VALUES (" . $insertValues . ")";
        query($insertQuery);
    }

    // Redirect to newsletter page with the saved year and week
    header("Location: newsletter.php?year=$editYear&week=$editWeek");
    exit;
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

// Password protection for production
if (isset($APP_ENV) && $APP_ENV === 'production') {
    $showPage = false;
    if (isset($_SESSION['newsletter_pw']) && $_SESSION['newsletter_pw'] === 'suntown') {
        $showPage = true;
    } elseif (isset($_POST['newsletter_pw'])) {
        if ($_POST['newsletter_pw'] === 'suntown') {
            $_SESSION['newsletter_pw'] = 'suntown';
            $showPage = true;
        }
    }
    if (!$showPage) {
        include 'header.php';
        echo '<div class="app-content content"><div class="content-wrapper"><div class="content-body" style="direction: ltr;">';
        echo '<div class="row"><div class="col-sm-12"><div class="card"><div class="card-header"><h4>Password Required</h4></div><div class="card-body" style="background: #fff;">';
        echo '<form method="POST"><input type="password" name="newsletter_pw" placeholder="Enter password" class="form-control" style="max-width:300px;display:inline-block;margin-right:10px;" />';
        echo '<button type="submit" class="btn btn-primary">Submit</button>';
        echo '</form>';
        if (isset($_POST['newsletter_pw']) && $_POST['newsletter_pw'] !== 'suntown') {
            echo '<p style="color:red;margin-top:10px;">Incorrect password.</p>';
        }
        echo '</div></div></div></div></div></div></div>';
        include 'footer.php';
        exit;
    }
}

include 'sidebar.php';

?>

<div class="app-content content">
    <div class="content-wrapper">

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

            <!-- Week's Matchups Card -->
            <div class="row">
                <div class="col-sm-12">
                    <div class="card">
                        <div class="card-header" style="direction: ltr;">
                            <h4>Week <?php echo $editWeek; ?> Schedule</h4>
                        </div>
                        <div class="card-body" style="background: #fff;">
                            <?php
                            // Get schedule info for the selected year and week
                            if (!function_exists('getScheduleInfo')) {
                                include_once 'functions.php';
                            }
                            $scheduleInfo = getScheduleInfo($editYear, $editWeek);
                            ?>
                            <?php if (!empty($scheduleInfo)): ?>
                                <table id="datatable-schedule" class="table table-striped table-bordered table-responsive" style="direction: ltr;">
                                    <thead>
                                        <tr>
                                            <th>Manager 1</th>
                                            <th>Manager 2</th>
                                            <th>Regular Season H2H</th>
                                            <th>Postseason H2H</th>
                                            <th>Current Streak</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($scheduleInfo as $matchup): ?>
                                            <tr>
                                                <td>
                                                    <a href="profile.php?id=<?php echo urlencode($matchup['manager1']); ?>&versus=<?php echo urlencode($matchup['manager2_id']); ?>" target="_blank" rel="noopener">
                                                        <?php echo htmlspecialchars($matchup['manager1']); ?>
                                                    </a>
                                                </td>
                                                <td>
                                                    <a href="profile.php?id=<?php echo urlencode($matchup['manager2']); ?>&versus=<?php echo urlencode($matchup['manager1_id']); ?>" target="_blank" rel="noopener">
                                                        <?php echo htmlspecialchars($matchup['manager2']); ?>
                                                    </a>
                                                </td>
                                                <td><?php echo htmlspecialchars($matchup['record']); ?></td>
                                                <td><?php echo htmlspecialchars($matchup['postseason_record']); ?></td>
                                                <td><?php echo htmlspecialchars($matchup['streak']); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php else: ?>
                                <p>No schedule information available for Week <?php echo $editWeek; ?> of the <?php echo $editYear; ?> season.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Edit Form -->
            <form method="POST" action="editNewsletter.php" enctype="multipart/form-data">
                <input type="hidden" name="year" value="<?php echo $editYear; ?>">
                <input type="hidden" name="week" value="<?php echo $editWeek; ?>">
                
                <div class="row">
                    <div class="col-sm-12 col-lg-6">
                        <div class="card">
                            <div class="card-header">
                                <h4>Week <?php echo $editWeek - 1; ?> Recap</h4>
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

                <!-- Metadata Image Upload -->
                <div class="row">
                    <div class="col-sm-12 col-lg-6">
                        <div class="card">
                            <div class="card-header">
                                <h4>Metadata Image</h4>
                            </div>
                            <div class="card-body" style="background: #fff;">
                                <input type="file" name="metadata_image" id="metadata_image" accept="image/*" class="form-control">
                                <?php
                                // Show current image if exists
                                $imgQuery = query("SELECT metadata_image FROM newsletters WHERE year = $editYear AND week = $editWeek");
                                $imgRow = fetch_array($imgQuery);
                                if ($imgRow && !empty($imgRow['metadata_image'])) {
                                    echo '<div style="margin-top:10px;"><img src="' . htmlspecialchars($imgRow['metadata_image']) . '" alt="Current Metadata Image" style="max-width:200px;max-height:200px;" /></div>';
                                }
                                ?>
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
