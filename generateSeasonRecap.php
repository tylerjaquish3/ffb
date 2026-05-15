<?php

include_once 'connections.php';

// Local-only admin tool
if (isset($APP_ENV) && $APP_ENV === 'production') {
    header("Location: /404.php");
    exit;
}

include_once 'functions.php';

$recapYear = isset($_GET['year']) ? (int)$_GET['year'] : (int)(date('Y') - 1);
$generatedText = '';
$savedRecap = '';
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $recapYear = isset($_POST['year']) ? (int)$_POST['year'] : $recapYear;
    $action = $_POST['action'] ?? '';

    if ($action === 'generate') {
        $promptText = buildSeasonPromptData($recapYear, $conn);
        $generatedText = callGeminiApi($promptText, $GEMINI_API_KEY);
    } elseif ($action === 'save') {
        $recapText = trim($_POST['recap_text'] ?? '');
        if (!empty($recapText)) {
            $escaped = SQLite3::escapeString($recapText);
            $existingId = $conn->querySingle("SELECT id FROM season_recaps WHERE year = $recapYear");
            if ($existingId) {
                $conn->exec("UPDATE season_recaps SET recap = '$escaped', created_at = CURRENT_TIMESTAMP WHERE year = $recapYear");
            } else {
                $conn->exec("INSERT INTO season_recaps (year, recap) VALUES ($recapYear, '$escaped')");
            }
            $message = "Recap saved for $recapYear.";
            $savedRecap = $recapText;
        }
    }
}

if (empty($savedRecap)) {
    $existing = $conn->querySingle("SELECT recap FROM season_recaps WHERE year = $recapYear", true);
    if ($existing) {
        $savedRecap = $existing['recap'];
    }
}

$yearsResult = $conn->query("SELECT DISTINCT year FROM finishes ORDER BY year DESC");
$availableYears = [];
while ($yr = $yearsResult->fetchArray(SQLITE3_ASSOC)) {
    $availableYears[] = $yr['year'];
}

