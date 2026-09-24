<?php

$pageName = "Luck";
include 'header.php';
include 'sidebar.php';

?>

<div class="app-content content">
    <div class="content-wrapper">

        <div class="content-body">

            <!-- Tabs Navigation -->
            <div class="row mb-1">
                <div class="col-sm-12">
                    <div class="tab-buttons-container">
                        <button class="tab-button active" id="good-luck-tab" onclick="showCard('good-luck')">
                            Good Luck
                        </button>
                        <button class="tab-button" id="bad-luck-tab" onclick="showCard('bad-luck')">
                            Bad Luck
                        </button>
                    </div>
                </div>
            </div>

            <div class="row card-section" id="good-luck">
                <div class="col-sm-12 col-lg-4 table-padding">
                    <div class="card">
                        <div class="card-header">
                            <h4>Good Luck by Manager</h4>
                        </div>
                        <div class="card-body" style="background: #fff; direction: ltr">
                            <table class="table table-responsive table-striped nowrap" id="datatable-good-luck-summary">
                                <thead>
                                    <th>Manager</th>
                                    <th>Wins with Bottom Points</th>
                                    <th>5 pt Wins</th>
                                    <th>Total</th>
                                </thead>
                                <tbody>
                                    <!-- Table body will be populated by JS -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-sm-12 col-lg-8 table-padding">
                    <div class="card">
                        <div class="card-header">
                            <h4>Wins with Bottom Points</h4>
                        </div>
                        <div class="card-body" style="background: #fff; direction: ltr">
                            <table class="table table-responsive table-striped nowrap" id="datatable-good-luck">
                                <thead>
                                    <th>Year</th>
                                    <th>Week</th>
                                    <th>Manager</th>
                                    <th>Score</th>
                                    <th>Weekly Rank</th>
                                    <th>Opponent</th>
                                    <th>Opponent Score</th>
                                    <th>Margin</th>
                                </thead>
                                <tbody>
                                    <!-- Table body will be populated by JS -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-header" style="direction: ltr; text-align: right">
                            <h4>5 Point Wins</h4>
                        </div>
                        <div class="card-body" style="background: #fff; direction: ltr">
                            <table class="table table-responsive table-striped nowrap" id="datatable-close-wins">
                                <thead>
                                    <th>Year</th>
                                    <th>Week</th>
                                    <th>Manager</th>
                                    <th>Score</th>
                                    <th>Opponent</th>
                                    <th>Opponent Score</th>
                                    <th>Margin</th>
                                </thead>
                                <tbody>
                                    <!-- Table body will be populated by JS -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row card-section" id="bad-luck" style="display: none;">
                <div class="col-sm-12 col-lg-4 table-padding">
                    <div class="card">
                        <div class="card-header">
                            <h4>Bad Luck by Manager</h4>
                        </div>
                        <div class="card-body" style="background: #fff; direction: ltr">
                            <table class="table table-responsive table-striped nowrap" id="datatable-bad-luck-summary">
                                <thead>
                                    <th>Manager</th>
                                    <th>Losses with Top Points</th>
                                    <th>5 pt Losses</th>
                                    <th>Total</th>
                                </thead>
                                <tbody>
                                    <!-- Table body will be populated by JS -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-sm-12 col-lg-8 table-padding">
                    <div class="card">
                        <div class="card-header">
                            <h4>Losses with Top Points</h4>
                        </div>
                        <div class="card-body" style="background: #fff; direction: ltr">
                            <table class="table table-responsive table-striped nowrap" id="datatable-bad-luck">
                                <thead>
                                    <th>Year</th>
                                    <th>Week</th>
                                    <th>Manager</th>
                                    <th>Score</th>
                                    <th>Weekly Rank</th>
                                    <th>Opponent</th>
                                    <th>Opponent Score</th>
                                    <th>Margin</th>
                                </thead>
                                <tbody>
                                    <!-- Table body will be populated by JS -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-header" style="direction: ltr; text-align: right">
                            <h4>5 Point Losses</h4>
                        </div>
                        <div class="card-body" style="background: #fff; direction: ltr">
                            <table class="table table-responsive table-striped nowrap" id="datatable-close-losses">
                                <thead>
                                    <th>Year</th>
                                    <th>Week</th>
                                    <th>Manager</th>
                                    <th>Score</th>
                                    <th>Opponent</th>
                                    <th>Opponent Score</th>
                                    <th>Margin</th>
                                </thead>
                                <tbody>
                                    <!-- Table body will be populated by JS -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<?php include 'footer.php'; ?>

