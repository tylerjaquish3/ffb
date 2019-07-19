<?php

$pageName = $_GET['id']."'s Profile";
include 'header.php'; 
include 'sidebar.html'; 

$result = mysqli_query($conn, "SELECT * FROM managers WHERE name = '".$_GET['id']."'");
while($row = mysqli_fetch_array($result)) 
{
    $managerId = $row['id'];
}

?>

<div class="app-content content container-fluid">
    <div class="content-wrapper">
        <div class="content-header row"></div>

        <div class="content-body">
            <!-- Statistics -->
            <div class="row">
                <div class="col-xl-3 col-lg-6 col-xs-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="media">
                                <div class="p-2 text-xs-center bg-green media-left media-middle">
                                    <i class="icon-star-full font-large-2 white"></i>
                                </div>
                                <div class="p-2 bg-green white media-body">
                                    <h5>Total Points</h5>
                                    <h5 class="text-bold-400"><?php echo $profileNumbers['totalPoints'].' (Rank: '.$profileNumbers['totalPointsRank'].')'; ?>&#x200E;</h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-lg-6 col-xs-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="media">
                                <div class="p-2 text-xs-center bg-green media-left media-middle">
                                    <i class="icon-stats-bars font-large-2 white"></i>
                                </div>
                                <div class="p-2 bg-green white media-body">
                                    <h5>Playoff Record</h5>
                                    <h5 class="text-bold-400"><?php echo $profileNumbers['playoffRecord'].' (Rank: '.$profileNumbers['playoffRecordRank'].')'; ?>&#x200E;</h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-lg-6 col-xs-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="media">
                                <div class="p-2 text-xs-center bg-green media-left media-middle">
                                    <i class="icon-trophy font-large-2 white"></i>
                                </div>
                                <div class="p-2 bg-green white media-body">
                                    <h5>Championships</h5>
                                    <h5 class="text-bold-400"><?php echo $profileNumbers['championships'].' ('.$profileNumbers['championshipYears'].')'; ?>&#x200E;</h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-lg-6 col-xs-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="media">
                                <div class="p-2 text-xs-center bg-green media-left media-middle">
                                    <i class="icon-calendar font-large-2 white"></i>
                                </div>
                                <div class="p-2 bg-green white media-body">
                                    <h5>Reg. Season Record</h5>
                                    <h5 class="text-bold-400"><?php echo $profileNumbers['record'].' (Rank: '.$profileNumbers['recordRank'].')'; ?>&#x200E;</h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!--/ Statistics -->
            <!--project Total Earning, visit & post-->
            <div class="row">
                <div class="col-xl-4 col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="position-relative">
                                <h3>Record vs. Opponent</h3>
                                <a class="btn btn-primary" id="postseason">Postseason</a>
                                <a class="btn btn-primary" id="regSeason">Regular Season</a>
                                <table class="table table-striped table-responsive stripe compact height-450" id="datatable-regSeason">
                                    <thead>
                                        <th>Manager</th>
                                        <th>Wins</th>
                                        <th>Losses</th>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $result = mysqli_query($conn,"SELECT name, SUM(CASE
                                                WHEN manager1_score > manager2_score THEN 1
                                                ELSE 0
                                            END) AS wins,
                                            SUM(CASE
                                                WHEN manager1_score < manager2_score THEN 1
                                                ELSE 0
                                            END) AS losses
                                            FROM regular_season_matchups rsm
                                            JOIN managers ON managers.id = rsm.manager2_id
                                            WHERE manager1_id = $managerId
                                            GROUP BY manager2_id
                                            ORDER BY wins DESC"
                                        );
                                        while($row = mysqli_fetch_array($result)) 
                                        { ?>
                                            <tr>
                                                <td><?php echo $row['name']; ?></td>
                                                <td><?php echo $row['wins']; ?></td>
                                                <td><?php echo $row['losses']; ?></td>
                                            </tr>

                                        <?php } ?>
                                    </tbody>
                                </table>

                                <table class="table table-striped table-responsive stripe compact height-450" id="datatable-postseason" style="display:none;">
                                    <thead>
                                        <th>Manager</th>
                                        <th>Wins</th>
                                        <th>Losses</th>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $result = mysqli_query($conn,"SELECT name, w.wins+w2.wins AS totalWins, l.losses+l2.losses AS totalLosses 
                                            FROM managers 
                                            JOIN (
                                                SELECT SUM(CASE
                                                WHEN manager1_id = $managerId AND manager1_score > manager2_score THEN 1
                                                ELSE 0
                                                END) AS wins, manager2_id 
                                                FROM playoff_matchups rsm 
                                                GROUP BY manager2_id
                                            ) w ON w.manager2_id = managers.id

                                            JOIN (
                                                SELECT SUM(CASE
                                                WHEN manager2_id = $managerId AND manager2_score > manager1_score THEN 1
                                                ELSE 0
                                                END) AS wins, manager1_id 
                                                FROM playoff_matchups rsm 
                                                GROUP BY manager1_id
                                            ) w2 ON w2.manager1_id = managers.id

                                            JOIN (
                                                SELECT SUM(CASE
                                                WHEN manager1_id = $managerId AND manager1_score < manager2_score THEN 1
                                                ELSE 0
                                                END) AS losses, manager2_id 
                                                FROM playoff_matchups rsm 
                                                GROUP BY manager2_id
                                            ) l ON l.manager2_id = managers.id

                                            JOIN (
                                                SELECT SUM(CASE
                                                WHEN manager2_id = $managerId AND manager2_score < manager1_score THEN 1
                                                ELSE 0
                                                END) AS losses, manager1_id 
                                                FROM playoff_matchups rsm 
                                                GROUP BY manager1_id
                                            ) l2 ON l2.manager1_id = managers.id
                                            WHERE name != '".$_GET['id']."'"
                                        );
                                        while($row = mysqli_fetch_array($result)) 
                                        { ?>
                                            <tr>
                                                <td><?php echo $row['name']; ?></td>
                                                <td><?php echo $row['totalWins']; ?></td>
                                                <td><?php echo $row['totalLosses']; ?></td>
                                            </tr>

                                        <?php } ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-8 col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="card-block">
                                <canvas id="posts-visits" class="height-400"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!--/project Total Earning, visit & post-->
            <!-- projects table with monthly chart -->
            <div class="row">
                <div class="col-sm-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">Ongoing Projects</h4>
                            <a class="heading-elements-toggle"><i class="icon-ellipsis font-medium-3"></i></a>
                            <div class="heading-elements">
                                <ul class="list-inline mb-0">
                                    <li><a data-action="reload"><i class="icon-reload"></i></a></li>
                                    <li><a data-action="expand"><i class="icon-expand2"></i></a></li>
                                </ul>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="card-block">
                                <p class="m-0">Total ongoing projects 6<span class="float-xs-right"><a href="#" target="_blank">Project Summary <i class="icon-arrow-right2"></i></a></span></p>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>Project</th>
                                            <th>Owner</th>
                                            <th>Priority</th>
                                            <th>Progress</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td class="text-truncate">ReactJS App</td>
                                            <td class="text-truncate">
                                                <span class="avatar avatar-xs"><img src="images/avatar-s-4.png" alt="avatar"></span> <span>Sarah W.</span>
                                            </td>
                                            <td class="text-truncate"><span class="tag tag-success">Low</span></td>
                                            <td class="valign-middle">
                                                <progress value="88" max="100" class="progress progress-xs progress-success m-0">88%</progress>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="text-truncate">Fitness App</td>
                                            <td class="text-truncate">
                                                <span class="avatar avatar-xs"><img src="images/avatar-s-5.png" alt="avatar"></span> <span>Edward C.</span>
                                            </td>
                                            <td class="text-truncate"><span class="tag tag-warning">Medium</span></td>
                                            <td class="valign-middle">
                                                <progress value="55" max="100" class="progress progress-xs progress-warning m-0">55%</progress>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="text-truncate">SOU plugin</td>
                                            <td class="text-truncate">
                                                <span class="avatar avatar-xs"><img src="images/avatar-s-6.png" alt="avatar"></span> <span>Carol E.</span>
                                            </td>
                                            <td class="text-truncate"><span class="tag tag-danger">Critical</span></td>
                                            <td class="valign-middle">
                                                <progress value="25" max="100" class="progress progress-xs progress-danger m-0">25%</progress>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="text-truncate">Android App</td>
                                            <td class="text-truncate">
                                                <span class="avatar avatar-xs"><img src="images/avatar-s-7.png" alt="avatar"></span> <span>Gregory L.</span>
                                            </td>
                                            <td class="text-truncate"><span class="tag tag-success">Low</span></td>
                                            <td class="valign-middle">
                                                <progress value="95" max="100" class="progress progress-xs progress-success m-0">95%</progress>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="text-truncate">ABC Inc. UI/UX</td>
                                            <td class="text-truncate">
                                                <span class="avatar avatar-xs"><img src="images/avatar-s-8.png" alt="avatar"></span> <span>Susan S.</span>
                                            </td>
                                            <td class="text-truncate"><span class="tag tag-warning">Medium</span></td>
                                            <td class="valign-middle">
                                                <progress value="45" max="100" class="progress progress-xs progress-warning m-0">45%</progress>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="text-truncate">Product UI</td>
                                            <td class="text-truncate">
                                                <span class="avatar avatar-xs"><img src="images/avatar-s-9.png" alt="avatar"></span> <span>Walter K.</span>
                                            </td>
                                            <td class="text-truncate"><span class="tag tag-danger">Critical</span></td>
                                            <td class="valign-middle">
                                                <progress value="15" max="100" class="progress progress-xs progress-danger m-0">15%</progress>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="text-truncate">Fitness App</td>
                                            <td class="text-truncate">
                                                <span class="avatar avatar-xs"><img src="images/avatar-s-5.png" alt="avatar"></span> <span>Edward C.</span>
                                            </td>
                                            <td class="text-truncate"><span class="tag tag-warning">Medium</span></td>
                                            <td class="valign-middle">
                                                <progress value="55" max="100" class="progress progress-xs progress-warning m-0">55%</progress>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.html'; ?>

<script type="text/javascript">

    $(document).ready(function(){

        $('#postseason').click(function () {
            $('#datatable-regSeason').hide();
            $('#datatable-postseason').show();
        });

        $('#regSeason').click(function () {
            $('#datatable-regSeason').show();
            $('#datatable-postseason').hide();
        });
        
        $('#datatable-regSeason').DataTable({
            "searching": false,
            "paging": false,
            "info": false,
            "order": [[ 1, "desc" ]]
        });

        $('#datatable-postseason').DataTable({
            "searching": false,
            "paging": false,
            "info": false,
            "order": [[ 1, "desc" ]]
        });
    });

</script>
