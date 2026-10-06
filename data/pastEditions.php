<?php
// Past Editions page data layer.
//
// Lists every published newsletter edition (regular season + playoffs),
// grouped by year, newest first. Depends on query()/fetch_array() from
// functions.php.

// Round label for a playoff week, matching the switch in
// newsletter.php/playoffNewsletter.php's edition selector.
function getPlayoffRoundLabel($week, $playoffStartWeek)
{
    switch ($week - $playoffStartWeek) {
        case 0: return 'Quarterfinal';
        case 1: return 'Semifinal';
        case 2: return 'Final';
        default: return null;
    }
}

// All published editions, newest first within each year, grouped by year.
// Each edition has: week, headline, created_at, isPlayoff, roundLabel, url.
function getPastEditions()
{
    $editions = [];

    $result = query("SELECT year, week, headline, created_at FROM newsletters WHERE published = 1 ORDER BY year DESC, week DESC");
    while ($row = fetch_array($result)) {
        $year = (int) $row['year'];
        $week = (int) $row['week'];
        $playoffStartWeek = ($year >= 2021) ? 15 : 14;
        $isPlayoff = $week >= $playoffStartWeek;

        $editions[$year][] = [
            'week'       => $week,
            'headline'   => $row['headline'],
            'created_at' => $row['created_at'],
            'isPlayoff'  => $isPlayoff,
            'roundLabel' => $isPlayoff ? getPlayoffRoundLabel($week, $playoffStartWeek) : null,
            'url'        => ($isPlayoff ? 'playoffNewsletter.php' : 'newsletter.php') . '?year=' . $year . '&week=' . $week,
        ];
    }

    return $editions;
}
