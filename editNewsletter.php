<?php

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
    // Redirect to 404
    header("Location: /404.php");
    exit;
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
                                                        <a href="profile.php?id=<?php echo urlencode($matchup['manager1_clean'] ?? $matchup['manager1']); ?>&versus=<?php echo urlencode($matchup['manager2_id']); ?>" target="_blank" rel="noopener">
                                                            <?php echo htmlspecialchars($matchup['manager1']); ?>
                                                        </a>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if (isset($matchup['is_bye']) && $matchup['is_bye'] || empty($matchup['manager2_id'])): ?>
                                                        <?php echo htmlspecialchars($matchup['manager2']); ?>
                                                    <?php else: ?>
                                                        <a href="profile.php?id=<?php echo urlencode($matchup['manager2_clean'] ?? $matchup['manager2']); ?>&versus=<?php echo urlencode($matchup['manager1_id']); ?>" target="_blank" rel="noopener">
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
                                <textarea id="recap" name="recap" class="form-control" rows="20" style="direction: ltr;" placeholder="Enter the recap content for Week <?php echo $editWeek; ?>..."><?php echo htmlspecialchars($recap); ?></textarea>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-sm-12 col-lg-6">
                        <div class="card">
                            <div class="card-header">
                                <h4>Week <?php echo $editWeek; ?> Preview</h4>
                            </div>
                            <div class="card-body" style="background: #fff;">
                                <textarea id="preview" name="preview" class="form-control" rows="20" style="direction: ltr;" placeholder="Enter the preview content for Week <?php echo $editWeek; ?>..."><?php echo htmlspecialchars($preview); ?></textarea>
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
                                <button type="submit" name="save" class="btn btn-secondary btn-lg" style="margin-right: 10px;">
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


<!-- CKEditor 5 CDN integration for Recap and Preview fields (must be after all other scripts) -->
<link rel="stylesheet" href="https://cdn.ckeditor.com/ckeditor5/47.3.0/ckeditor5.css" crossorigin>
<script src="https://cdn.ckeditor.com/ckeditor5/47.3.0/ckeditor5.umd.js" crossorigin></script>
<script src="https://cdn.ckbox.io/ckbox/2.9.2/ckbox.js" crossorigin></script>

<script>
    $( document ).ready( () => {
        const {
            ClassicEditor,Autoformat,AutoImage,Autosave,BlockQuote,Bold,Emoji,
            Essentials,Heading,Indent,IndentBlock,Italic,Link,List,MediaEmbed,Mention,Paragraph,
		    Table,TableCaption,TableToolbar,TextTransformation,TodoList,Underline
        } = CKEDITOR;

        let plugins = [
            Autoformat,AutoImage,Autosave,BlockQuote,Bold,Emoji,
            Essentials,Heading,Indent,IndentBlock,Italic,Link,List,MediaEmbed,Mention,Paragraph,
		    Table,TableCaption,TableToolbar,TextTransformation,TodoList,Underline
        ];

        let toolbar = ['undo','redo','|',
            'heading','|',
            'bold','italic','underline','|',
            'emoji','link','mediaEmbed','insertTable','blockQuote','|',
            'bulletedList','numberedList','todoList','outdent','indent'
        ];

        ClassicEditor.create( $( '#recap' )[ 0 ], {
            licenseKey: '<?php echo $CKEDITOR_LICENSE; ?>',
            plugins: plugins,
            toolbar: toolbar
        })
        .catch( error => {
            console.error( 'Error initializing CKEditor 5:', error );
        });
        
        ClassicEditor.create( $( '#preview' )[ 0 ], {
            licenseKey: '<?php echo $CKEDITOR_LICENSE; ?>',
            plugins: plugins,
            toolbar: toolbar
        })
        .catch( error => {
            console.error( 'Error initializing CKEditor 5:', error );
        });
    } );
    
</script>
