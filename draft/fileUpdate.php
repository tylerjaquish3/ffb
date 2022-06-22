<!DOCTYPE html>
<html lang="en" data-textdirection="rtl" class="loading">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">

    <title>Suntown FFB</title>

    <link rel="icon" type="image/png" href="/images/favicon.jpg">

    <meta property="og:title" content="Suntown Fantasy Football League" />
    <meta property="og:description" content="The best league in all the land" />
    <meta property="og:url" content="http://suntownffb.us" />
    <meta property="og:image" content="http://suntownffb.us/images/favicon.jpg" />

    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-touch-fullscreen" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <!-- BEGIN VENDOR CSS-->
    <link rel="stylesheet" type="text/css" href="../assets/bootstrap.min.css">
    <!-- font icons-->
    <link rel="stylesheet" type="text/css" href="../assets/icomoon.css">
    <link rel="stylesheet" type="text/css" href="../assets/flag-icon.min.css">
    <link rel="stylesheet" type="text/css" href="../assets/pace.css">
    <!-- END VENDOR CSS-->
    <!-- BEGIN ROBUST CSS-->
    <link rel="stylesheet" type="text/css" href="../assets/bootstrap-extended.min.css">
    <link rel="stylesheet" type="text/css" href="../assets/app.min.css">
    <link rel="stylesheet" type="text/css" href="../assets/colors.min.css">
    <!-- END ROBUST CSS-->
    <link rel="stylesheet" type="text/css" href="../assets/suntown.css">
    <link rel="stylesheet" type="text/css" href="../assets/responsive.css">

</head>

