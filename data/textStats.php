<?php
// Group-text stats page data layer.
//
// Parses the raw texts-Sept2026 chat export (a copy/paste of the group text
// thread) into per-sender message and word counts, for the hidden
// /textStats.php "who texts the most" chart.
//
// The export has no consistent machine-readable structure — it's a text
// screenshot dump. A sender's display name appears on its own line before
// their message(s), except for the phone owner (Tyler), whose own messages
// are never labeled. Reading it back out relies on a few format quirks:
//   - A blank line means an image/attachment with no caption, not a break.
//   - Timestamps, day headers, and "Read by ..." lines are boundaries — the
//     next message always gets a fresh name label (or none, if it's Tyler).
//   - Reactions ("Loved an image", a lone emoji, a tapback count) aren't
//     real messages and are dropped.
//   - Without a boundary, a sender's name is re-shown only when the sender
//     actually changes and changes back — e.g. AJ asks something, Tyler
//     replies inline with no separator, then "AJ Sartin" appears again for
//     AJ's next line. That repeat is the only signal that an unlabeled line
//     in between belongs to Tyler rather than being AJ's own follow-up, so
//     that's the split point used below (attributing the last line in the
//     run to Tyler, unless the run contains a shared link — link previews
//     span multiple lines and are kept together as the original sender's).
// This is a best-effort reconstruction, not an exact transcript — expect a
// handful of misattributed one-liners in ambiguous back-and-forth exchanges.