function buildSeasonPromptData($year, $conn) {
    // Final standings with team names
    $standingsData = [];
    $res = $conn->query("
        SELECT m.name, f.finish, COALESCE(tn.name, 'Unknown') as team_name
        FROM finishes f
        JOIN managers m ON m.id = f.manager_id
        LEFT JOIN team_names tn ON tn.manager_id = m.id AND tn.year = f.year
        WHERE f.year = $year
        ORDER BY f.finish ASC
    ");
    while ($row = $res->fetchArray(SQLITE3_ASSOC)) {
        $standingsData[] = $row;
    }

    // Regular season champion = seed 1 in playoff_matchups
    $regSeasonChamp = $conn->querySingle("
        SELECT m.name FROM managers m
        WHERE m.id = (
            SELECT CASE WHEN manager1_seed = 1 THEN manager1_id ELSE manager2_id END
            FROM playoff_matchups
            WHERE year = $year AND (manager1_seed = 1 OR manager2_seed = 1)
            LIMIT 1
        )
    ") ?? '';

    if (empty($standingsData)) {
        return "No season data found for $year.";
    }

    // Regular season W/L records and total points
    $records = [];
    $res = $conn->query("
        SELECT m.name,
            SUM(CASE WHEN rsm.winning_manager_id = m.id THEN 1 ELSE 0 END) as wins,
            SUM(CASE WHEN rsm.winning_manager_id != m.id THEN 1 ELSE 0 END) as losses,
            ROUND(SUM(CASE WHEN rsm.manager1_id = m.id THEN rsm.manager1_score
                          WHEN rsm.manager2_id = m.id THEN rsm.manager2_score
                          ELSE 0 END), 1) as pf
        FROM managers m
        JOIN finishes f ON f.manager_id = m.id AND f.year = $year
        LEFT JOIN regular_season_matchups rsm ON rsm.year = $year
            AND (rsm.manager1_id = m.id OR rsm.manager2_id = m.id)
        GROUP BY m.id
        ORDER BY pf DESC
    ");
    while ($row = $res->fetchArray(SQLITE3_ASSOC)) {
        $records[$row['name']] = $row;
    }

    // Highest single-game score and who scored it
    $highScoreRow = $conn->querySingle("
        SELECT m.name, t.score
        FROM (
            SELECT manager1_id as mgr_id, manager1_score as score FROM regular_season_matchups WHERE year = $year
            UNION ALL
            SELECT manager2_id, manager2_score FROM regular_season_matchups WHERE year = $year
        ) t
        JOIN managers m ON m.id = t.mgr_id
        ORDER BY t.score DESC
        LIMIT 1
    ", true);

    // Trade count
    $tradeCount = $conn->querySingle("SELECT COUNT(DISTINCT trade_identifier) FROM trades WHERE year = $year") ?? 0;

    // Build prompt
    $champion = '';
    $runnerUp = '';
    foreach ($standingsData as $s) {
        if ($s['finish'] == 1) {
            $champion = $s['name'] . ' ("' . $s['team_name'] . '")';
        }
        if ($s['finish'] == 2) {
            $runnerUp = $s['name'];
        }
    }

    $prompt  = "Write a fun, casual one-paragraph recap of the $year Suntown Fantasy Football League season. ";
    $prompt .= "This is a 10-manager fantasy football league between friends who've been playing together for years.\n\n";
    $prompt .= "Season Data:\n";
    $prompt .= "Champion: $champion\n";
    $prompt .= "Runner-Up: $runnerUp\n";
    $prompt .= "Regular Season Champion (best record/seed): $regSeasonChamp\n\n";
    $prompt .= "Final Standings:\n";

    foreach ($standingsData as $s) {
        $name = $s['name'];
        $rec  = $records[$name] ?? null;
        $wl   = $rec ? "{$rec['wins']}-{$rec['losses']}" : '?-?';
        $pf   = $rec ? $rec['pf'] : '?';
        $prompt .= "  {$s['finish']}. {$name} ({$s['team_name']}) — {$wl}, {$pf} pts\n";
    }

    if ($highScoreRow) {
        $prompt .= "\nHighest Single-Game Score: {$highScoreRow['score']} pts ({$highScoreRow['name']})\n";
    }

    $prompt .= "Total Trades Made: $tradeCount\n\n";
    $prompt .= "Instructions: Keep it under 300 words. Write in a fun, friendly tone — like you're texting the group chat. ";
    $prompt .= "Mention the champion, the top scorer, and a few interesting storylines from the data. Use first names only.";

    return $prompt;
}

function callGeminiApi($prompt, $apiKey) {
    if (empty($apiKey)) {
        return '[No API key set — add $GEMINI_API_KEY to connections.php. Get a free key at https://aistudio.google.com/app/apikey]';
    }

    if (!function_exists('curl_init')) {
        return '[curl is not available on this server]';
    }

    $url  = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=' . urlencode($apiKey);
    $body = json_encode([
        'contents'         => [['parts' => [['text' => $prompt]]]],
        'generationConfig' => [
            'maxOutputTokens' => 1024,
            'temperature'     => 0.85,
            'thinkingConfig'  => ['thinkingBudget' => 0],
        ],
    ]);

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $body,
        CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
        CURLOPT_TIMEOUT        => 30,
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if (!$response || $httpCode !== 200) {
        $err    = json_decode($response, true);
        $errMsg = $err['error']['message'] ?? "HTTP $httpCode — raw: $response";
        return "[Gemini API error: $errMsg]";
    }

    $data = json_decode($response, true);
    return trim($data['candidates'][0]['content']['parts'][0]['text'] ?? '[No text in response]');
}

$pageName = "Generate Season Recap";
include 'header.php';
include 'sidebar.php';
?>

<div class="app-content content">
    <div class="content-wrapper">
        <div class="content-body">

            <!-- Year selector -->
            <div class="row" style="direction: ltr;">
                <div class="col-sm-12">
                    <div class="card">
                        <div class="card-header">
                            <h4>Generate Season Recap</h4>
                        </div>
                        <div class="card-body" style="background: #fff;">
                            <form method="GET" id="yearForm">
                                <div class="row align-items-end">
                                    <div class="col-sm-12 col-md-3">
                                        <label for="year-select">Season:</label>
                                        <select id="year-select" name="year" class="form-control" onchange="this.form.submit()">
                                            <?php foreach ($availableYears as $yr): ?>
                                                <option value="<?php echo $yr; ?>"<?php if ($yr == $recapYear) echo ' selected'; ?>><?php echo $yr; ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-sm-12 col-md-3" style="margin-top: 10px;">
                                        <a href="/seasonRecaps.php?id=<?php echo $recapYear; ?>" target="_blank" class="btn btn-secondary btn-sm">
                                            View <?php echo $recapYear; ?> Season Page
                                        </a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <?php if ($message): ?>
            <div class="row" style="direction: ltr;">
                <div class="col-sm-12">
                    <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Saved recap -->
            <?php if ($savedRecap): ?>
            <div class="row" style="direction: ltr;">
                <div class="col-sm-12">
                    <div class="card">
                        <div class="card-header">
                            <h4>Saved Recap — <?php echo $recapYear; ?></h4>
                        </div>
                        <div class="card-body" style="background: #fff;">
                            <p style="line-height: 1.8; margin: 0;"><?php echo nl2br(htmlspecialchars($savedRecap)); ?></p>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- AI generator -->
            <div class="row" style="direction: ltr;">
                <div class="col-sm-12">
                    <div class="card">
                        <div class="card-header">
                            <h4>AI Generator</h4>
                        </div>
                        <div class="card-body" style="background: #fff;">

                            <form method="POST" action="generateSeasonRecap.php" style="margin-bottom: 0;">
                                <input type="hidden" name="year" value="<?php echo $recapYear; ?>">
                                <input type="hidden" name="action" value="generate">
                                <button type="submit" class="btn btn-secondary">
                                    Generate Recap for <?php echo $recapYear; ?>
                                </button>
                                <small style="margin-left: 10px; color: #888;">Uses Gemini 2.5 Flash (free tier)</small>
                            </form>

                            <?php if ($generatedText): ?>
                            <hr style="margin: 20px 0;">
                            <form method="POST" action="generateSeasonRecap.php">
                                <input type="hidden" name="year" value="<?php echo $recapYear; ?>">
                                <input type="hidden" name="action" value="save">
                                <div style="margin-bottom: 12px;">
                                    <label><strong>Generated text — edit before saving:</strong></label>
                                    <textarea name="recap_text" class="form-control" rows="6" style="margin-top: 8px; direction: ltr;"><?php echo htmlspecialchars($generatedText); ?></textarea>
                                </div>
                                <button type="submit" class="btn btn-success">
                                    <i class="icon-checkmark"></i> Save Recap
                                </button>
                                <a href="generateSeasonRecap.php?year=<?php echo $recapYear; ?>" class="btn btn-secondary" style="margin-left: 8px;">Discard</a>
                            </form>
                            <?php endif; ?>

                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
