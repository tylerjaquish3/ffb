<!-- Win/lose streaks -->
<table class="table" id="datatable-misc1">
	<thead>
		<th>Manager</th>
		<th>Longest Win Streak</th>
		<th>Longest Lose Streak</th>
	</thead>
	<tbody>
		<?php
		$managers = '';
		$result2 = query("SELECT * FROM managers");
		while ($manager = fetch_array($result2)) {

			$managerId = $manager['id'];

			$winStreak = $loseStreak = $longestWinStreak = $longestLoseStreak = 0;
			$result = query("SELECT name, w.year, w.week_number, win, lose
	            FROM managers
	            JOIN regular_season_matchups rsm ON managers.id = rsm.manager1_id
	            JOIN (
	                SELECT SUM(CASE
	                WHEN manager1_id = $managerId AND manager1_score > manager2_score THEN 1
	                ELSE 0
	                END) AS win, year, week_number
	                FROM regular_season_matchups
	                GROUP BY year, week_number
	            ) w ON w.year = rsm.year AND w.week_number = rsm.week_number

	            JOIN (
	                SELECT SUM(CASE
	                WHEN manager1_id = $managerId AND manager1_score < manager2_score THEN 1
	                ELSE 0
	                END) AS lose, year, week_number
	                FROM regular_season_matchups
	                GROUP BY year, week_number
	            ) w2 ON w2.year = rsm.year AND w2.week_number = rsm.week_number
	            WHERE name = '" . $manager['name'] . "'");
			while ($row = fetch_array($result)) {
				if ($row['win'] == 1) {
					$winStreak++;
				} else {
					$longestWinStreak = ($winStreak > $longestWinStreak) ? $winStreak : $longestWinStreak;
					$winStreak = 0;
				}

				if ($row['lose'] == 1) {
					$loseStreak++;
				} else {
					$longestLoseStreak = ($loseStreak > $longestLoseStreak) ? $loseStreak : $longestLoseStreak;
					$loseStreak = 0;
				}
			}

			$response[] = [
				'manager' => $manager['name'],
				'winStreak' => $longestWinStreak,
				'loseStreak' => $longestLoseStreak
			];
		}

		foreach ($response as $row) { ?>
			<tr>
				<td><?php echo $row['manager']; ?></td>
				<td><?php echo $row['winStreak']; ?></td>
				<td><?php echo $row['loseStreak']; ?></td>
			</tr>

		<?php } ?>
	</tbody>
	<tfoot>
		<tr>
			<td colspan=3>Streaks span across seasons</td>
		</tr>
	</tfoot>
</table>
<!-- Total Points -->
<table class="table" id="datatable-misc2" style="display:none;">
	<thead>
		<th>Manager</th>
		<th>Points For</th>
		<th>Points Against</th>
		<th>Difference</th>
	</thead>
	<tbody>
		<?php
		$result = query("SELECT managers.name, SUM(manager1_score) AS points_for,
			SUM(manager2_score) AS points_against, SUM(manager1_score) - SUM(manager2_score) AS diff
			FROM regular_season_matchups rsm
			JOIN managers ON managers.id = rsm.manager1_id
			GROUP BY manager1_id");
		while ($row = fetch_array($result)) { ?>
			<tr>
				<td><?php echo $row['name']; ?></td>
				<td><?php echo number_format($row['points_for'], 2, '.', ','); ?></td>
				<td><?php echo number_format($row['points_against'], 2, '.', ','); ?></td>
				<td><?php echo number_format($row['diff'], 2, '.', ','); ?></td>
			</tr>

		<?php } ?>
	</tbody>
	<tfoot>
		<tr>
			<td colspan=4>Total points over the course of our league history</td>
		</tr>
	</tfoot>
</table>
<!-- Season points -->
<table class="table" id="datatable-misc3" style="display:none;">
	<thead>
		<th>Manager</th>
		<th>Most PF</th>
		<th>Least PF</th>
		<th>Most PA</th>
		<th>Least PA</th>
	</thead>
	<tbody>
		<?php
		$result = query("SELECT name, MAX(points_for) as max_pf, MAX(points_against) as max_pa,
			MIN(points_for) as min_pf, MIN(points_against) as min_pa
			FROM (
				SELECT managers.name, year, SUM(manager1_score) AS points_for,
				SUM(manager2_score) AS points_against
				FROM regular_season_matchups rsm
				JOIN managers ON managers.id = rsm.manager1_id
				GROUP BY manager1_id, year
			) as all_years
			GROUP BY name");
		while ($row = fetch_array($result)) { ?>
			<tr>
				<td><?php echo $row['name']; ?></td>
				<td><?php echo number_format($row['max_pf'], 2, '.', ','); ?></td>
				<td><?php echo number_format($row['min_pf'], 2, '.', ','); ?></td>
				<td><?php echo number_format($row['max_pa'], 2, '.', ','); ?></td>
				<td><?php echo number_format($row['min_pa'], 2, '.', ','); ?></td>
			</tr>

		<?php } ?>
	</tbody>
	<tfoot>
		<tr>
			<td colspan=5>Min and max points for and against for a season</td>
		</tr>
	</tfoot>
</table>
<!-- Average PF/PA -->
<table class="table" id="datatable-misc4" style="display:none;">
	<thead>
		<th>Manager</th>
		<th>Avg PF</th>
		<th>Avg PA</th>
		<th>Difference</th>
	</thead>
	<tbody>
		<?php
		$result = query("SELECT managers.name, AVG(manager1_score) as avg_pf, AVG(manager2_score) as avg_pa,
			AVG(manager1_score) - AVG(manager2_score) as diff
			FROM regular_season_matchups rsm
			JOIN managers ON managers.id = rsm.manager1_id
			GROUP BY manager1_id");
		while ($row = fetch_array($result)) { ?>
			<tr>
				<td><?php echo $row['name']; ?></td>
				<td><?php echo number_format($row['avg_pf'], 2, '.', ','); ?></td>
				<td><?php echo number_format($row['avg_pa'], 2, '.', ','); ?></td>
				<td><?php echo number_format($row['diff'], 2, '.', ','); ?></td>
			</tr>

		<?php } ?>
	</tbody>
	<tfoot>
		<tr>
			<td colspan=4>Average weekly points for and against</td>
		</tr>
	</tfoot>
</table>
<!-- Start/end streaks -->
<table class="table" id="datatable-misc5" style="display:none;">
	<thead>
		<th>Manager</th>
		<th>Best Start</th>
		<th>Year</th>
		<th>Finish</th>
		<th>Worst Start</th>
		<th>Year</th>
		<th>Finish</th>
	</thead>
	<tbody>
		<?php
		$longestWin = $longestLose = 0;
        $longestWina = $longestLosea = [];

        for ($x = 1; $x < 11; $x++) {
            
			$years = [];
			$result = query("SELECT * FROM regular_season_matchups
				WHERE manager1_id = $x and winning_manager_id = $x
				order by year asc, week_number asc");
			while ($row = fetch_array($result)) {
				$years[$row['year']][] = $row;
			};
			
            // manager win streak
            $myStreakWin = $myLongestWin = 0;
            foreach ($years as $y => $weeks) {
                $lastWeek = 0;
                foreach ($weeks as $w) {
                    if ($w['week_number'] == 1) {
                        $myStreakWin  = 0;
                    }
                    if ($w['week_number'] == ($lastWeek + 1)) {
                        $myStreakWin++;
                        if ($myStreakWin > $myLongestWin) {
                            $myLongestWin = $myStreakWin;
							$winYear = $y;
                        }
                        $lastWeek = $w['week_number'];
                    } else {
                        break; // go to next year
                    }
                }
            }
            
			$years = [];
            // manager lose streak
            $result = query("SELECT * FROM regular_season_matchups
				WHERE manager1_id = $x and losing_manager_id = $x
				order by year asc, week_number asc");
			while ($row = fetch_array($result)) {
				$years[$row['year']][] = $row;
			};

            $myStreakLose = $myLongestLose = 0;
            foreach ($years as $y => $weeks) {
                $lastWeek = 0;
                foreach ($weeks as $w) {
                    if ($w['week_number'] == 1) {
                        $myStreakLose  = 0;
                    }
                    if ($w['week_number'] == ($lastWeek + 1)) {
                        $myStreakLose++;
                        if ($myStreakLose > $myLongestLose) {
                            $myLongestLose = $myStreakLose;
							$loseYear = $y;
                        }
                        $lastWeek = $w['week_number'];
                    } else {
                        break; // go to next year
                    }
                }
            }

			$result2 = query("SELECT * FROM managers WHERE id = $x");
			while ($row2 = fetch_array($result2)) {
				$manager = $row2['name'];
			}

			$result3 = query("SELECT * FROM finishes WHERE manager_id = $x AND year = $winYear");
			while ($row3 = fetch_array($result3)) {
				$winYearFinish = $row3['finish'];
			}

			$result4 = query("SELECT * FROM finishes WHERE manager_id = $x AND year = $loseYear");
			while ($row4 = fetch_array($result4)) {
				$loseYearFinish = $row4['finish'];
			}

			$startStreaks[] = [
				'manager' => $manager,
				'winStreak' => $myLongestWin . ' - 0',
				'winYear' => $winYear,
				'winYearFinish' => $winYearFinish,
				'loseStreak' => '0 - ' . $myLongestLose,
				'loseYear' => $loseYear,
				'loseYearFinish' => $loseYearFinish
			];
        }

		foreach ($startStreaks as $row) { ?>
			<tr>
				<td><?php echo $row['manager']; ?></td>
				<td><?php echo $row['winStreak']; ?></td>
				<td><?php echo $row['winYear']; ?></td>
				<td><?php echo $row['winYearFinish']; ?></td>
				<td><?php echo $row['loseStreak']; ?></td>
				<td><?php echo $row['loseYear']; ?></td>
				<td><?php echo $row['loseYearFinish']; ?></td>
			</tr>

		<?php } ?>
	</tbody>
	<tfoot>
		<tr>
			<td colspan=7>Longest winning or losing streak to start a season and how it ended</td>
		</tr>
	</tfoot>
</table>
<!-- Win/loss margin -->
<table class="table" id="datatable-misc6" style="display:none;">
	<thead>
		<th>Manager</th>
		<th>Biggest Win</th>
		<th>Smallest Win</th>
		<th>Biggest Loss</th>
		<th>Smallest Loss</th>
	</thead>
	<tbody>
		<?php
		$result = query("SELECT managers.name, MAX(manager1_score - manager2_score) as biggest_win,
			MAX(manager2_score - manager1_score) as biggest_loss,
			MIN(IF (manager1_score > manager2_score, manager1_score - manager2_score, null)) as smallest_win,
			MIN(IF (manager1_score < manager2_score, manager2_score - manager1_score, null)) as smallest_loss
			FROM regular_season_matchups rsm
			JOIN managers ON managers.id = rsm.manager1_id
			GROUP BY manager1_id");
		while ($row = fetch_array($result)) { ?>
			<tr>
				<td><?php echo $row['name']; ?></td>
				<td><?php echo number_format($row['biggest_win'], 2, '.', ','); ?></td>
				<td><?php echo number_format($row['smallest_win'], 2, '.', ','); ?></td>
				<td><?php echo number_format($row['biggest_loss'], 2, '.', ','); ?></td>
				<td><?php echo number_format($row['smallest_loss'], 2, '.', ','); ?></td>
			</tr>

		<?php } ?>
	</tbody>
	<tfoot>
		<tr>
			<td colspan=5>Min and max margin of victory and defeat</td>
		</tr>
	</tfoot>
</table>
<!-- Weekly points -->
<table class="table" id="datatable-misc7" style="display:none;">
	<thead>
		<th>Manager</th>
		<th>Most PF</th>
		<th>Least PF</th>
		<th>Most PA</th>
		<th>Least PA</th>
	</thead>
	<tbody>
		<?php
		$result = query("SELECT managers.name, MAX(manager1_score) as max_pf, MAX(manager2_score) as max_pa,
			MIN(manager1_score) as min_pf, MIN(manager2_score) as min_pa
			FROM regular_season_matchups rsm
			JOIN managers ON managers.id = rsm.manager1_id
			GROUP BY manager1_id");
		while ($row = fetch_array($result)) { ?>
			<tr>
				<td><?php echo $row['name']; ?></td>
				<td><?php echo number_format($row['max_pf'], 2, '.', ','); ?></td>
				<td><?php echo number_format($row['min_pf'], 2, '.', ','); ?></td>
				<td><?php echo number_format($row['max_pa'], 2, '.', ','); ?></td>
				<td><?php echo number_format($row['min_pa'], 2, '.', ','); ?></td>
			</tr>

		<?php } ?>
	</tbody>
	<tfoot>
		<tr>
			<td colspan=5>Regular season min and max points for and points against</td>
		</tr>
	</tfoot>
</table>
<!-- Losses with Top 3 points -->
<table class="table" id="datatable-misc8" style="display:none;">
	<thead>
		<th>Manager</th>
		<th>Wks with Top 3 Pts</th>
		<th>Losses with Top 3 Pts</th>
		<th>Pct</th>
	</thead>
	<tbody>
		<?php
		$year = $week = 0;
		$index = -1;
		$first = true;
		$managers = [
			'AJ' => [
				'top' => 0,
				'losses' => 0
			],
			'Ben' => [
				'top' => 0,
				'losses' => 0
			],
			'Tyler' => [
				'top' => 0,
				'losses' => 0
			],
			'Matt' => [
				'top' => 0,
				'losses' => 0
			],
			'Justin' => [
				'top' => 0,
				'losses' => 0
			],
			'Andy' => [
				'top' => 0,
				'losses' => 0
			],
			'Cole' => [
				'top' => 0,
				'losses' => 0
			],
			'Everett' => [
				'top' => 0,
				'losses' => 0
			],
			'Cameron' => [
				'top' => 0,
				'losses' => 0
			],
			'Gavin' => [
				'top' => 0,
				'losses' => 0
			]
		];
		$result = query("SELECT * FROM regular_season_matchups rsm
			JOIN managers ON managers.id = rsm.manager1_id
			ORDER BY year, week_number, manager1_score DESC");
		while ($row = fetch_array($result)) {

			$currentYear = $row['year'];
			$currentWeek = $row['week_number'];

			if ($year != $currentYear || $week != $currentWeek) {
				$index = -1;
			}

			$index++;

			if ($index < 3) {
				if ($first || ($year == $currentYear && $week == $currentWeek)) {
					$managers[$row['name']]['top']++;
					if ($row['manager1_score'] < $row['manager2_score']) {
						$managers[$row['name']]['losses']++;
					}
					$first = false;
				}
			}

			$year = $currentYear;
			$week = $currentWeek;
		}

		foreach ($managers as $manager => $array) { ?>
			<tr>
				<td><?php echo $manager; ?></td>
				<td><?php echo $array['top']; ?></td>
				<td><?php echo $array['losses']; ?></td>
				<td><?php echo round(($array['losses'] / $array['top']) * 100, 1) . ' %'; ?></td>
			</tr>

		<?php } ?>
	</tbody>
	<tfoot>
		<tr>
			<td colspan=4>How many times we were top 3 in points for the week and unluckily lost</td>
		</tr>
	</tfoot>
</table>
<!-- Wins with Bottom 3 points -->
<table class="table" id="datatable-misc9" style="display:none;">
	<thead>
		<th>Manager</th>
		<th>Wks with Bottom 3 Pts</th>
		<th>Wins with Bottom 3 Pts</th>
		<th>Pct</th>
	</thead>
	<tbody>
		<?php
		$year = $week = 0;
		$index = -1;
		$first = true;
		$managers = [
			'AJ' => [
				'bottom' => 0,
				'wins' => 0
			],
			'Ben' => [
				'bottom' => 0,
				'wins' => 0
			],
			'Tyler' => [
				'bottom' => 0,
				'wins' => 0
			],
			'Matt' => [
				'bottom' => 0,
				'wins' => 0
			],
			'Justin' => [
				'bottom' => 0,
				'wins' => 0
			],
			'Andy' => [
				'bottom' => 0,
				'wins' => 0
			],
			'Cole' => [
				'bottom' => 0,
				'wins' => 0
			],
			'Everett' => [
				'bottom' => 0,
				'wins' => 0
			],
			'Cameron' => [
				'bottom' => 0,
				'wins' => 0
			],
			'Gavin' => [
				'bottom' => 0,
				'wins' => 0
			]
		];
		$result = query("SELECT * FROM regular_season_matchups rsm
			JOIN managers ON managers.id = rsm.manager1_id
			ORDER BY year, week_number, manager1_score ASC");
		while ($row = fetch_array($result)) {

			$currentYear = $row['year'];
			$currentWeek = $row['week_number'];

			if ($year != $currentYear || $week != $currentWeek) {
				$index = -1;
			}

			$index++;

			if ($index < 3) {
				if ($first || ($year == $currentYear && $week == $currentWeek)) {
					$managers[$row['name']]['bottom']++;
					if ($row['manager1_score'] > $row['manager2_score']) {
						$managers[$row['name']]['wins']++;
					}
					$first = false;
				}
			}

			$year = $currentYear;
			$week = $currentWeek;
		}

		foreach ($managers as $manager => $array) { ?>
			<tr>
				<td><?php echo $manager; ?></td>
				<td><?php echo $array['bottom']; ?></td>
				<td><?php echo $array['wins']; ?></td>
				<td><?php echo round(($array['wins'] / $array['bottom']) * 100, 1) . ' %'; ?></td>
			</tr>

		<?php } ?>
	</tbody>
	<tfoot>
		<tr>
			<td colspan=4>How many times we were bottom 3 in points for the week and luckily won</td>
		</tr>
	</tfoot>
</table>
<!-- Record against everyone -->
<table class="table" id="datatable-misc10" style="display:none;">
	<thead>
		<th>Manager</th>
		<th>Wins</th>
		<th>Losses</th>
		<th>Win %</th>
	</thead>
	<tbody>
		<?php
		$prevYear = $prevWeek = 0;
		$index = -1;
		$first = true;

		$managers = [
			'AJ' => [
				'losses' => 0,
				'wins' => 0
			],
			'Ben' => [
				'losses' => 0,
				'wins' => 0
			],
			'Tyler' => [
				'losses' => 0,
				'wins' => 0
			],
			'Matt' => [
				'losses' => 0,
				'wins' => 0
			],
			'Justin' => [
				'losses' => 0,
				'wins' => 0
			],
			'Andy' => [
				'losses' => 0,
				'wins' => 0
			],
			'Cole' => [
				'losses' => 0,
				'wins' => 0
			],
			'Everett' => [
				'losses' => 0,
				'wins' => 0
			],
			'Cameron' => [
				'losses' => 0,
				'wins' => 0
			],
			'Gavin' => [
				'losses' => 0,
				'wins' => 0
			]
		];
		$scores = [];
		$result = query("SELECT year, week_number, name, manager1_score FROM regular_season_matchups rsm
			JOIN managers ON managers.id = rsm.manager1_id
			ORDER BY year, week_number, manager1_score ASC");
		while ($row = fetch_array($result)) {
			$scores[$row['year']][$row['week_number']][$row['name']] = $row['manager1_score'];
		}
		foreach ($scores as $year => $weekArray) {
			foreach ($weekArray as $week) {
				$index = 0;
				foreach ($week as $manager => $value) {
					// Account for the years where there were only 8 managers
					if (count($week) == 8) {
						$managers[$manager]['wins'] += $index;
						$managers[$manager]['losses'] += 7 - $index;
					} else {
						$managers[$manager]['wins'] += $index;
						$managers[$manager]['losses'] += 9 - $index;
					}

					$index++;
				}
			}
		}

		foreach ($managers as $manager => $array) { ?>
			<tr>
				<td><?php echo $manager; ?></td>
				<td><?php echo $array['wins']; ?></td>
				<td><?php echo $array['losses']; ?></td>
				<td><?php echo round(($array['wins'] / ($array['wins'] + $array['losses'])) * 100, 1) . ' %'; ?></td>
			</tr>

		<?php } ?>
	</tbody>
	<tfoot>
		<tr>
			<td colspan=4>Had we played every manager every week, our record would be...</td>
		</tr>
	</tfoot>
</table>
<!-- Draft position -->
<table class="table" id="datatable-misc11" style="display:none;">
	<thead>
		<th>Manager</th>
		<th>#1 Picks</th>
		<th>#10 Picks</th>
		<th>Avg. Position</th>
	</thead>
	<tbody>
		<?php
		$result = query("SELECT name, IFNULL(pick1, 0) as pick1, IFNULL(pick10, 0) as pick10, adp
			FROM managers
			LEFT JOIN (
				SELECT COUNT(id) as pick1, manager_id FROM draft
				WHERE overall_pick = 1
			GROUP BY manager_id
			) p1 ON p1.manager_id = managers.id

			LEFT JOIN (
			SELECT COUNT(id) as pick10, manager_id FROM draft
			WHERE overall_pick = 10
			GROUP BY manager_id
			) p10 ON p10.manager_id = managers.id

			LEFT JOIN (
			SELECT AVG(overall_pick) as adp, manager_id FROM draft
			WHERE round = 1
			GROUP BY manager_id
			) average ON average.manager_id = managers.id");
		while ($row = fetch_array($result)) { ?>
			<tr>
				<td><?php echo $row['name']; ?></td>
				<td><?php echo $row['pick1']; ?></td>
				<td><?php echo $row['pick10']; ?></td>
				<td><?php echo round($row['adp'], 1); ?></td>
			</tr>

		<?php } ?>
	</tbody>
	<tfoot>
		<tr>
			<td colspan=4>Number of times with #1 or #10 pick and average draft position</td>
		</tr>
	</tfoot>
</table>
<!-- Moves/trades -->
<table class="table" id="datatable-misc12" style="display:none;">
	<thead>
		<th>Manager</th>
		<th>Moves</th>
		<th>Trades</th>
		<th>Total Per Year</th>
	</thead>
	<tbody>
		<?php
		$result = query("SELECT managers.name, SUM(moves) as moves, SUM(trades) as trades, SUM(moves+trades) as total
			FROM team_names
			JOIN managers on manager_id = managers.id
			GROUP BY managers.name");
		while ($row = fetch_array($result)) { ?>
			<tr>
				<td><?php echo $row['name']; ?></td>
				<td><?php echo $row['moves']; ?></td>
				<td><?php echo $row['trades']; ?></td>
				<td><?php echo round($row['total']/$dashboardNumbers['seasons'], 1); ?></td>
			</tr>

		<?php } ?>
	</tbody>
	<tfoot>
		<tr>
			<td colspan=4>Number of adds/drops/trades</td>
		</tr>
	</tfoot>
</table>