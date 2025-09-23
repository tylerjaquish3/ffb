
<!-- Weekly Points Table -->
<table class="table table-responsive table-striped nowrap" id="datatable-league30">
    <thead>
        <tr>
            <th>Year</th>
            <th>Week</th>
            <th>Total Points</th>
            <th>Avg. Points</th>
            <th>Total Margin</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $result = query("
            SELECT 
                year,
                week_number,
                ROUND(SUM(manager1_score), 2) as total_points,
                ROUND(SUM(ABS(manager1_score - manager2_score)) / 2, 2) as margin,
                ROUND(AVG(manager1_score), 2) as avg_points
            FROM regular_season_matchups 
            GROUP BY year, week_number 
            ORDER BY year DESC, week_number DESC
        ");
        
        while ($row = fetch_array($result)) { ?>
            <tr>
                <td><?php echo $row['year']; ?></td>
                <td><?php echo $row['week_number']; ?></td>
                <td><?php echo number_format($row['total_points'], 2, '.', ','); ?></td>
                <td><?php echo number_format($row['avg_points'], 2, '.', ','); ?></td>
                <td><?php echo number_format($row['margin'], 2, '.', ','); ?></td>
            </tr>
        <?php } ?>
    </tbody>
</table>

<!-- Yearly Points Table -->
<table class="table table-responsive table-striped nowrap" id="datatable-league31" style="display:none;">
    <thead>
        <tr>
            <th>Year</th>
            <th>Total Points</th>
            <th>Total Margin</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $result = query("
            SELECT 
                year,
                ROUND(SUM(manager1_score), 2) as total_points,
                ROUND(SUM(ABS(manager1_score - manager2_score)) / 2, 2) as margin
            FROM regular_season_matchups 
            GROUP BY year 
            ORDER BY year DESC
        ");
        
        while ($row = fetch_array($result)) { ?>
            <tr>
                <td><?php echo $row['year']; ?></td>
                <td><?php echo number_format($row['total_points'], 2, '.', ','); ?></td>
                <td><?php echo number_format($row['margin'], 2, '.', ','); ?></td>
            </tr>
        <?php } ?>
    </tbody>
</table>

<!-- Position Points by Week Table -->
<table class="table table-responsive table-striped nowrap" id="datatable-league32" style="display:none;">
    <thead>
        <tr>
            <th>Year</th>
            <th>Week</th>
            <th>QB</th>
            <th>RB</th>
            <th>WR</th>
            <th>TE</th>
            <th>K</th>
            <th>DEF</th>
            <th>BN</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $result = query("
            SELECT 
                year,
                week,
                ROUND(SUM(CASE WHEN position = 'QB' AND roster_spot != 'BN' THEN points ELSE 0 END), 2) as qb_points,
                ROUND(SUM(CASE WHEN position = 'RB' AND roster_spot != 'BN' THEN points ELSE 0 END), 2) as rb_points,
                ROUND(SUM(CASE WHEN position = 'WR' AND roster_spot != 'BN' THEN points ELSE 0 END), 2) as wr_points,
                ROUND(SUM(CASE WHEN position = 'TE' AND roster_spot != 'BN' THEN points ELSE 0 END), 2) as te_points,
                ROUND(SUM(CASE WHEN position = 'K' AND roster_spot != 'BN' THEN points ELSE 0 END), 2) as k_points,
                ROUND(SUM(CASE WHEN position = 'DEF' AND roster_spot != 'BN' THEN points ELSE 0 END), 2) as def_points,
                ROUND(SUM(CASE WHEN roster_spot = 'BN' THEN points ELSE 0 END), 2) as bn_points
            FROM rosters 
            WHERE roster_spot != 'IR' 
                AND points IS NOT NULL 
                AND points > 0
            GROUP BY year, week 
            ORDER BY year DESC, week DESC
        ");
        
        while ($row = fetch_array($result)) { ?>
            <tr>
                <td><?php echo $row['year']; ?></td>
                <td><?php echo $row['week']; ?></td>
                <td><?php echo number_format($row['qb_points'], 2, '.', ','); ?></td>
                <td><?php echo number_format($row['rb_points'], 2, '.', ','); ?></td>
                <td><?php echo number_format($row['wr_points'], 2, '.', ','); ?></td>
                <td><?php echo number_format($row['te_points'], 2, '.', ','); ?></td>
                <td><?php echo number_format($row['k_points'], 2, '.', ','); ?></td>
                <td><?php echo number_format($row['def_points'], 2, '.', ','); ?></td>
                <td><?php echo number_format($row['bn_points'], 2, '.', ','); ?></td>
            </tr>
        <?php } ?>
    </tbody>
</table>
                                                    

<script src="/assets/datatables.js"></script>

<script type="text/javascript">

    function showLeagueTable(tableId) {
        for (i = 30; i < 40; i++) {
            $('#datatable-league' + i + '_wrapper').hide();
            $('#datatable-league' + i).hide();
        }

        $('#datatable-league' + tableId).show();
        $('#datatable-league' + tableId + '_wrapper').show();
    }

    // Initialize Weekly Points DataTable
    $('#datatable-league30').DataTable({
        paging: true,
        info: true,
        order: [
            [2, "desc"]
        ]
    });

    // Initialize Yearly Points DataTable
    $('#datatable-league31').DataTable({
        searching: false,
        paging: true,
        info: true,
        order: [
            [1, "desc"]
        ]
    });

    // Initialize Position Points by Week DataTable
    $('#datatable-league32').DataTable({
        searching: false,
        paging: true,
        info: true,
        order: [
            [2, "desc"]
        ]
    });

</script>

