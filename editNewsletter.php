<?php

include_once 'connections.php';
include_once 'functions.php';

// Initialize variables
$recap = '';
$preview = '';
$headline = '';
$notes = '';
$heroImagePath = '';
$editYear = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');
$editWeek = isset($_GET['week']) ? (int)$_GET['week'] : 1;

// Only process newsletter form if 'save' is set
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save'])) {
    $editYear = isset($_POST['year']) ? (int)$_POST['year'] : $editYear;
    $editWeek = isset($_POST['week']) ? (int)$_POST['week'] : $editWeek;
    $recap = isset($_POST['recap']) ? $_POST['recap'] : '';
    $preview = isset($_POST['preview']) ? $_POST['preview'] : '';
    $headline = isset($_POST['headline']) ? $_POST['headline'] : '';
    $notes = isset($_POST['notes']) ? $_POST['notes'] : '';
    $metadataImagePath = '';
    $heroImagePath = '';

    // Handle hero image upload
    if (isset($_FILES['hero_image']) && $_FILES['hero_image']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['hero_image']['tmp_name'];
        $fileName = $_FILES['hero_image']['name'];
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowedExts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (in_array($fileExt, $allowedExts)) {
            $newFileName = "newsletter_{$editYear}_wk{$editWeek}_hero.{$fileExt}";
            $destPath = "images/newsletter_metadata/" . $newFileName;
            if (move_uploaded_file($fileTmpPath, $destPath)) {
                $heroImagePath = '/' . $destPath;
            }
        }
    }

    // Handle metadata image upload
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
        $updateQuery = "UPDATE newsletters SET recap = '" . SQLite3::escapeString($recap) . "', preview = '" . SQLite3::escapeString($preview) . "', headline = '" . SQLite3::escapeString($headline) . "', notes = '" . SQLite3::escapeString($notes) . "', created_at = datetime('now')";
        if ($metadataImagePath) {
            $updateQuery .= ", metadata_image = '" . SQLite3::escapeString($metadataImagePath) . "'";
        }
        if (!empty($_POST['remove_hero_image'])) {
            $updateQuery .= ", hero_image = NULL";
        } elseif ($heroImagePath) {
            $updateQuery .= ", hero_image = '" . SQLite3::escapeString($heroImagePath) . "'";
        }
        $updateQuery .= " WHERE year = $editYear AND week = $editWeek";
        query($updateQuery);
    } else {
        // Insert new record
        $insertQuery = "INSERT INTO newsletters (year, week, recap, preview, headline, notes, created_at";
        $insertValues = "$editYear, $editWeek, '" . SQLite3::escapeString($recap) . "', '" . SQLite3::escapeString($preview) . "', '" . SQLite3::escapeString($headline) . "', '" . SQLite3::escapeString($notes) . "', datetime('now')";
        if ($metadataImagePath) {
            $insertQuery .= ", metadata_image";
            $insertValues .= ", '" . SQLite3::escapeString($metadataImagePath) . "'";
        }
        if ($heroImagePath) {
            $insertQuery .= ", hero_image";
            $insertValues .= ", '" . SQLite3::escapeString($heroImagePath) . "'";
        }
        $insertQuery .= ") VALUES (" . $insertValues . ")";
        query($insertQuery);
    }

    $saved = true;
}

