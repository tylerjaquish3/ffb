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

    // Regular season W/L records and total points (from final regular-season week in standings)
    $records = [];
    $res = $conn->query("
        SELECT m.name, s.wins, s.losses, ROUND(s.points, 1) as pf
        FROM standings s
        JOIN managers m ON m.id = s.manager_id
        WHERE s.year = $year
          AND s.week = (SELECT MAX(week) FROM standings WHERE year = $year)
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

    // Build prompt
    $champion = '';
    $championName = '';
    $runnerUp = '';
    foreach ($standingsData as $s) {
        if ($s['finish'] == 1) {
            $champion = $s['name'] . ' ("' . $s['team_name'] . '")';
            $championName = $s['name'];
        }
        if ($s['finish'] == 2) {
            $runnerUp = $s['name'];
        }
    }

    // Top 3 players on the champion's team (starters only, by total points)
    $topPlayers = [];
    if ($championName !== '') {
        $escChamp = SQLite3::escapeString($championName);
        $res = $conn->query("
            SELECT player, position, ROUND(SUM(points), 1) as total_points
            FROM rosters
            WHERE year = $year
              AND manager = '$escChamp'
              AND roster_spot NOT IN ('BN', 'IR')
            GROUP BY player
            ORDER BY total_points DESC
            LIMIT 3
        ");
        while ($row = $res->fetchArray(SQLITE3_ASSOC)) {
            $topPlayers[] = $row;
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

    if (!empty($topPlayers)) {
        $prompt .= "\nChampion's Top 3 Players (starter points only):\n";
        foreach ($topPlayers as $p) {
            $prompt .= "  - {$p['player']} ({$p['position']}) — {$p['total_points']} pts\n";
        }
    }

    $prompt .= "\nInstructions: Keep it under 300 words. Write in a fun, friendly tone — like you're texting the group chat. ";
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
                        <div class="card-body" style="background: #fff; padding: 20px 24px;">
                            <form method="GET" id="yearForm">
                                <div style="display: flex; flex-wrap: wrap; align-items: center; gap: 12px; direction: ltr;">
                                    <label for="year-select" style="margin: 0; font-weight: 600; line-height: 38px;">Season</label>
                                    <select id="year-select" name="year" class="form-control" style="width: auto; height: 38px; padding: 0 12px; margin: 0;" onchange="this.form.submit()">
                                        <?php foreach ($availableYears as $yr): ?>
                                            <option value="<?php echo $yr; ?>"<?php if ($yr == $recapYear) echo ' selected'; ?>><?php echo $yr; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <a href="/seasonRecaps.php?id=<?php echo $recapYear; ?>" target="_blank" class="btn btn-primary" style="height: 38px; line-height: 1; padding: 0 16px; display: inline-flex; align-items: center;">
                                        View <?php echo $recapYear; ?> Season Page
                                    </a>
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

            <!-- Saved / Manual recap -->
            <div class="row" style="direction: ltr;">
                <div class="col-sm-12">
                    <div class="card">
                        <div class="card-header">
                            <h4><?php echo $savedRecap ? "Saved Recap — $recapYear" : "Manual Recap — $recapYear"; ?></h4>
                        </div>
                        <div class="card-body" style="background: #fff; padding: 20px 24px;">
                            <form method="POST" action="generateSeasonRecap.php" style="direction: ltr;">
                                <input type="hidden" name="year" value="<?php echo $recapYear; ?>">
                                <input type="hidden" name="action" value="save">
                                <label for="manual-recap" style="display: block; margin-bottom: 6px; font-weight: 600;">Recap Text</label>
                                <textarea id="manual-recap" name="recap_text" class="form-control" rows="8" style="direction: ltr; line-height: 1.7; font-size: 14px;"><?php echo htmlspecialchars($savedRecap); ?></textarea>
                                <button type="submit" class="btn btn-primary" style="margin-top: 14px;">
                                    <i class="icon-checkmark"></i> Save Recap
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- AI generator -->
            <div class="row" style="direction: ltr;">
                <div class="col-sm-12">
                    <div class="card">
                        <div class="card-header">
                            <h4>AI Generator</h4>
                        </div>
                        <div class="card-body" style="background: #fff; padding: 20px 24px; direction: ltr;">

                            <form method="POST" action="generateSeasonRecap.php" style="margin-bottom: 0;">
                                <input type="hidden" name="year" value="<?php echo $recapYear; ?>">
                                <input type="hidden" name="action" value="generate">
                                <button type="submit" class="btn btn-primary">
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
                                    <label style="display: block; margin-bottom: 6px; font-weight: 600;">Generated text — edit before saving</label>
                                    <textarea name="recap_text" class="form-control" rows="6" style="direction: ltr; line-height: 1.7; font-size: 14px;"><?php echo htmlspecialchars($generatedText); ?></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="icon-checkmark"></i> Save Recap
                                </button>
                                <a href="generateSeasonRecap.php?year=<?php echo $recapYear; ?>" class="btn btn-primary" style="margin-left: 8px;">Discard</a>
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
