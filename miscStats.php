
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
	    while($manager = mysqli_fetch_array($result2)) 
	    {
	        
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
	            WHERE name = '".$manager['name']."'");
	        while($row = mysqli_fetch_array($result)) 
	        {
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
	    
	    foreach($response as $row) 
	    { ?>
	        <tr>
	            <td><?php echo $row['manager']; ?></td>
	            <td><?php echo $row['winStreak']; ?></td>
	            <td><?php echo $row['loseStreak']; ?></td>
	        </tr>

	    <?php } ?>
	</tbody>
</table>
<table class="table" id="datatable-misc2">
	<thead>
	    <th>Manager</th>
	    <th>Points For</th>
	    <th>Points Against</th>
	    <th>Difference</th>
	</thead>
	<tbody>
	    <?php 
	    // Calc total points and rank
        $rank = 1;
        $result = mysqli_query($conn, "SELECT managers.name, SUM(manager1_score) AS points_for, 
			SUM(manager2_score) AS points_against, SUM(manager1_score) - SUM(manager2_score) AS diff
			FROM regular_season_matchups rsm
			JOIN managers ON managers.id = rsm.manager1_id
			GROUP BY manager1_id;");
        while($row = mysqli_fetch_array($result)) 
        { ?>
	        <tr>
	            <td><?php echo $row['name']; ?></td>
	            <td><?php echo number_format($row['points_for'], 2, '.', ','); ?></td>
	            <td><?php echo number_format($row['points_against'], 2, '.', ','); ?></td>
	            <td><?php echo number_format($row['diff'], 2, '.', ','); ?></td>
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
	    $result = mysqli_query($conn,"SELECT name, IFNULL(one_seeds, 0) as one_seeds, IFNULL(two_seeds, 0) as two_seeds, IFNULL(one_seeds, 0)+IFNULL(two_seeds, 0) as total
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
	    while($row = mysqli_fetch_array($result)) 
	    { ?>
	        <tr>
	            <td><?php echo $row['name']; ?></td>
	            <td><?php echo $row['one_seeds']; ?></td>
	            <td><?php echo $row['two_seeds']; ?></td>
	            <td><?php echo $row['total']; ?></td>
	        </tr>

	    <?php } ?>
	</tbody>
</table>