// Fetch existing content if any
$contentQuery = query("SELECT recap, preview, headline, notes, hero_image FROM newsletters WHERE year = $editYear AND week = $editWeek");
$contentRow = fetch_array($contentQuery);
$existingHeroImage = null;
if ($contentRow) {
    $recap = $contentRow['recap'] ?? '';
    $preview = $contentRow['preview'] ?? '';
    $headline = $contentRow['headline'] ?? '';
    $notes = $contentRow['notes'] ?? '';
    $existingHeroImage = !empty($contentRow['hero_image']) ? $contentRow['hero_image'] : null;
}


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
                                <div class="row" style="direction: ltr;">
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

            <!-- Edit Form (wraps everything that saves) -->
            <form method="POST" action="admin.php?tab=newsletter" enctype="multipart/form-data" style="direction: ltr;">
                <input type="hidden" name="year" value="<?php echo $editYear; ?>">
                <input type="hidden" name="week" value="<?php echo $editWeek; ?>">

                <!-- Headline & Hero Image -->
                <div class="row" style="direction: ltr;">
                    <div class="col-sm-12">
                        <div class="card">
                            <div class="card-header" style="direction: ltr;">
                                <h4>Headline &amp; Hero Image</h4>
                            </div>
                            <div class="card-body" style="background: #fff; direction: ltr;">
                                <div class="row" style="direction: ltr;">
                                    <div class="col-sm-12">
                                        <label for="headline">Headline</label>
                                        <input type="text" id="headline" name="headline" class="form-control" value="<?php echo htmlspecialchars($headline); ?>" placeholder="Enter a headline for this newsletter edition...">
                                    </div>
                                </div>
                                <div class="row" style="direction: ltr; margin-top: 1rem;">
                                    <div class="col-sm-12">
                                        <label for="hero_image">Hero Image</label>
                                        <input type="file" id="hero_image" name="hero_image" class="form-control" accept="image/*">
                                        <?php if ($existingHeroImage): ?>
                                            <div style="margin-top: 0.75rem;">
                                                <p style="font-size: 0.8rem; color: #666; margin-bottom: 0.4rem;">Current hero image:</p>
                                                <img src="<?php echo htmlspecialchars($existingHeroImage); ?>" alt="Current hero image" style="max-width: 400px; max-height: 200px; object-fit: cover; border: 1px solid #ddd;">
                                                <div style="margin-top: 0.5rem;">
                                                    <label style="font-size: 0.85rem; color: #c00; cursor: pointer;">
                                                        <input type="checkbox" name="remove_hero_image" value="1"> Remove hero image
                                                    </label>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            <!-- Week's Matchups Card (inside form so hidden inputs are available) -->
            <div class="row">
                <div class="col-sm-12">
                    <div class="card">
                        <div class="card-header" style="direction: ltr;">
                            <h4>Week <?php echo $editWeek; ?> Schedule<?php if (isset($isPlayoffWeek) && $isPlayoffWeek): ?> (<?php echo $playoffRound; ?>)<?php endif; ?></h4>
                        </div>
                        <div class="card-body" style="background: #fff;">
                            <?php
                            // Get schedule info for the selected year and week
                            if (!function_exists('getScheduleInfo')) {
                                include_once 'functions.php';
                            }
                            
                            // Determine if this is a playoff week
                            $playoffStartWeek = ($editYear >= 2021) ? 15 : 14;
                            $isPlayoffWeek = ($editWeek >= $playoffStartWeek);
                            
                            if ($isPlayoffWeek) {
                                // Determine playoff round name
                                $weeksSincePlayoffStart = $editWeek - $playoffStartWeek;
                                switch ($weeksSincePlayoffStart) {
                                    case 0:
                                        $playoffRound = 'Quarterfinal';
                                        break;
                                    case 1:
                                        $playoffRound = 'Semifinal';
                                        break;
                                    case 2:
                                        $playoffRound = 'Final';
                                        break;
                                    default:
                                        $playoffRound = 'Playoff';
                                }
                                $scheduleInfo = getPlayoffScheduleInfo($editYear, $editWeek, $playoffRound);
                            } else {
                                $scheduleInfo = getScheduleInfo($editYear, $editWeek);
                            }
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
                                                    <?php if (isset($matchup['is_bye']) && $matchup['is_bye'] || empty($matchup['manager1_id'])): ?>
                                                        <?php echo htmlspecialchars($matchup['manager1']); ?>
                                                    <?php else: ?>
                                                                        <a href="profile.php?id=<?php echo urlencode($matchup['manager1_clean'] ?? $matchup['manager1']); ?>&versus=<?php echo urlencode($matchup['manager2_id']); ?>#head-to-head" target="_blank" rel="noopener">
                                                            <?php echo htmlspecialchars($matchup['manager1']); ?>
                                                        </a>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if (isset($matchup['is_bye']) && $matchup['is_bye'] || empty($matchup['manager2_id'])): ?>
                                                        <?php echo htmlspecialchars($matchup['manager2']); ?>
                                                    <?php else: ?>
                                                        <a href="profile.php?id=<?php echo urlencode($matchup['manager2_clean'] ?? $matchup['manager2']); ?>&versus=<?php echo urlencode($matchup['manager1_id']); ?>#head-to-head" target="_blank" rel="noopener">
                                                            <?php echo htmlspecialchars($matchup['manager2']); ?>
                                                        </a>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?php echo (isset($matchup['is_bye']) && $matchup['is_bye'] || empty($matchup['record'])) ? '—' : htmlspecialchars($matchup['record']); ?></td>
                                                <td><?php echo (isset($matchup['is_bye']) && $matchup['is_bye'] || empty($matchup['postseason_record'])) ? '—' : htmlspecialchars($matchup['postseason_record']); ?></td>
                                                <td><?php echo (isset($matchup['is_bye']) && $matchup['is_bye'] || empty($matchup['streak'])) ? '—' : htmlspecialchars($matchup['streak']); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php else: ?>
                                <p>No schedule information available for Week <?php echo $editWeek; ?> of the <?php echo $editYear; ?> season.
                                <?php if (isset($isPlayoffWeek) && $isPlayoffWeek): ?>
                                    <?php if ($playoffRound === 'Quarterfinal'): ?>
                                        This could be because the regular season standings are not yet available.
                                    <?php else: ?>
                                        Matchups will be determined based on previous round results.
                                    <?php endif; ?>
                                <?php endif; ?>
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

                <!-- Notes Card -->
                <div class="row" style="direction: ltr;">
                    <div class="col-sm-12">
                        <div class="card">
                            <div class="card-header" style="direction: ltr;">
                                <h4>Notes <small style="font-weight:normal;font-size:0.8rem;color:#999;">(internal only)</small></h4>
                            </div>
                            <div class="card-body" style="background: #fff; direction: ltr;">
                                <textarea id="newsletter-notes" name="notes" class="form-control" rows="10" style="direction: ltr;" placeholder="Internal notes for this week..."><?php echo htmlspecialchars($notes); ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row" style="direction: ltr;">
                    <div class="col-sm-12">
                        <div class="card">
                            <div class="card-header" style="direction: ltr;">
                                <h4>Week <?php echo $editWeek - 1; ?> Recap</h4>
                            </div>
                            <div class="card-body" style="background: #fff; direction: ltr;">
                                <textarea id="newsletter-recap" name="recap" class="form-control" rows="20" style="direction: ltr;" placeholder="Enter the recap content for Week <?php echo $editWeek; ?>..."><?php echo htmlspecialchars($recap); ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row" style="direction: ltr;">
                    <div class="col-sm-12">
                        <div class="card">
                            <div class="card-header" style="direction: ltr; display: flex; align-items: center; justify-content: space-between;">
                                <h4 style="margin: 0;">Week <?php echo $editWeek; ?> Preview</h4>
                                <button type="button" id="generate-preview-btn" class="btn btn-primary btn-sm">
                                    Generate with AI
                                </button>
                            </div>
                            <div class="card-body" style="background: #fff; direction: ltr;">
                                <div id="preview-ai-result" style="display: none; margin-bottom: 14px; padding: 12px 16px; background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 4px; direction: ltr;">
                                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
                                        <strong style="font-size: 0.85rem; color: #555;">AI-generated preview — edit then copy into the editor below</strong>
                                        <button type="button" id="copy-ai-preview-btn" class="btn btn-sm btn-outline-primary">Copy</button>
                                    </div>
                                    <pre id="preview-ai-text" style="white-space: pre-wrap; word-break: break-word; margin: 0; font-family: inherit; font-size: 14px; line-height: 1.7;"></pre>
                                </div>
                                <div id="preview-ai-error" style="display: none;" class="alert alert-danger"></div>
                                <textarea id="preview" name="preview" class="form-control" rows="20" style="direction: ltr;" placeholder="Enter the preview content for Week <?php echo $editWeek; ?>..."><?php echo htmlspecialchars($preview); ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row" style="direction: ltr;">
                    <div class="col-sm-12">
                        <div style="padding: 10px 0;">
                            <button type="submit" name="save" class="btn btn-primary" style="margin-right: 8px;">
                                <i class="icon-checkmark"></i> Save Changes
                            </button>
                            <button type="button" class="btn btn-primary" onclick="window.location.href='newsletter.php'">
                                <i class="icon-cross"></i> Cancel
                            </button>
                        </div>
                    </div>
                </div>
            </form>
            
        </div>
    </div>
