<!-- Average finish -->
<table class="table table-responsive table-striped nowrap" id="datatable-misc20">
    <thead>
        <th>Manager</th>
        <th>Average Finish</th>
        <th>Highest Finish</th>
        <th>Lowest Finish</th>
    </thead>
    <tbody>
        <?php
        $result = query("SELECT managers.name, AVG(finish) as avg_finish,
			MIN(finish) as highest, MAX(finish) as lowest
			FROM finishes
			JOIN managers ON managers.id = finishes.manager_id
			GROUP BY manager_id");
        while ($row = fetch_array($result)) { ?>
            <tr>
                <td><?php echo $row['name']; ?></td>
                <td><?php echo number_format($row['avg_finish'], 2, '.', ','); ?></td>
                <td><?php echo $row['highest']; ?></td>
                <td><?php echo $row['lowest']; ?></td>
            </tr>

        <?php } ?>
    </tbody>
    <tfoot>
        <tr>
            <td colspan=4>Average placing in league</td>
        </tr>
    </tfoot>
</table>
<!-- First round byes -->
<table class="table table-responsive table-striped nowrap" id="datatable-misc21" style="display:none;">
    <thead>
        <th>Manager</th>
        <th>#1 Seed</th>
        <th>#2 Seed</th>
        <th>First Round Byes</th>
    </thead>
    <tbody>
        <?php
        $result = query(
            "SELECT name, IFNULL(one_seeds, 0) as one_seeds, IFNULL(two_seeds1, 0)+IFNULL(two_seeds2, 0) as two_seeds, 
            IFNULL(one_seeds, 0)+IFNULL(two_seeds1, 0)+IFNULL(two_seeds2, 0) as total
            FROM managers
            LEFT JOIN (SELECT COUNT(manager1_id) as one_seeds, manager1_id 
                FROM playoff_matchups pm 
                WHERE manager1_seed = 1 and round = 'Semifinal'
                GROUP BY manager1_id) one ON managers.id = one.manager1_id
            LEFT JOIN (SELECT COUNT(manager1_id) as two_seeds1, manager1_id 
                FROM playoff_matchups pm 
                WHERE manager1_seed = 2 AND round = 'Semifinal'
                GROUP BY manager1_id) two1 ON managers.id = two1.manager1_id
            LEFT JOIN (SELECT COUNT(manager2_id) as two_seeds2, manager2_id 
                FROM playoff_matchups pm 
                WHERE manager2_seed = 2 AND round = 'Semifinal'
                GROUP BY manager2_id) two2 ON managers.id = two2.manager2_id");
        while ($row = fetch_array($result)) { ?>
            <tr>
                <td><?php echo $row['name']; ?></td>
                <td><?php echo $row['one_seeds']; ?></td>
                <td><?php echo $row['two_seeds']; ?></td>
                <td><?php echo $row['total']; ?></td>
            </tr>

        <?php } ?>
    </tbody>
    <tfoot>
        <tr>
            <td colspan=4>Number of times we got a bye in the first round of postseason</td>
        </tr>
    </tfoot>
</table>
<!-- Appearances -->
<table class="table table-responsive table-striped nowrap" id="datatable-misc22" style="display:none;">
    <thead>
        <th>Manager</th>
        <th>Appearances</th>
        <th>Best Streak</th>
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
        $result = query(
            "SELECT * FROM finishes JOIN managers ON managers.id = finishes.manager_id"
        );
        while ($row = fetch_array($result)) {
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
    <tfoot>
        <tr>
            <td colspan=3>Postseason appearances and best streak of consecutive appearances</td>
        </tr>
    </tfoot>
</table>
<!-- Underdog wins -->
<table class="table table-responsive table-striped nowrap" id="datatable-misc23" style="display:none;">
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
        $result = query("SELECT name, round, COUNT(name) as num
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
        while ($row = fetch_array($result)) {

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
    <tfoot>
        <tr>
            <td colspan=5>Postseason wins as the bottom seed in a matchup</td>
        </tr>
    </tfoot>
</table>
<!-- Top seed losses -->
<table class="table table-responsive table-striped nowrap" id="datatable-misc24" style="display:none;">
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
        $result = query("SELECT name, round, COUNT(name) as num
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
        while ($row = fetch_array($result)) {

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
    <tfoot>
        <tr>
            <td colspan=5>Postseason losses as the top seed in a matchup</td>
        </tr>
    </tfoot>
</table>
<!-- Playoff points -->
<table class="table table-responsive table-striped nowrap" id="datatable-misc25" style="display:none;">
    <thead>
        <th>Manager</th>
        <th>Points</th>
        <th># Matchups</th>
        <th>Average</th>
    </thead>
    <tbody>
        <?php
        $result = query(
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
        while ($row = fetch_array($result)) {

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
    <tfoot>
        <tr>
            <td colspan=4>Points scored in postseason matchups</td>
        </tr>
    </tfoot>
</table>
<!-- Win/loss margin -->
<table class="table table-responsive table-striped nowrap" id="datatable-misc26" style="display:none;">
	<thead>
		<th>Manager</th>
		<th>Biggest Win</th>
		<th>Smallest Win</th>
		<th>Biggest Loss</th>
		<th>Smallest Loss</th>
	</thead>
	<tbody>
		<?php
		$managers = [
			'AJ' => [
				'biggestWin' => 0,
				'smallestWin' => 999,
				'biggestLoss' => 0,
				'smallestLoss' => 999
			],
			'Ben' => [
				'biggestWin' => 0,
				'smallestWin' => 999,
				'biggestLoss' => 0,
				'smallestLoss' => 999
			],
			'Tyler' => [
				'biggestWin' => 0,
				'smallestWin' => 999,
				'biggestLoss' => 0,
				'smallestLoss' => 999
			],
			'Matt' => [
				'biggestWin' => 0,
				'smallestWin' => 999,
				'biggestLoss' => 0,
				'smallestLoss' => 999
			],
			'Justin' => [
				'biggestWin' => 0,
				'smallestWin' => 999,
				'biggestLoss' => 0,
				'smallestLoss' => 999
			],
			'Andy' => [
				'biggestWin' => 0,
				'smallestWin' => 999,
				'biggestLoss' => 0,
				'smallestLoss' => 999
			],
			'Cole' => [
				'biggestWin' => 0,
				'smallestWin' => 999,
				'biggestLoss' => 0,
				'smallestLoss' => 999
			],
			'Everett' => [
				'biggestWin' => 0,
				'smallestWin' => 999,
				'biggestLoss' => 0,
				'smallestLoss' => 999
			],
			'Cameron' => [
				'biggestWin' => 0,
				'smallestWin' => 999,
				'biggestLoss' => 0,
				'smallestLoss' => 999
			],
			'Gavin' => [
				'biggestWin' => 0,
				'smallestWin' => 999,
				'biggestLoss' => 0,
				'smallestLoss' => 999
			]
		];
		$result = query("SELECT * FROM playoff_matchups JOIN managers ON manager1_id = managers.id");
		while ($row = fetch_array($result)) { 
			$diff = abs($row['manager1_score'] - $row['manager2_score']);
			// if manager won
			if ($row['manager1_score'] > $row['manager2_score']) {
				$managers[$row['name']]['biggestWin'] = $diff > $managers[$row['name']]['biggestWin'] ? $diff : $managers[$row['name']]['biggestWin'];
				$managers[$row['name']]['smallestWin'] = $diff < $managers[$row['name']]['smallestWin'] ? $diff : $managers[$row['name']]['smallestWin'];
			} else {
				// manager lost
				$managers[$row['name']]['biggestLoss'] = $diff > $managers[$row['name']]['biggestLoss'] ? $diff : $managers[$row['name']]['biggestLoss'];
				$managers[$row['name']]['smallestLoss'] = $diff < $managers[$row['name']]['smallestLoss'] ? $diff : $managers[$row['name']]['smallestLoss'];
			}
		}

		$result = query("SELECT * FROM playoff_matchups JOIN managers ON manager2_id = managers.id");
		while ($row = fetch_array($result)) { 
			$diff = abs($row['manager2_score'] - $row['manager1_score']);
			// if manager won
			if ($row['manager2_score'] > $row['manager1_score']) {
				$managers[$row['name']]['biggestWin'] = $diff > $managers[$row['name']]['biggestWin'] ? $diff : $managers[$row['name']]['biggestWin'];
				$managers[$row['name']]['smallestWin'] = $diff < $managers[$row['name']]['smallestWin'] ? $diff : $managers[$row['name']]['smallestWin'];
			} else {
				// manager lost
				$managers[$row['name']]['biggestLoss'] = $diff > $managers[$row['name']]['biggestLoss'] ? $diff : $managers[$row['name']]['biggestLoss'];
				$managers[$row['name']]['smallestLoss'] = $diff < $managers[$row['name']]['smallestLoss'] ? $diff : $managers[$row['name']]['smallestLoss'];
			}
		}

		foreach ($managers as $name => $manager) { ?>
			<tr>
				<td><?php echo $name; ?></td>
				<td><?php echo number_format($manager['biggestWin'], 2, '.', ','); ?></td>
				<td><?php echo number_format($manager['smallestWin'], 2, '.', ','); ?></td>
				<td><?php echo number_format($manager['biggestLoss'], 2, '.', ','); ?></td>
				<td><?php echo number_format($manager['smallestLoss'], 2, '.', ','); ?></td>
			</tr>

		<?php } ?>
	</tbody>
	<tfoot>
		<tr>
			<td colspan=5>Min and max margin of victory and defeat</td>
		</tr>
	</tfoot>
</table>