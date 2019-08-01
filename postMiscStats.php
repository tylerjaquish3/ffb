<!-- Average finish -->
<table class="table" id="datatable-misc10">
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
<!-- First round byes -->
<table class="table" id="datatable-misc11" style="display:none;">
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
<!-- Appearances -->
<table class="table" id="datatable-misc12" style="display:none;">
    <thead>
        <th>Manager</th>
        <th>Appearances</th>
        <th>Consecutive</th>
    </thead>
    <tbody>
        <?php
        $managers = [
            'AJ' => [
                'app' => 0,
                'streak' => 0
            ],
            'Ben' => [
                'app' => 0,
                'streak' => 0
            ],
            'Tyler' => [
                'app' => 0,
                'streak' => 0
            ],
            'Matt' => [
                'app' => 0,
                'streak' => 0
            ],
            'Justin' => [
                'app' => 0,
                'streak' => 0
            ],
            'Andy' => [
                'app' => 0,
                'streak' => 0
            ],
            'Cole' => [
                'app' => 0,
                'streak' => 0
            ],
            'Everett' => [
                'app' => 0,
                'streak' => 0
            ],
            'Cameron' => [
                'app' => 0,
                'streak' => 0
            ],
            'Gavin' => [
                'app' => 0,
                'streak' => 0
            ]
        ];
        $currentName = '';
        $name = 'dog';
        $currentStreak = $longestStreak = $appearances = 0;
        $result = mysqli_query(
            $conn,
            "SELECT * FROM finishes JOIN managers ON managers.id = finishes.manager_id"
        );
        while ($row = mysqli_fetch_array($result)) {
            $currentName = $row['name'];

            if ($currentName != $name && $name != 'dog') {
                $managers[$name]['streak'] = $longestStreak;
                $longestStreak = $currentStreak = 0;
            }

            if ($row['finish'] < 7) {
                $currentStreak++;
                $managers[$row['name']]['app']++;
            } else {
                if ($currentStreak > $longestStreak) {
                    $longestStreak = $currentStreak;
                }
                $currentStreak = 0;
            }

            $name = $currentName;
        }
        $managers[$name]['streak'] = $longestStreak;

        foreach ($managers as $manager => $array) { ?>
            <tr>
                <td><?php echo $manager; ?></td>
                <td><?php echo $array['app']; ?></td>
                <td><?php echo $array['streak']; ?></td>
            </tr>

        <?php } ?>
    </tbody>