</div>

<?php if (!empty($saved)): ?>
<!-- Success modal (fallback when SweetAlert is not available) -->
<div id="save-success-modal" style="display:none;position:fixed;inset:0;z-index:9999;background:rgba(0,0,0,0.45);align-items:center;justify-content:center;">
    <div style="background:#fff;border-radius:6px;padding:2rem 2.5rem;max-width:380px;width:90%;text-align:center;box-shadow:0 8px 32px rgba(0,0,0,0.2);">
        <div style="font-size:2.5rem;margin-bottom:0.5rem;">&#10003;</div>
        <h3 style="margin:0 0 0.5rem;font-size:1.2rem;">Saved successfully!</h3>
        <p style="color:#666;font-size:0.9rem;margin-bottom:1.5rem;">Newsletter Week <?php echo $editWeek; ?> &middot; <?php echo $editYear; ?></p>
        <div style="display:flex;gap:0.75rem;justify-content:center;">
            <a href="newsletter.php?year=<?php echo $editYear; ?>&week=<?php echo $editWeek; ?>" class="btn btn-primary">Preview</a>
            <button onclick="document.getElementById('save-success-modal').style.display='none';" class="btn btn-default">OK</button>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
function updateURL() {
    const year = document.getElementById('year-select').value;
    const week = document.getElementById('week-select').value;
    window.location.href = 'admin.php?tab=newsletter&year=' + year + '&week=' + week;
}

<?php if (!empty($saved)): ?>
(function() {
    var modal = document.getElementById('save-success-modal');
    modal.style.display = 'flex';
    modal.addEventListener('click', function(e) {
        if (e.target === modal) modal.style.display = 'none';
    });
})();
<?php endif; ?>
</script>