function getTextStatsData()
{
    $palette = getChartsManagerPalette();

    $senderToMid = [
        'Matt Reid'       => 4,
        'AJ Sartin'       => 2,
        'Andy Stamschror' => 6,
        'Justin Didier'   => 8,
        'Cole Boboth'     => 9,
        'Everett Boboth'  => 7,
        'Gavin Ohlde'     => 3,
        'Ben Bardell'     => 10,
    ];
    $selfMid = 1; // Tyler — never labeled in the export

    $names = [];
    $res = query("SELECT id, name FROM managers ORDER BY id");
    while ($row = fetch_array($res)) {
        $names[(int) $row['id']] = $row['name'];
    }

    $path = __DIR__ . '/../texts-Sept2026';
    if (!file_exists($path)) {
        return ['managers' => [], 'totalMessages' => 0, 'totalWords' => 0];
    }

    $rawLines = file($path, FILE_IGNORE_NEW_LINES);

    // The export has no year anywhere in it — recent-day headers are
    // weekday-only ("Friday · 7:26 PM"), older ones spell out the month
    // ("Friday, Sep 18 · 1:35 PM"). Both need a year to resolve to a real
    // date; take it from the filename (texts-Sept2026 -> 2026).
    $year = 2026;
    if (preg_match('/(\d{4})/', basename($path), $ym)) {
        $year = (int) $ym[1];
    }

    // Exported timestamps use a narrow no-break space (U+202F) before AM/PM,
    // not a plain space — both are matched here.
    $timeRe        = '/^\d{1,2}:\d{2}[\x{00A0}\x{202F}\s]?(AM|PM)$/u';
    $dayRe         = '/^([A-Za-z]+)(?:,\s*([A-Za-z]+)\s+(\d{1,2}))?\s*(?:\x{00b7}|·)\s*\d{1,2}:\d{2}[\x{00A0}\x{202F}\s]?(?:AM|PM)\s*$/u';
    $readByRe      = '/^Read by /';
    $deliveredRe   = '/^(Delivered|Sent)$/';
    $dotRe         = '/^(\x{00b7}|·)$/u';
    $reactionRe    = '/^(Laughed at|Loved|Liked|Disliked|Emphasized|Questioned)( an?)?( (image|message|video|link))?$/i';
    $reactedToRe   = '/^Reacted .+ to (an? )?(image|message|video|link)$/i';
    $emojiOnlyRe   = '/^[\x{1F300}-\x{1FAFF}\x{2600}-\x{27BF}\x{FE0F}\s]+$/u';
    $numberOnlyRe  = '/^\d+$/';
    $repliedRe     = '/^Replied to a message:$/';
    $domainRe      = '/^(www\.)?[a-z0-9\-]+\.(com|org|net|us|io)\/?.*$/i';
    $urlRe         = '/https?:\/\//i';

    // --- Tokenize into NAME / CONTENT / BOUNDARY, dropping noise ---
    // Day headers are also walked here to resolve the conversation's
    // timeframe: a weekday-only header ("Friday · ...") means "the next
    // Friday on or after the last known date," since headers only appear
    // moving forward through the export.
    $tokens = [];
    $skipNextAsQuote = false;
    $prevContentLine = null;
    $currentDate = null;
    $minDate = null;
    $maxDate = null;

    foreach ($rawLines as $raw) {
        $line = trim($raw);

        if ($skipNextAsQuote) {
            $skipNextAsQuote = false;
            continue;
        }
        if ($line === '') {
            continue; // image/attachment with no caption
        }
        if (isset($senderToMid[$line])) {
            $tokens[] = ['NAME', $senderToMid[$line]];
            $prevContentLine = null;
            continue;
        }
        if (preg_match($dayRe, $line, $dm)) {
            $weekday = $dm[1];
            if (!empty($dm[2])) {
                $currentDate = DateTime::createFromFormat('Y M j', "$year {$dm[2]} {$dm[3]}");
            } elseif ($currentDate !== null) {
                $probe = clone $currentDate;
                for ($k = 0; $k < 8; $k++) {
                    if ($probe->format('l') === $weekday) {
                        break;
                    }
                    $probe->modify('+1 day');
                }
                $currentDate = $probe;
            }
            if ($currentDate !== null) {
                $ymd = $currentDate->format('Y-m-d');
                if ($minDate === null || $ymd < $minDate) {
                    $minDate = $ymd;
                }
                if ($maxDate === null || $ymd > $maxDate) {
                    $maxDate = $ymd;
                }
            }
            $tokens[] = ['BOUNDARY', $line];
            $prevContentLine = null;
            continue;
        }
        if (preg_match($timeRe, $line) || preg_match($readByRe, $line)
            || preg_match($deliveredRe, $line) || preg_match($dotRe, $line)) {
            $tokens[] = ['BOUNDARY', $line];
            $prevContentLine = null;
            continue;
        }
        if (preg_match($repliedRe, $line)) {
            $skipNextAsQuote = true;
            continue;
        }
        if (preg_match($reactionRe, $line) || preg_match($reactedToRe, $line)) {
            continue;
        }
        if (preg_match($emojiOnlyRe, $line) && mb_strlen($line) <= 6) {
            continue;
        }
        if (preg_match($numberOnlyRe, $line)) {
            continue;
        }
        if (preg_match($domainRe, $line) && strpos($line, ' ') === false) {
            continue;
        }
        if ($line === $prevContentLine) {
            continue; // duplicate link-preview title line
        }

        $tokens[] = ['CONTENT', $line];
        $prevContentLine = $line;
    }

    // --- Walk tokens, resolving each content run to a sender ---
    $messages = []; // mid => [line, ...]
    $i = 0;
    $n = count($tokens);

    while ($i < $n) {
        [$kind, $val] = $tokens[$i];

        if ($kind === 'BOUNDARY') {
            $i++;
            continue;
        }

        if ($kind === 'NAME') {
            $owner = $val;
            $i++;
            $run = [];
            $j = $i;
            while ($j < $n && $tokens[$j][0] === 'CONTENT') {
                $run[] = $tokens[$j][1];
                $j++;
            }

            $sameNameRepeats = $j < $n && $tokens[$j][0] === 'NAME' && $tokens[$j][1] === $owner;
            if ($sameNameRepeats) {
                $hasUrl = false;
                foreach ($run as $c) {
                    if (preg_match($urlRe, $c)) {
                        $hasUrl = true;
                        break;
                    }
                }
                if (count($run) > 1 && !$hasUrl) {
                    // The trailing line is Tyler's unlabeled reply; the rest
                    // stays with the named sender (see file header comment).
                    $last = array_pop($run);
                    foreach ($run as $c) {
                        $messages[$owner][] = $c;
                    }
                    $messages[$selfMid][] = $last;
                } else {
                    foreach ($run as $c) {
                        $messages[$owner][] = $c;
                    }
                }
            } else {
                foreach ($run as $c) {
                    $messages[$owner][] = $c;
                }
            }
            $i = $j;
            continue;
        }

        // CONTENT with no preceding NAME in this run — Tyler's own message.
        $run = [];
        $j = $i;
        while ($j < $n && $tokens[$j][0] === 'CONTENT') {
            $run[] = $tokens[$j][1];
            $j++;
        }
        foreach ($run as $c) {
            $messages[$selfMid][] = $c;
        }
        $i = $j;
    }

    // --- Tally ---
    $managers = [];
    $totalMessages = 0;
    $totalWords = 0;

    foreach ($messages as $mid => $lines) {
        $messageCount = count($lines);
        $wordCount = 0;
        foreach ($lines as $line) {
            $wordCount += str_word_count($line, 0, "'’");
        }
        $managers[$mid] = [
            'name'     => $names[$mid] ?? "Manager $mid",
            'color'    => $palette[$mid] ?? '#9c68d9',
            'messages' => $messageCount,
            'words'    => $wordCount,
        ];
        $totalMessages += $messageCount;
        $totalWords    += $wordCount;
    }

    foreach ($managers as $mid => &$m) {
        $m['messagePct'] = $totalMessages > 0 ? round($m['messages'] / $totalMessages * 100, 1) : 0.0;
        $m['wordPct']    = $totalWords    > 0 ? round($m['words']    / $totalWords    * 100, 1) : 0.0;
    }
    unset($m);

    uasort($managers, fn($a, $b) => $b['messages'] <=> $a['messages']);

    $timeframe = null;
    if ($minDate !== null && $maxDate !== null) {
        $startDt = new DateTime($minDate);
        $endDt   = new DateTime($maxDate);
        $sameYear = $startDt->format('Y') === $endDt->format('Y');
        $timeframe = [
            'start' => $startDt->format('M j, Y'),
            'end'   => $endDt->format('M j, Y'),
            'label' => $sameYear
                ? $startDt->format('M j') . ' – ' . $endDt->format('M j, Y')
                : $startDt->format('M j, Y') . ' – ' . $endDt->format('M j, Y'),
            'days'  => $startDt->diff($endDt)->days + 1,
        ];
    }

    return [
        'managers'      => $managers,
        'totalMessages' => $totalMessages,
        'totalWords'    => $totalWords,
        'timeframe'     => $timeframe,
    ];
}