<script type="text/javascript">
    function ordinal(n) {
        var s = ['th', 'st', 'nd', 'rd'];
        var v = n % 100;
        return n + (s[(v - 20) % 10] || s[v] || s[0]);
    }

    function luckColumns() {
        return [
            { data: 'year' },
            { data: 'week' },
            { data: 'manager', render: function(data, type, row) {
                return `<a href="/rosters.php?year=${row.year}&week=${row.week}&manager=${data}">${data}</a>`;
            }},
            { data: 'score' },
            { data: 'rank', render: function(data, type, row) {
                return ordinal(data);
            }},
            { data: 'opponent', render: function(data, type, row) {
                return `<a href="/rosters.php?year=${row.year}&week=${row.week}&manager=${data}">${data}</a>`;
            }},
            { data: 'opponent_score' },
            { data: 'margin' },
        ];
    }

    function closeLuckColumns() {
        return [
            { data: 'year' },
            { data: 'week' },
            { data: 'manager', render: function(data, type, row) {
                return `<a href="/rosters.php?year=${row.year}&week=${row.week}&manager=${data}">${data}</a>`;
            }},
            { data: 'score' },
            { data: 'opponent', render: function(data, type, row) {
                return `<a href="/rosters.php?year=${row.year}&week=${row.week}&manager=${data}">${data}</a>`;
            }},
            { data: 'opponent_score' },
            { data: 'margin' },
        ];
    }

    function summaryColumns() {
        return [
            { data: 'manager' },
            { data: 'primaryCount' },
            { data: 'closeCount' },
            { data: 'total' },
        ];
    }

    $(function() {
        fetch('data/luck.php')
            .then(response => response.json())
            .then(data => {
                $('#datatable-good-luck-summary').DataTable({
                    data: data.goodLuckSummary,
                    columns: summaryColumns(),
                    paging: false,
                    searching: false,
                    info: false,
                    order: [[3, 'desc']],
                });

                $('#datatable-bad-luck-summary').DataTable({
                    data: data.badLuckSummary,
                    columns: summaryColumns(),
                    paging: false,
                    searching: false,
                    info: false,
                    order: [[3, 'desc']],
                });

                $('#datatable-good-luck').DataTable({
                    data: data.goodLuck,
                    columns: luckColumns(),
                    pageLength: 10,
                    order: [[0, 'desc'], [1, 'desc']],
                });

                $('#datatable-bad-luck').DataTable({
                    data: data.badLuck,
                    columns: luckColumns(),
                    pageLength: 10,
                    order: [[0, 'desc'], [1, 'desc']],
                });

                $('#datatable-close-wins').DataTable({
                    data: data.closeWins,
                    columns: closeLuckColumns(),
                    pageLength: 10,
                    order: [[0, 'desc'], [1, 'desc']],
                });

                $('#datatable-close-losses').DataTable({
                    data: data.closeLosses,
                    columns: closeLuckColumns(),
                    pageLength: 10,
                    order: [[0, 'desc'], [1, 'desc']],
                });
            });

        // Initialize the page with the tab from the URL hash if valid, else Good Luck
        var hashTab = window.location.hash.substring(1);
        if (hashTab && document.getElementById(hashTab)) {
            showCard(hashTab);
        } else {
            showCard('good-luck');
        }
    });
</script>
