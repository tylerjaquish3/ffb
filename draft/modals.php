<div class="modal fade" id="draft-board" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="fa fa-times"></i></button>
                <h4 class="modal-title">Draft Board</h4>
            </div>
            <div class="modal-body">
                <table class="table table-responsive" id="datatable-board">
                    <thead>
                        <?php
                        foreach ($draftOrder as $man => $avatar) {
                            echo '<th>'.$man.'</th>';
                        }
                        ?>
                    </thead>
                    <tbody>
                        <?php
                        for($round = 1; $round <= 22; $round++) {
                            $pickMin = ($round*10)-10;
                            $pickMax = $round*10;
                            $dir = 'asc';
                            // Even rounds go backwards
                            if ($round % 2 == 0) {
                                $dir = 'desc';
                            }
                            echo '<tr>';

                            $result = mysqli_query(
                                $conn,
                                "SELECT pick_number, player, position, adp FROM draft_selections ds
                                JOIN preseason_rankings pr ON pr.id = ds.ranking_id
                                WHERE pick_number <= $pickMax
                                AND pick_number > $pickMin
                                ORDER BY pick_number $dir"
                            );

                            $count = mysqli_num_rows($result);
                            if ($round % 2 == 0 && $count < 10) {
                                for ($x = 0; $x < (10-$count); $x++) {
                                    echo '<td></td>';
                                }
                            }

                            while ($row = mysqli_fetch_array($result)) {
                                $goodPick = $row['pick_number'] >= $row['adp'] ? 'good-pick' : 'bad-pick';
                                ?>
                                <td class="color-<?php echo $row['position']; ?>"><?php echo '<span class="sub '.$goodPick.'">'.$row['pick_number'].'</span>&nbsp;'.$row['player']; ?></td>
                        <?php }
                            echo '</tr>';
                        } ?>
                    </tbody>
                </table>

            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="cheat-sheet" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="fa fa-times"></i></button>
                <h4 class="modal-title">Cheat Sheet</h4>
            </div>
            <div class="modal-body" style="direction: ltr">
                <div class="row">
                    <?php
                        $positions = ['QB','RB','WR','TE'];
                        $tiers = [1,2,3,4,5,6,7,8];
                        $posRank = 1;

                        foreach ($positions as $pos) {
                            echo '<div class="col-md-3">';
                            foreach ($tiers as $tier) {

                                echo '<strong>Tier '.$tier.'</strong><br />';

                                $result = mysqli_query(
                                    $conn,
                                    "SELECT tier, my_rank, player, position, pick_number, proj_points, is_mine
                                    FROM preseason_rankings pr
                                    LEFT JOIN draft_selections ds ON pr.id = ds.ranking_id
                                    WHERE position = '$pos'
                                    AND tier = $tier
                                    ORDER BY my_rank"
                                );
                                while ($row = mysqli_fetch_array($result)) {
                                    if ($tier == $row['tier']) {
                                        $class = '';

                                        if ($row['pick_number']) {
                                            $class = 'strike';
                                        }
                                        if ($row['is_mine']) {
                                            $class = 'strike mine';
                                        }
                                        echo '<span class="'.$class.'">'.$row['my_rank'].'. '.$row['player'].' ('.$row['proj_points'].')</span><br />';
                                    }
                                }
                            }
                            echo '</div>';
                        }
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="proj-standings" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-md" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="fa fa-times"></i></button>
                <h4 class="modal-title">Projected Standings</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-xs-12">
                        <table class="table table-responsive" id="datatable-standings">
                            <thead>
                                <th>Manager</th>
                                <th>QB</th>
                                <th>RB</th>
                                <th>WR</th>
                                <th>TE</th>
                                <th>DEF</th>
                                <th>K</th>
                                <th>Total</th>
                            </thead>
                            <tbody>
                                <?php
                                $drafted = [];
                                foreach ($draftOrder as $man => $avatar) {
                                    $drafted[$man] = ['QB' => 0,'RB' => 0,'WR' => 0,'TE' => 0,'DEF' => 0,'K' => 0,'Total' => 0,
                                    'QBc' => 0, 'RBc' => 0, 'WRc' => 0, 'TEc' => 0, 'DEFc' => 0, 'Kc' => 0,
                                    'QBm' => 2, 'RBm' => 3, 'WRm' => 3, 'TEm' => 1, 'DEFm' => 1, 'Km' => 1];

                                }
                                $count = 0;
                                $result = mysqli_query($conn,
                                    "SELECT name, proj_points, position, pick_number
                                    FROM preseason_rankings
                                    JOIN draft_selections ON preseason_rankings.id = draft_selections.ranking_id
                                    JOIN managers ON managers.id = draft_selections.manager_id"
                                );
                                while ($row = mysqli_fetch_array($result)) {
                                    $pos = $row['position'];
                                    if ($pos) {
                                        // Only count the position if less than max at the position
                                        // This is to only sum the starting lineup, not all players
                                        if ($drafted[$row['name']][$pos.'c'] < $drafted[$row['name']][$pos.'m']) {
                                            $drafted[$row['name']][$pos] += (int)$row['proj_points'];
                                            $drafted[$row['name']][$pos.'c']++;
                                            $drafted[$row['name']]['Total'] += (int)$row['proj_points'];
                                        }
                                    }
                                }
                                foreach ($drafted as $man => $row) {
                                ?>
                                    <tr>
                                        <td><?php echo $man; ?></td>
                                        <td><?php echo $row['QB']; ?></td>
                                        <td><?php echo $row['RB']; ?></td>
                                        <td><?php echo $row['WR']; ?></td>
                                        <td><?php echo $row['TE']; ?></td>
                                        <td><?php echo $row['DEF']; ?></td>
                                        <td><?php echo $row['K']; ?></td>
                                        <td><?php echo $row['Total']; ?></td>
                                    </tr>
                                <?php
                                } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="player-data" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content" style="direction: ltr">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="fa fa-times"></i></button>
                <h4 class="modal-title">Player Data</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-xs-12">
                        <input type="hidden" id="player-id">
                        <div id="player-header"></div>
                        <div id="fetched-data"></div>

                        <textarea id="player-notes" cols=150 rows=6></textarea>
                        <br /><a class="btn btn-secondary mine" id="save-note">Save</a><div id="confirm"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="depth-chart" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content" style="direction: ltr">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="fa fa-times"></i></button>
                <h4 class="modal-title">Depth Chart</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-xs-12">
                        <table class="table table-responsive" id="datatable-depth">
                            <thead>
                                <th>Team</th>
                                <th>QB 1</th>
                                <th>QB 2</th>
                                <th>RB 1</th>
                                <th>RB 2</th>
                                <th>WR 1</th>
                                <th>WR 2</th>
                                <th>WR 3</th>
                                <th>WR 4</th>
                                <th>TE</th>
                                <th>K</th>
                            </thead>
                            <tbody>
                                <?php
                                $result = mysqli_query($conn, "SELECT team,
                                    max(case when position = 'QB' and depth = 1 then player ELSE '' end) as QB1,
                                    max(case when position = 'QB' and depth = 1 AND ranking_id IS NOT null then player ELSE '' end) as QB1p,
                                    max(case when position = 'QB' and depth = 2 then player ELSE '' end) as QB2,
                                    max(case when position = 'QB' and depth = 1 AND ranking_id IS NOT null then player ELSE '' end) as QB2p,
                                    max(case when position = 'RB' and depth = 1 then player ELSE '' end) as RB1,
                                    max(case when position = 'RB' and depth = 1 AND ranking_id IS NOT null then player ELSE '' end) as RB1p,
                                    max(case when position = 'RB' and depth = 2 then player ELSE '' end) as RB2,
                                    max(case when position = 'RB' and depth = 1 AND ranking_id IS NOT null then player ELSE '' end) as RB2p,
                                    max(case when position = 'WR' and depth = 1 then player ELSE '' end) as WR1,
                                    max(case when position = 'WR' and depth = 1 AND ranking_id IS NOT null then player ELSE '' end) as WR1p,
                                    max(case when position = 'WR' and depth = 2 then player ELSE '' end) as WR2,
                                    max(case when position = 'WR' and depth = 1 AND ranking_id IS NOT null then player ELSE '' end) as WR2p,
                                    max(case when position = 'WR' and depth = 3 then player ELSE '' end) as WR3,
                                    max(case when position = 'WR' and depth = 1 AND ranking_id IS NOT null then player ELSE '' end) as WR3p,
                                    max(case when position = 'WR' and depth = 4 then player ELSE '' end) as WR4,
                                    max(case when position = 'WR' and depth = 1 AND ranking_id IS NOT null then player ELSE '' end) as WR4p,
                                    max(case when position = 'TE' and depth = 1 then player ELSE '' end) as TE1,
                                    max(case when position = 'TE' and depth = 1 AND ranking_id IS NOT null then player ELSE '' end) as TE1p,
                                    max(case when position = 'K' then player ELSE '' end) as K,
                                    max(case when position = 'K' and depth = 1 AND ranking_id IS NOT null then player ELSE '' end) as Kp
                                    FROM preseason_rankings pr
                                    LEFT JOIN draft_selections ds ON ds.ranking_id = pr.id
                                    WHERE team IS NOT null
                                    GROUP BY team");
                                while ($row = mysqli_fetch_array($result)) {
                                ?>
                                    <tr>
                                        <td><strong><?php echo $row['team']; ?></strong></td>
                                        <td class="color-QB <?php echo $row['QB1'] == $row['QB1p'] ? 'color-gray' : ''; ?>"><?php echo $row['QB1']; ?></td>
                                        <td class="color-QB <?php echo $row['QB2'] == $row['QB2p'] ? 'color-gray' : ''; ?>"><?php echo $row['QB2']; ?></td>
                                        <td class="color-RB <?php echo $row['RB1'] == $row['RB1p'] ? 'color-gray' : ''; ?>"><?php echo $row['RB1']; ?></td>
                                        <td class="color-RB <?php echo $row['RB1'] == $row['RB2p'] ? 'color-gray' : ''; ?>"><?php echo $row['RB2']; ?></td>
                                        <td class="color-WR <?php echo $row['WR1'] == $row['WR1p'] ? 'color-gray' : ''; ?>"><?php echo $row['WR1']; ?></td>
                                        <td class="color-WR <?php echo $row['WR2'] == $row['WR2p'] ? 'color-gray' : ''; ?>"><?php echo $row['WR2']; ?></td>
                                        <td class="color-WR <?php echo $row['WR3'] == $row['WR3p'] ? 'color-gray' : ''; ?>"><?php echo $row['WR3']; ?></td>
                                        <td class="color-WR <?php echo $row['WR4'] == $row['WR4p'] ? 'color-gray' : ''; ?>"><?php echo $row['WR4']; ?></td>
                                        <td class="color-TE <?php echo $row['TE1'] == $row['TE1p'] ? 'color-gray' : ''; ?>"><?php echo $row['TE1']; ?></td>
                                        <td class="color-K <?php echo $row['K'] == $row['Kp'] ? 'color-gray' : ''; ?>"><?php echo $row['K']; ?></td>
                                    </tr>
                                <?php
                                } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>