</table>
<!-- Underdog wins -->
<table class="table" id="datatable-misc13" style="display:none;">
    <thead>
        <th>Manager</th>
        <th>Quarterfinal</th>
        <th>Semifinal</th>
        <th>Final</th>
        <th>Total</th>
    </thead>
    <tbody>
        <?php
        $managers = [
            'AJ' => [
                'quarter' => 0,
                'semi' => 0,
                'final' => 0,
                'total' => 0
            ],
            'Ben' => [
                'quarter' => 0,
                'semi' => 0,
                'final' => 0,
                'total' => 0
            ],
            'Tyler' => [
                'quarter' => 0,
                'semi' => 0,
                'final' => 0,
                'total' => 0
            ],
            'Matt' => [
                'quarter' => 0,
                'semi' => 0,
                'final' => 0,
                'total' => 0
            ],
            'Justin' => [
                'quarter' => 0,
                'semi' => 0,
                'final' => 0,
                'total' => 0
            ],
            'Andy' => [
                'quarter' => 0,
                'semi' => 0,
                'final' => 0,
                'total' => 0
            ],
            'Cole' => [
                'quarter' => 0,
                'semi' => 0,
                'final' => 0,
                'total' => 0
            ],
            'Everett' => [
                'quarter' => 0,
                'semi' => 0,
                'final' => 0,
                'total' => 0
            ],
            'Cameron' => [
                'quarter' => 0,
                'semi' => 0,
                'final' => 0,
                'total' => 0
            ],
            'Gavin' => [
                'quarter' => 0,
                'semi' => 0,
                'final' => 0,
                'total' => 0
            ]
        ];
        $result = mysqli_query($conn, "SELECT name, round, COUNT(name) as num
            FROM (
            SELECT year, round, managers.name, manager1_seed, manager2_seed, manager1_score, manager2_score
            FROM playoff_matchups pm
            JOIN managers ON pm.manager1_id = managers.id
            WHERE (manager1_seed > manager2_seed) AND manager1_score > manager2_score
            UNION
            SELECT year, round, managers.name, manager1_seed, manager2_seed, manager1_score, manager2_score
            FROM playoff_matchups pm
            JOIN managers ON pm.manager2_id = managers.id
            WHERE (manager1_seed < manager2_seed) AND manager1_score < manager2_score 
            ) as underdog
            GROUP BY round, name");
        while ($row = mysqli_fetch_array($result)) {

            if ($row['round'] == 'Final') {
                $managers[$row['name']]['final'] = $row['num'];
                $managers[$row['name']]['total'] += $row['num'];
            } elseif ($row['round'] == 'Semifinal') {
                $managers[$row['name']]['semi'] = $row['num'];
                $managers[$row['name']]['total'] += $row['num'];
            } elseif ($row['round'] == 'Quarterfinal') {
                $managers[$row['name']]['quarter'] = $row['num'];
                $managers[$row['name']]['total'] += $row['num'];
            }
        }

        foreach ($managers as $manager => $array) { ?>
            <tr>
                <td><?php echo $manager; ?></td>
                <td><?php echo $array['quarter']; ?></td>
                <td><?php echo $array['semi']; ?></td>
                <td><?php echo $array['final']; ?></td>
                <td><?php echo $array['total']; ?></td>
            </tr>

        <?php } ?>
    </tbody>
</table>
<!-- Top seed losses -->
<table class="table" id="datatable-misc14" style="display:none;">
    <thead>
        <th>Manager</th>
        <th>Quarterfinal</th>
        <th>Semifinal</th>
        <th>Final</th>
        <th>Total</th>
    </thead>
    <tbody>
        <?php
        $managers = [
            'AJ' => [
                'quarter' => 0,
                'semi' => 0,
                'final' => 0,
                'total' => 0
            ],
            'Ben' => [
                'quarter' => 0,
                'semi' => 0,
                'final' => 0,
                'total' => 0
            ],
            'Tyler' => [
                'quarter' => 0,
                'semi' => 0,
                'final' => 0,
                'total' => 0
            ],
            'Matt' => [
                'quarter' => 0,
                'semi' => 0,
                'final' => 0,
                'total' => 0
            ],
            'Justin' => [
                'quarter' => 0,
                'semi' => 0,
                'final' => 0,
                'total' => 0
            ],
            'Andy' => [
                'quarter' => 0,
                'semi' => 0,
                'final' => 0,
                'total' => 0
            ],
            'Cole' => [
                'quarter' => 0,
                'semi' => 0,
                'final' => 0,
                'total' => 0
            ],
            'Everett' => [
                'quarter' => 0,
                'semi' => 0,
                'final' => 0,
                'total' => 0
            ],
            'Cameron' => [
                'quarter' => 0,
                'semi' => 0,
                'final' => 0,
                'total' => 0
            ],
            'Gavin' => [
                'quarter' => 0,
                'semi' => 0,
                'final' => 0,
                'total' => 0
            ]
        ];
        $result = mysqli_query($conn, "SELECT name, round, COUNT(name) as num
            FROM (
            SELECT year, round, managers.name, manager1_seed, manager2_seed, manager1_score, manager2_score
            FROM playoff_matchups pm
            JOIN managers ON pm.manager1_id = managers.id
            WHERE (manager1_seed < manager2_seed) AND manager1_score < manager2_score
            UNION
            SELECT year, round, managers.name, manager1_seed, manager2_seed, manager1_score, manager2_score
            FROM playoff_matchups pm
            JOIN managers ON pm.manager2_id = managers.id
            WHERE (manager1_seed > manager2_seed) AND manager1_score > manager2_score 
            ) as underdog
            GROUP BY round, name");
        while ($row = mysqli_fetch_array($result)) {

            if ($row['round'] == 'Final') {
                $managers[$row['name']]['final'] = $row['num'];
                $managers[$row['name']]['total'] += $row['num'];
            } elseif ($row['round'] == 'Semifinal') {
                $managers[$row['name']]['semi'] = $row['num'];
                $managers[$row['name']]['total'] += $row['num'];
            } elseif ($row['round'] == 'Quarterfinal') {
                $managers[$row['name']]['quarter'] = $row['num'];
                $managers[$row['name']]['total'] += $row['num'];
            }
        }

        foreach ($managers as $manager => $array) { ?>
            <tr>
                <td><?php echo $manager; ?></td>
                <td><?php echo $array['quarter']; ?></td>
                <td><?php echo $array['semi']; ?></td>
                <td><?php echo $array['final']; ?></td>
                <td><?php echo $array['total']; ?></td>
            </tr>

        <?php } ?>
    </tbody>
</table>
<!-- Playoff points -->
<table class="table" id="datatable-misc15" style="display:none;">
    <thead>
        <th>Manager</th>
        <th>Points</th>
        <th># Matchups</th>
        <th>Average</th>
    </thead>
    <tbody>
        <?php
        $result = mysqli_query(
            $conn,
            "SELECT name, ptsTop, ptsBottom, gamest, gamesb
            FROM managers 
            LEFT JOIN (
            SELECT COUNT(id) as gamest, SUM(manager1_score) AS ptsTop, manager1_id FROM playoff_matchups rsm 
            GROUP BY manager1_id
            ) w ON w.manager1_id = managers.id

            LEFT JOIN (
            SELECT COUNT(id) as gamesb, SUM(manager2_score) AS ptsBottom, manager2_id FROM playoff_matchups rsm 
            GROUP BY manager2_id
            ) l ON l.manager2_id = managers.id"
        );
        while ($row = mysqli_fetch_array($result)) {

            $points = $row['ptsTop'] + $row['ptsBottom'];
            $games = $row['gamest'] + $row['gamesb'];
            $average = $points / $games;
            ?>
            <tr>
                <td><?php echo $row['name']; ?></td>
                <td><?php echo number_format($points, 2, '.', ','); ?></td>
                <td><?php echo $games; ?></td>
                <td><?php echo number_format($average, 2, '.', ','); ?></td>
            </tr>

        <?php } ?>
    </tbody>
</table>