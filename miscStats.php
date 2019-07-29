<table class="table" id="datatable-misc1" style="display:none;">
	<thead>
		<th>Manager</th>
		<th>Longest Win Streak</th>
		<th>Longest Lose Streak</th>
	</thead>
	<tbody>
		<?php
		$managers = '';
		$result2 = mysqli_query($conn, "SELECT * FROM managers");
		while ($manager = mysqli_fetch_array($result2)) {

			$managerId = $manager['id'];

			$winStreak = $loseStreak = $longestWinStreak = $longestLoseStreak = 0;
			$result = mysqli_query($conn, "SELECT name, w.year, w.week_number, win, lose 
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
			while ($row = mysqli_fetch_array($result)) {
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
</table>
<table class="table" id="datatable-misc2" style="display:none;">
	<thead>
		<th>Manager</th>
		<th>Points For</th>
		<th>Points Against</th>
		<th>Difference</th>
	</thead>
	<tbody>
		<?php
		$result = mysqli_query($conn, "SELECT managers.name, SUM(manager1_score) AS points_for, 
			SUM(manager2_score) AS points_against, SUM(manager1_score) - SUM(manager2_score) AS diff
			FROM regular_season_matchups rsm
			JOIN managers ON managers.id = rsm.manager1_id
			GROUP BY manager1_id");
		while ($row = mysqli_fetch_array($result)) { ?>
			<tr>
				<td><?php echo $row['name']; ?></td>
				<td><?php echo number_format($row['points_for'], 2, '.', ','); ?></td>
				<td><?php echo number_format($row['points_against'], 2, '.', ','); ?></td>
				<td><?php echo number_format($row['diff'], 2, '.', ','); ?></td>
			</tr>

		<?php } ?>
	</tbody>
</table>
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
		$result = mysqli_query($conn, "SELECT name, MAX(points_for) as max_pf, MAX(points_against) as max_pa, 
			MIN(points_for) as min_pf, MIN(points_against) as min_pa
			FROM (
				SELECT managers.name, year, SUM(manager1_score) AS points_for, 
				SUM(manager2_score) AS points_against
				FROM regular_season_matchups rsm
				JOIN managers ON managers.id = rsm.manager1_id
				GROUP BY manager1_id, year
			) as all_years
			GROUP BY name");
		while ($row = mysqli_fetch_array($result)) { ?>
			<tr>
				<td><?php echo $row['name']; ?></td>
				<td><?php echo number_format($row['max_pf'], 2, '.', ','); ?></td>
				<td><?php echo number_format($row['min_pf'], 2, '.', ','); ?></td>
				<td><?php echo number_format($row['max_pa'], 2, '.', ','); ?></td>
				<td><?php echo number_format($row['min_pa'], 2, '.', ','); ?></td>
			</tr>

		<?php } ?>
	</tbody>
</table>
<table class="table" id="datatable-misc7" style="display:none;">
	<thead>
		<th>Manager</th>
		<th>Biggest Win</th>
		<th>Smallest Win</th>
		<th>Biggest Loss</th>
		<th>Smallest Loss</th>
	</thead>
	<tbody>
		<?php
		$result = mysqli_query($conn, "SELECT managers.name, MAX(manager1_score - manager2_score) as biggest_win,
			MAX(manager2_score - manager1_score) as biggest_loss,
			MIN(IF (manager1_score > manager2_score, manager1_score - manager2_score, null)) as smallest_win, 
			MIN(IF (manager1_score < manager2_score, manager2_score - manager1_score, null)) as smallest_loss
			FROM regular_season_matchups rsm
			JOIN managers ON managers.id = rsm.manager1_id
			GROUP BY manager1_id");
		while ($row = mysqli_fetch_array($result)) { ?>
			<tr>
				<td><?php echo $row['name']; ?></td>
				<td><?php echo number_format($row['biggest_win'], 2, '.', ','); ?></td>
				<td><?php echo number_format($row['smallest_win'], 2, '.', ','); ?></td>
				<td><?php echo number_format($row['biggest_loss'], 2, '.', ','); ?></td>
				<td><?php echo number_format($row['smallest_loss'], 2, '.', ','); ?></td>
			</tr>

		<?php } ?>
	</tbody>
</table>
<table class="table" id="datatable-misc8" style="display:none;">
	<thead>
		<th>Manager</th>
		<th>Most PF</th>
		<th>Least PF</th>
		<th>Most PA</th>
		<th>Least PA</th>
	</thead>
	<tbody>
		<?php
		$result = mysqli_query($conn, "SELECT managers.name, MAX(manager1_score) as max_pf, MAX(manager2_score) as max_pa, 
			MIN(manager1_score) as min_pf, MIN(manager2_score) as min_pa
			FROM regular_season_matchups rsm
			JOIN managers ON managers.id = rsm.manager1_id
			GROUP BY manager1_id");
		while ($row = mysqli_fetch_array($result)) { ?>
			<tr>
				<td><?php echo $row['name']; ?></td>
				<td><?php echo number_format($row['max_pf'], 2, '.', ','); ?></td>
				<td><?php echo number_format($row['min_pf'], 2, '.', ','); ?></td>
				<td><?php echo number_format($row['max_pa'], 2, '.', ','); ?></td>
				<td><?php echo number_format($row['min_pa'], 2, '.', ','); ?></td>
			</tr>

		<?php } ?>
	</tbody>
</table>
<table class="table" id="datatable-misc9" style="display:none;">
	<thead>
		<th>Manager</th>
		<th>Average Finish</th>
		<th>Highest Finish</th>
		<th>Lowest Finish</th>
	</thead>
	<tbody>
		<?php
		$result = mysqli_query($conn, "SELECT managers.name, AVG(finish) as avg_finish,
			MIN(finish) as highest, MAX(finish) as lowest
			FROM finishes
			JOIN managers ON managers.id = finishes.manager_id
			GROUP BY manager_id");
		while ($row = mysqli_fetch_array($result)) { ?>
			<tr>
				<td><?php echo $row['name']; ?></td>
				<td><?php echo number_format($row['avg_finish'], 2, '.', ','); ?></td>
				<td><?php echo $row['highest']; ?></td>
				<td><?php echo $row['lowest']; ?></td>
			</tr>

		<?php } ?>
	</tbody>
</table>
<table class="table" id="datatable-misc10">
	<thead>
		<th>Manager</th>
		<th>#1 Seed</th>
		<th>#2 Seed</th>
		<th>First Round Byes</th>
	</thead>
	<tbody>
		<?php
		$result = mysqli_query(
			$conn,
			"SELECT name, IFNULL(one_seeds, 0) as one_seeds, IFNULL(two_seeds, 0) as two_seeds, IFNULL(one_seeds, 0)+IFNULL(two_seeds, 0) as total
	        FROM managers
	        LEFT JOIN (SELECT COUNT(manager1_id) as one_seeds, manager1_id 
	        FROM playoff_matchups pm 
	        WHERE manager1_seed = 1 and round = 'Semifinal'
	        GROUP BY manager1_id) one ON managers.id = one.manager1_id
	        LEFT JOIN (SELECT COUNT(manager1_id) as two_seeds, manager1_id 
	        FROM playoff_matchups pm 
	        WHERE (manager1_seed = 2 OR manager2_seed = 2) AND round = 'Semifinal'
	        GROUP BY manager1_id) two ON managers.id = two.manager1_id"
		);
		while ($row = mysqli_fetch_array($result)) { ?>
			<tr>
				<td><?php echo $row['name']; ?></td>
				<td><?php echo $row['one_seeds']; ?></td>
				<td><?php echo $row['two_seeds']; ?></td>
				<td><?php echo $row['total']; ?></td>
			</tr>

		<?php } ?>
	</tbody>
</table>
<table class="table" id="datatable-misc12" style="display:none;">
	<thead>
		<th>Year</th>
		<th>Round</th>
		<th>Manager</th>
		<th>Seed</th>
		<th>Opponent</th>
		<th>Seed</th>
		<th>Score</th>
	</thead>
	<tbody>
		<?php
		$result = mysqli_query($conn, "SELECT year, round, managers.name, manager1_seed, CONCAT(manager1_score, ' - ', manager2_score) as score,
			manager2_seed, (SELECT name FROM managers WHERE id = manager2_id) as opponent
			FROM playoff_matchups pm
			JOIN managers ON pm.manager1_id = managers.id
			WHERE (manager1_seed > manager2_seed) AND manager1_score > manager2_score");
		while ($row = mysqli_fetch_array($result)) { ?>
			<tr>
				<td><?php echo $row['year']; ?></td>
				<td><?php echo $row['round']; ?></td>
				<td><?php echo $row['name']; ?></td>
				<td><?php echo $row['manager1_seed']; ?></td>
				<td><?php echo $row['opponent']; ?></td>
				<td><?php echo $row['manager2_seed']; ?></td>
				<td><?php echo $row['score']; ?></td>
			</tr>

		<?php } ?>
	</tbody>
</table>