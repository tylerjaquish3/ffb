<?php
$pageName = 'Vote Info';
include 'header.php';
include 'sidebar.html';

?>

<div class="app-content content container-fluid">
    <div class="content-wrapper">
        <div class="content-header row"></div>

        <div class="content-body">

            <div class="row" id="superflex">
                <div class="col-sm-12 table-padding">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">Roster Superflex Info</h4>
                        </div>
                        <div class="card-body info-card">
                            2019: 2 QB spots<br />
                            2020: 1 QB spot, 1 Q/W/R/T spot<br />
                            Superflex was added to provide more flexibility for a season that had many unknowns (covid).<br />
                            Throughout the season, the flexibility was utilized a total of 4 times:
                            <ul>
                                <li>Tyler wk 4 - wasn't forced to, and didn't work out beneficially</li>
                                <li>Ben wk 6 - forced to based on roster make up and byes</li>
                                <li>Tyler wk 6 - forced to based on roster make up and injury</li>
                                <li>Everett wk 13 - wasn't forced to, and didn't work out beneficially</li>
                            </ul>
                            It seemed that if QBs were effected by covid, the manager was able to pivot to another QB in all cases.
                            Also, it appears that the 2021 season has fewer unknowns than 2020.<br />
                            <b>Proposal:</b> Change the Q/W/R/T position back to a QB position.
                        </div>
                    </div>
                </div>
            </div>
            <div class="row" id="ir">
                <div class="col-sm-12 table-padding">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">Roster IR Info</h4>
                        </div>
                        <div class="card-body info-card">
                            2019: 0 IR spots<br />
                            2020: 4 IR spots<br />
                            2021: 2 IR spots<br />
                            The NFL's IR designation changed in 2020 to not be a season-ending or 8-week designation, but can now be as few as 3 weeks.
                            IR spots were added in our fantasy league to provide more flexibility for a season that had many unknowns (covid).<br />
                            2020 numbers (reg. season):<br />
                            <ul>
                                <li>4 IR spots used three times in a week (Justin wk 3, Cole wk 12, Cole wk 13)</li>
                                <li>3 IR spots used twelve times</li>
                                <li>Total weeks = 130 (10 managers for 13 reg. season weeks)</li>
                                <li>Therefore, the extra spots were used 15 times in 130 weeks</li>
                            </ul>
                            We don't have exact numbers on how many IR uses were related to covid, but best guess is less than 10%.
                            Regardless, the IR spot is useful because more players are put on real life IR and return in three weeks.
                            It helps managers not have to use bench spots for injured players. <br />

                            <b>Proposal:</b> Change the number of IR spots to 1 or 0.
                        </div>
                    </div>
                </div>
            </div>
            
        </div>
    </div>
</div>

<?php include 'footer.html'; ?>

<script type="text/javascript">
    $(document).ready(function () {


        $('#datatable-wins').DataTable({
            "searching": false,
            "paging": false,
            "info": false,
            "order": [
                [3, "desc"]
            ]
        });

        // Misc tables
        $('#datatable-misc1').DataTable({
            "searching": false,
            "paging": false,
            "info": false,
            "order": [
                [1, "desc"]
            ]
        });
        $('#datatable-misc2').DataTable({
            "searching": false,
            "paging": false,
            "info": false,
            "order": [
                [1, "desc"]
            ]
        });

    });


</script>