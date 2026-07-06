<?php

$pageName = "Awards";
include 'header.php';
include 'sidebar.php';

$type = 'all';
if (isset($_GET['id'])) {
    $type = $_GET['id'];
}
?>

<div class="app-content content">
    <div class="content-wrapper">

        <div class="content-body">
            <?php
            $type_labels = [
                'current' => 'Current Season',
                'draft'   => 'Draft',
                'post'    => 'Postseason',
                'regular' => 'Regular Season',
                'roster'  => 'Roster',
            ];
            $active_categories = $type != 'all' ? [$type => $type_labels[$type]] : $type_labels;

            $summary_query = "SELECT managers.name, ff.type, is_positive, COUNT(*) as count
                FROM manager_fun_facts mff
                JOIN fun_facts ff ON mff.fun_fact_id = ff.id
                JOIN managers ON managers.id = mff.manager_id
                WHERE managers.id BETWEEN 1 AND 10";
            if ($type != 'all') {
                $summary_query .= " AND ff.type = '$type'";
            }
            $summary_query .= " GROUP BY managers.name, ff.type, is_positive ORDER BY managers.name, ff.type";
            $summary_result = query($summary_query);

            $summary_data = [];
            while ($summary_row = fetch_array($summary_result)) {
                $name = $summary_row['name'];
                $t    = $summary_row['type'];
                if (!isset($summary_data[$name])) {
                    $summary_data[$name] = [];
                }
                if (!isset($summary_data[$name][$t])) {
                    $summary_data[$name][$t] = ['positive' => 0, 'negative' => 0];
                }
                $key = $summary_row['is_positive'] ? 'positive' : 'negative';
                $summary_data[$name][$t][$key] = (int)$summary_row['count'];
            }

            // Column totals
            $col_totals = [];
            foreach ($active_categories as $cat_key => $cat_label) {
                $col_totals[$cat_key] = ['positive' => 0, 'negative' => 0];
                foreach ($summary_data as $name => $cats) {
                    $col_totals[$cat_key]['positive'] += $cats[$cat_key]['positive'] ?? 0;
                    $col_totals[$cat_key]['negative'] += $cats[$cat_key]['negative'] ?? 0;
                }
            }
            ?>
            <div class="row">
                <div class="col-sm-4">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">Filters</h4>
                        </div>
                        <div class="card-body" style="direction: ltr;">
                            <div class="card-block">
                                <div class="form-group">
                                    <label for="type-select">Record Type:</label>
                                    <select class="form-control" id="type-select">
                                        <option value="all" <?php if ($type == 'all') { echo 'selected'; } ?>>All Record Types</option>
                                        <option value="regular" <?php if ($type == 'regular') { echo 'selected'; } ?>>Regular Season</option>
                                        <option value="post" <?php if ($type == 'post') { echo 'selected'; } ?>>Postseason</option>
                                        <option value="current" <?php if ($type == 'current') { echo 'selected'; } ?>>Current Season</option>
                                        <option value="draft" <?php if ($type == 'draft') { echo 'selected'; } ?>>Draft</option>
                                        <option value="roster" <?php if ($type == 'roster') { echo 'selected'; } ?>>Roster</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="manager-select">Manager:</label>
                                    <select class="form-control" id="manager-select">
                                        <option value="all">All Managers</option>
                                        <?php
                                        $mgr_result = query("SELECT name FROM managers WHERE id BETWEEN 1 AND 10 ORDER BY name");
                                        while ($mgr_row = fetch_array($mgr_result)) {
                                            echo '<option value="' . htmlspecialchars($mgr_row['name']) . '">' . htmlspecialchars($mgr_row['name']) . '</option>';
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <div class="form-check">
                                        <input type="checkbox" id="positive-only">
                                        <label class="form-check-label" for="positive-only">Positive Only</label>
                                    </div>
                                </div>
                                <a href="/records.php">Go To Record Log</a>
                                <div style="margin-top: 12px;">
                                    <button type="button" class="btn btn-primary btn-sm" id="btn-grid">
                                        <i class="fa fa-th-large"></i> Grid
                                    </button>
                                    <button type="button" class="btn btn-default btn-sm" id="btn-table" style="margin-left: 6px;">
                                        <i class="fa fa-table"></i> Table
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-8">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">Awards Summary</h4>
                        </div>
                        <div class="card-body" style="direction: ltr;">
                            <div class="card-block">
                                <div style="overflow-x: auto;">
                                <table class="table table-sm table-bordered" style="margin-bottom: 0; white-space: nowrap;">
                                    <thead>
                                        <tr>
                                            <th rowspan="2" class="align-middle">Manager</th>
                                            <?php foreach ($active_categories as $cat_key => $cat_label): ?>
                                                <th colspan="2" class="text-center"><?php echo $cat_label; ?></th>
                                            <?php endforeach; ?>
                                            <th rowspan="2" class="text-center align-middle">Total</th>
                                        </tr>
                                        <tr>
                                            <?php foreach ($active_categories as $cat_key => $cat_label): ?>
                                                <th class="text-center text-success" style="font-size: 0.8em;">+</th>
                                                <th class="text-center text-danger"  style="font-size: 0.8em;">-</th>
                                            <?php endforeach; ?>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($summary_data as $name => $cats):
                                            $row_total = 0;
                                            foreach ($active_categories as $cat_key => $cat_label) {
                                                $row_total += ($cats[$cat_key]['positive'] ?? 0) + ($cats[$cat_key]['negative'] ?? 0);
                                            }
                                        ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($name); ?></td>
                                                <?php foreach ($active_categories as $cat_key => $cat_label): ?>
                                                    <td class="text-center text-success"><?php echo $cats[$cat_key]['positive'] ?? 0; ?></td>
                                                    <td class="text-center text-danger"><?php echo $cats[$cat_key]['negative'] ?? 0; ?></td>
                                                <?php endforeach; ?>
                                                <td class="text-center"><strong><?php echo $row_total; ?></strong></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th>Total</th>
                                            <?php
                                            $grand_total = 0;
                                            foreach ($active_categories as $cat_key => $cat_label):
                                                $pos = $col_totals[$cat_key]['positive'];
                                                $neg = $col_totals[$cat_key]['negative'];
                                                $grand_total += $pos + $neg;
                                            ?>
                                                <th class="text-center text-success"><?php echo $pos; ?></th>
                                                <th class="text-center text-danger"><?php echo $neg; ?></th>
                                            <?php endforeach; ?>
                                            <th class="text-center"><?php echo $grand_total; ?></th>
                                        </tr>
                                    </tfoot>
                                </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div id="view-grid">
            <div class="row">
                <div class="col-sm-12 table-padding">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">Fun Facts</h4>
                        </div>
                        <div class="card-body" style="background: #fff; direction: ltr;">
                            <?php
                            // Collect awards grouped by manager (alphabetical order)
                            $managers_awards = [];

                            $all_managers_result = query("SELECT * FROM managers WHERE id BETWEEN 1 AND 10 ORDER BY name");
                            while ($manager_row = fetch_array($all_managers_result)) {
                                $x = $manager_row['id'];
                                $manager_name = $manager_row['name'];
                                $managers_awards[$manager_name] = [];

                                    // Get positive awards
                                    $query = "SELECT * FROM manager_fun_facts mff
                                        JOIN fun_facts ff ON mff.fun_fact_id = ff.id
                                        JOIN managers ON managers.id = mff.manager_id
                                        WHERE is_positive = 1 AND manager_id = $x";

                                    if ($type != 'all') {
                                        $query .= " AND type = '$type'";
                                    }

                                    $query .= " ORDER BY ff.sort_order";

                                    $result = query($query);
                                    while ($row = fetch_array($result)) {
                                        $value = $row['value'];
                                        if (is_numeric($row['value'])) {
                                            $value = isDecimal($row['value'])
                                                ? number_format($row['value'], 2, '.', ',')
                                                : number_format($row['value'], 0, '.', ',');
                                        }

                                        $managers_awards[$manager_name][] = [
                                            'fact' => $row['fact'],
                                            'value' => $value,
                                            'note' => $row['note'],
                                            'is_positive' => true
                                        ];
                                    }

                                    // Get negative awards
                                    $query = "SELECT * FROM manager_fun_facts mff
                                        JOIN fun_facts ff ON mff.fun_fact_id = ff.id
                                        JOIN managers ON managers.id = mff.manager_id
                                        WHERE is_positive = 0 AND manager_id = $x";

                                    if ($type != 'all') {
                                        $query .= " AND type = '$type'";
                                    }

                                    $query .= " ORDER BY ff.sort_order";

                                    $result = query($query);
                                    while ($row = fetch_array($result)) {
                                        $value = $row['value'];
                                        if (is_numeric($row['value'])) {
                                            $value = isDecimal($row['value'])
                                                ? number_format($row['value'], 2, '.', ',')
                                                : number_format($row['value'], 0, '.', ',');
                                        }

                                        $managers_awards[$manager_name][] = [
                                            'fact' => $row['fact'],
                                            'value' => $value,
                                            'note' => $row['note'],
                                            'is_positive' => false
                                        ];
                                    }
                            }

                            // Display awards grouped by manager
                            foreach ($managers_awards as $manager_name => $awards) {
                                if (!empty($awards)) {
                                    echo '<div class="manager-section" data-manager="' . htmlspecialchars($manager_name) . '">';
                                    echo '<div class="manager-header">';
                                    echo '<h3><a href="profile.php?id=' . urlencode($manager_name) . '">' . htmlspecialchars($manager_name) . '</a></h3>';
                                    echo '<div class="award-count">' . count($awards) . ' award' . (count($awards) != 1 ? 's' : '') . '</div>';
                                    echo '</div>';

                                    echo '<div class="awards-grid">';
                                    foreach ($awards as $award) {
                                        $award_class = $award['is_positive'] ? 'award-badge positive' : 'award-badge negative';
                                        $is_positive_attr = $award['is_positive'] ? '1' : '0';

                                        echo '<div class="' . $award_class . '" data-positive="' . $is_positive_attr . '">';
                                        echo '<div class="award-header-badge">';
                                        echo '</div>';
                                        echo '<div class="award-title">' . htmlspecialchars($award['fact']) . '</div>';
                                        echo '<div class="award-value">' . htmlspecialchars($award['value']) . '</div>';
                                        if (!empty($award['note'])) {
                                            echo '<div class="award-note">' . htmlspecialchars($award['note']) . '</div>';
                                        }
                                        echo '</div>';
                                    }
                                    echo '</div>';
                                    echo '</div>';
                                }
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
            </div><!-- #view-grid -->

            <div id="view-table" style="display:none;">
            <div class="row">
                <div class="col-sm-12 table-padding">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">Fun Facts</h4>
                        </div>
                        <div class="card-body" style="direction: ltr;">
                            <div style="overflow-x: auto;">
                            <table id="awards-table" class="table table-sm table-bordered table-striped" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>Award</th>
                                        <th>Manager</th>
                                        <th>Value</th>
                                        <th>Note</th>
                                        <th>Type</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php
                                $table_query = "SELECT ff.fact, managers.name as manager, mff.value, mff.note, ff.type, ff.is_positive
                                    FROM manager_fun_facts mff
                                    JOIN fun_facts ff ON mff.fun_fact_id = ff.id
                                    JOIN managers ON managers.id = mff.manager_id
                                    WHERE managers.id BETWEEN 1 AND 10";
                                if ($type != 'all') {
                                    $table_query .= " AND ff.type = '$type'";
                                }
                                $table_query .= " ORDER BY ff.sort_order, managers.name";
                                $table_result = query($table_query);
                                while ($trow = fetch_array($table_result)) {
                                    $tval = $trow['value'];
                                    if (is_numeric($trow['value'])) {
                                        $tval = isDecimal($trow['value'])
                                            ? number_format($trow['value'], 2, '.', ',')
                                            : number_format($trow['value'], 0, '.', ',');
                                    }
                                    $fact_class = $trow['is_positive'] ? 'text-success' : 'text-danger';
                                    $type_label = $type_labels[$trow['type']] ?? ucfirst($trow['type']);
                                    echo '<tr>';
                                    echo '<td class="' . $fact_class . '">' . htmlspecialchars($trow['fact']) . '</td>';
                                    echo '<td>' . htmlspecialchars($trow['manager']) . '</td>';
                                    echo '<td>' . htmlspecialchars($tval) . '</td>';
                                    echo '<td>' . htmlspecialchars($trow['note'] ?? '') . '</td>';
                                    echo '<td>' . htmlspecialchars($type_label) . '</td>';
                                    echo '</tr>';
                                }
                                ?>
                                </tbody>
                            </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            </div><!-- #view-table -->

        </div>
    </div>
</div>

<?php include 'footer.php'; ?>

<script type="text/javascript">

    let baseUrl = "<?php echo $BASE_URL; ?>";

    $('#type-select').change(function() {
        window.location = baseUrl+'awards.php?id='+$('#type-select').val();
    });

    function applyClientFilters() {
        const positiveOnly = $('#positive-only').is(':checked');
        const selectedManager = $('#manager-select').val();

        $('.manager-section').each(function() {
            const section = $(this);
            const managerName = section.data('manager');

            // Manager filter
            if (selectedManager !== 'all' && managerName !== selectedManager) {
                section.hide();
                return;
            }

            // Positive-only filter — show/hide individual badges
            section.find('.award-badge').each(function() {
                const isPositive = $(this).data('positive') === 1;
                $(this).toggle(!positiveOnly || isPositive);
            });

            // Update the award count to reflect visible badges
            const visibleCount = section.find('.award-badge:visible').length;
            if (visibleCount === 0) {
                section.hide();
            } else {
                section.show();
                section.find('.award-count').text(visibleCount + ' award' + (visibleCount !== 1 ? 's' : ''));
            }
        });
    }

    $('#positive-only').change(applyClientFilters);
    $('#manager-select').change(applyClientFilters);

    // View toggle
    let tableInitialized = false;

    $('#btn-grid').click(function() {
        $(this).addClass('active btn-primary').removeClass('btn-default');
        $('#btn-table').addClass('btn-default').removeClass('active btn-primary');
        $('#view-table').hide();
        $('#view-grid').show();
    });

    $('#btn-table').click(function() {
        $(this).addClass('active btn-primary').removeClass('btn-default');
        $('#btn-grid').addClass('btn-default').removeClass('active btn-primary');
        $('#view-grid').hide();
        $('#view-table').show();
        if (!tableInitialized) {
            $('#awards-table').DataTable({
                pageLength: 25,
                order: []
            });
            tableInitialized = true;
        }
        // Sync manager filter to DataTable search
        const mgr = $('#manager-select').val();
        if (mgr !== 'all') {
            $('#awards-table').DataTable().column(1).search(mgr).draw();
        }
    });

    // Keep DataTable manager filter in sync when manager dropdown changes
    $('#manager-select').change(function() {
        if (tableInitialized) {
            const mgr = $(this).val();
            $('#awards-table').DataTable().column(1).search(mgr === 'all' ? '' : mgr).draw();
        }
    });

</script>