<body data-open="click" data-menu="vertical-menu" data-col="2-columns" class="vertical-layout vertical-menu 2-columns  fixed-navbar">

    <!-- navbar-fixed-top-->
    <nav class="header-navbar navbar navbar-with-menu navbar-fixed-top navbar-semi-dark navbar-shadow">
        <div class="navbar-wrapper">
            <div class="navbar-header">
                <ul class="nav navbar-nav">
                    <li class="nav-item mobile-menu hidden-md-up float-xs-left"><a class="nav-link nav-menu-main menu-toggle hidden-xs"><i class="icon-menu5 font-large-1"></i></a></li>
                    <li class="nav-item tab-size">
                        <a href="/"><h2>Suntown FFB</h2></a>
                    </li>
                    <li class="nav-item tab-size-alt">
                        <a href="/"><h2>FFB</h2></a>
                    </li>
                    <li class="nav-item hidden-md-up float-xs-right"><a data-toggle="collapse" data-target="#navbar-mobile" class="nav-link open-navbar-container"><i class="icon-ellipsis pe-2x icon-icon-rotate-right-right"></i></a></li>
                </ul>
            </div>
            <div class="navbar-container content container-fluid">
                <div id="navbar-mobile">
                    <h2>File Update</h2>
                </div>
            </div>
        </div>
    </nav>

    <?php
        $year = date('Y');
    ?>

    <div class="app-content content container-fluid">
        <div class="content-wrapper">
            <div class="content-header row"></div>

            <div class="content-body">
            
                <div class="row">
                    
                    <div class="col-xl-3 col-lg-6 col-xs-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="media">
                                    <div class="p-2 text-xs-center bg-green-ffb media-left media-middle">
                                        <i class="icon-star-full font-large-2 white"></i>
                                    </div>
                                    <div class="p-2 bg-green-ffb media-body">
                                        <?php $disabled = 'disabled'; if(file_exists('files/'.$year.'/teamChange.csv')) { $disabled = ''; } ?>
                                        <h5>Team Changes</h5>
                                        <h6><a href="https://www.fantasypros.com/nfl/players/team-changes.php" target="_blank">Get Data</a></h6>
                                        <form action="preseasonFileUpdate.php" method="POST">
                                            <input type="hidden" name="team-change" value="true">
                                            <button type="submit" <?php echo $disabled; ?>>Update</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-lg-6 col-xs-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="media">
                                    <div class="p-2 text-xs-center bg-green-ffb media-left media-middle">
                                        <i class="icon-star-full font-large-2 white"></i>
                                    </div>
                                    <div class="p-2 bg-green-ffb media-body">
                                        <?php $disabled = 'disabled'; if(file_exists('files/'.$year.'/rookies.csv')) { $disabled = ''; } ?>
                                        <h5>Rookies</h5>
                                        <h6><a href="" target="_blank">Get Data</a></h6>
                                        <form action="preseasonFileUpdate.php" method="POST">
                                            <input type="hidden" name="player-rookies" value="true">
                                            <button type="submit" <?php echo $disabled; ?>>Update</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                </div>
                <div class="row">
                    <div class="col-xl-3 col-lg-6 col-xs-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="media">
                                    <div class="p-2 text-xs-center bg-green-ffb media-left media-middle">
                                        <i class="icon-star-full font-large-2 white"></i>
                                    </div>
                                    <div class="p-2 bg-green-ffb media-body">
                                        <?php $disabled = 'disabled'; if(file_exists('files/'.$year.'/oline.csv')) { $disabled = ''; } ?>
                                        <h5>Team O-Line</h5>
                                        <h6><a href="" target="_blank">Get Data</a></h6>
                                        <form action="preseasonFileUpdate.php" method="POST">
                                            <input type="hidden" name="team-oline" value="true">
                                            <button type="submit" <?php echo $disabled; ?>>Update</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-lg-6 col-xs-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="media">
                                    <div class="p-2 text-xs-center bg-green-ffb media-left media-middle">
                                        <i class="icon-star-full font-large-2 white"></i>
                                    </div>
                                    <div class="p-2 bg-green-ffb media-body">
                                        <?php $disabled = 'disabled'; if(file_exists('files/'.$year.'/sos.csv')) { $disabled = ''; } ?>
                                        <h5>Team SoS</h5>
                                        <h6><a href="https://www.fantasypros.com/nfl/points-allowed.php" target="_blank">Get Data</a></h6>
                                        <form action="preseasonFileUpdate.php" method="POST">
                                            <input type="hidden" name="team-sos" value="true">
                                            <button type="submit" <?php echo $disabled; ?>>Update</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-lg-6 col-xs-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="media">
                                    <div class="p-2 text-xs-center bg-green-ffb media-left media-middle">
                                        <i class="icon-star-full font-large-2 white"></i>
                                    </div>
                                    <div class="p-2 bg-green-ffb media-body">
                                        <?php $disabled = 'disabled'; if(file_exists('files/'.$year.'/byes.csv')) { $disabled = ''; } ?>
                                        <h5>Team Byes</h5>
                                        <h6><a href="http://www.espn.com/nfl/schedulegrid" target="_blank">Get Data</a></h6>
                                        <form action="preseasonFileUpdate.php" method="POST">
                                            <input type="hidden" name="team-byes" value="true">
                                            <button type="submit" <?php echo $disabled; ?>>Update</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    
                    <div class="col-xl-3 col-lg-6 col-xs-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="media">
                                    <div class="p-2 text-xs-center bg-green-ffb media-left media-middle">
                                        <i class="icon-star-full font-large-2 white"></i>
                                    </div>
                                    <div class="p-2 bg-green-ffb media-body">
                                        <?php $disabled = 'disabled'; if(file_exists('files/'.$year.'-projPoints.csv')) { $disabled = ''; } ?>
                                        <h5>Player Proj Points</h5>
                                        <h6><a href="" target="_blank">Get Data</a></h6>
                                        <form action="preseasonFileUpdate.php" method="POST">
                                            <input type="hidden" name="player-projPoints" value="true">
                                            <button type="submit" <?php echo $disabled; ?>>Update</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-lg-6 col-xs-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="media">
                                    <div class="p-2 text-xs-center bg-green-ffb media-left media-middle">
                                        <i class="icon-star-full font-large-2 white"></i>
                                    </div>
                                    <div class="p-2 bg-green-ffb media-body">
                                        <h5>Player Points</h5>
                                        <h6><a href="" target="_blank">Get Data</a></h6>
                                        <form action="preseasonFileUpdate.php" method="POST">
                                            <input type="hidden" name="player-points" value="true">
                                            <button type="submit">Update</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-xl-3 col-lg-6 col-xs-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="media">
                                    <div class="p-2 text-xs-center bg-green-ffb media-left media-middle">
                                        <i class="icon-star-full font-large-2 white"></i>
                                    </div>
                                    <div class="p-2 bg-green-ffb media-body">
                                        <?php $disabled = 'disabled'; if(file_exists('files/'.$year.'/adp.csv')) { $disabled = ''; } ?>
                                        <h5>Player ADP</h5>
                                        <h6><a href="" target="_blank">Get Data</a></h6>
                                        <form action="preseasonFileUpdate.php" method="POST">
                                            <input type="hidden" name="player-adp" value="true">
                                            <button type="submit" <?php echo $disabled; ?>>Update</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                </div>
            </div>
        </div>
    </div>

</body>
</html>