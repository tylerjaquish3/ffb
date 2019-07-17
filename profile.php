<?php

$pageName = "Profile";
include 'header.php'; 
include 'sidebar.html'; 
var_dump($profileName);
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
                                    <h5>Most Wins</h5>
                                    <h5 class="text-bold-400"><?php echo $dashboardNumbers['most_wins_manager'].' ('.$dashboardNumbers['most_wins_number'].')'; ?>&#x200E;</h5>
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
                                    <h5>Most Championships</h5>
                                    <h5 class="text-bold-400"><?php echo $dashboardNumbers['most_championships_manager'].' ('.$dashboardNumbers['most_championships_number'].')'; ?>&#x200E;</h5>
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
                                    <i class="icon-user font-large-2 white"></i>
                                </div>
                                <div class="p-2 bg-green white media-body">
                                    <h5>Unique Champions</h5>
                                    <h5 class="text-bold-400"><?php echo $dashboardNumbers['unique_winners']; ?></h5>
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
                                    <h5>Seasons</h5>
                                    <h5 class="text-bold-400"><?php echo $dashboardNumbers['seasons']; ?></h5>
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
                                <table class="table table-striped table-responsive stripe compact height-450" id="datatable-wins">
                                <thead>
                                    <th>Manager</th>
                                    <th>Wins</th>
                                    <th>Losses</th>
                                    <th>Win %&#x200E;</th>
                                </thead>
                                <tbody>
                                    <?php 
                                    $result = mysqli_query($conn,"SELECT name, wins, losses, total, wins/total AS win_pct 
                                        FROM managers 
                                        JOIN (
                                            SELECT COUNT(manager1_id) AS wins, manager1_id FROM regular_season_matchups rsm 
                                            WHERE manager1_score > manager2_score GROUP BY manager1_id
                                        ) w ON w.manager1_id = managers.id

                                        JOIN (
                                            SELECT COUNT(manager1_id) AS losses, manager1_id FROM regular_season_matchups rsm 
                                            WHERE manager1_score < manager2_score GROUP BY manager1_id
                                        ) l ON l.manager1_id = managers.id

                                        JOIN (
                                            SELECT COUNT(manager1_id) AS total, manager1_id FROM regular_season_matchups rsm 
                                            GROUP BY manager1_id
                                        ) t ON t.manager1_id = managers.id"
                                    );
                                    while($row = mysqli_fetch_array($result)) 
                                    { ?>
                                        <tr>
                                            <td><?php echo $row['name']; ?></td>
                                            <td><?php echo $row['wins']; ?></td>
                                            <td><?php echo $row['losses']; ?></td>
                                            <td><?php echo $row['win_pct']; ?></td>
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
                <div class="col-xl-8 col-lg-12">
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
                <div class="col-xl-4 col-lg-12">
                    <div class="card bg-green">
                        <div class="card-body">
                            <div class="card-block">
                                <div class="media">
                                    <div class="media-body text-xs-left">
                                        Reg. Season: Cameron (55.4)&#x200E;<br />
                                        Postseason: Justin (52.2)&#x200E;
                                    </div>
                                    <div class="media-body text-xs-right">
                                        <h2>Win %&#x200E;</h2>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card bg-green">
                        <div class="card-body">
                            <div class="card-block">
                                <div class="media">
                                    <div class="media-body text-xs-left">
                                        Total: Matt (42352)&#x200E;<br />
                                        Season: Gavin (2250)&#x200E;
                                    </div>
                                    <div class="media-body text-xs-right">
                                        <h2>Points For</h2>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card bg-green">
                        <div class="card-body">
                            <div class="card-block">
                                <div class="media">
                                    <div class="media-body text-xs-left">
                                        Winning: Cameron (9)&#x200E;<br />
                                        Losing: AJ (6)&#x200E;
                                    </div>
                                    <div class="media-body text-xs-right">
                                        <h2>Streaks</h2>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card bg-green">
                        <div class="card-body">
                            <div class="card-block">
                                <div class="media">
                                    <div class="media-body text-xs-left">
                                        Reg. Season: Ben (155.4)&#x200E;<br />
                                        Postseason: Everett (152.2)&#x200E;
                                    </div>
                                    <div class="media-body text-xs-right">
                                        <h2>Biggest Blowout</h2>
                                    </div>
                                </div>
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
        
        $('#datatable-wins').DataTable({
            "searching": false,
            "paging": false,
            "info": false,
            "order": [[ 1, "desc" ]]
        });
    });

</script>
