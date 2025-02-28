<?php

$pageName = "20th Anniversary Draft Weekend";
include '../sidebar.html';

?>

<!DOCTYPE html>
<html lang="en" data-textdirection="rtl" class="loading">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">

    <title><?php echo isset($pageName) ? $pageName. ' | Suntown FFB' : 'Suntown FFB'; ?></title>
    <?php $version = "v2.6.5"; 
    $vDate = "(12/31/24)"; ?>

    <link rel="icon" type="image/png" href="/images/football.ico">

    <meta property="og:title" content="Suntown Fantasy Football League" />
    <meta property="og:description" content="The best league in all the land" />
    <meta property="og:url" content="http://suntownffb.us" />
    <meta property="og:image" content="http://suntownffb.us/images/football.ico" />

    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-touch-fullscreen" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <!-- BEGIN VENDOR CSS-->
    <link rel="stylesheet" type="text/css" href="/assets/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="/assets/icomoon.css">
    <link rel="stylesheet" type="text/css" href="/assets/vertical-menu.min.css">
    <link rel="stylesheet" type="text/css" href="/assets/bootstrap-extended.min.css">
    <link rel="stylesheet" type="text/css" href="/assets/app.min.css">
    <link rel="stylesheet" type="text/css" href="/assets/colors.min.css">
    <link rel="stylesheet" type="text/css" href="/assets/custom-rtl.min.css">
    <link rel="stylesheet" type="text/css" href="/assets/owl.carousel.min.css">
    <link rel="stylesheet" type="text/css" href="/assets/datatables.min.css">

    <!-- BEGIN Page Level CSS-->
    <link rel="stylesheet" type="text/css" href="/assets/suntown.css?v=<?php echo $version; ?>">
    <link rel="stylesheet" type="text/css" href="/assets/responsive.css?v=<?php echo $version; ?>">

</head>

<body data-open="click" data-menu="vertical-menu" data-col="2-columns" class="vertical-layout vertical-menu 2-columns fixed-navbar">

    <!-- navbar-fixed-top-->
    <nav class="header-navbar navbar navbar-with-menu navbar-fixed-top navbar-semi-dark navbar-shadow">
        <div class="navbar-wrapper">
            <div class="navbar-header">
                <ul class="nav navbar-nav">
                    <li class="nav-item mobile-menu hidden-md-up float-xs-left">
                        <a class="nav-link nav-menu-main menu-toggle hidden-xs"><i class="icon-menu5 font-large-1"></i></a>
                    </li>
                    <li class="nav-item tab-size">
                        <a href="/"><h2>Suntown FFB</h2></a>
                    </li>
                    <li class="nav-item tab-size-alt">
                        <a href="/"><h2>FFB</h2></a>
                    </li>
                    <li class="nav-item hidden-md-up float-xs-right">
                        <a data-toggle="collapse" data-target="#navbar-mobile" class="nav-link open-navbar-container">
                            <i class="icon-ellipsis pe-2x icon-icon-rotate-right-right"></i>
                        </a>
                    </li>
                </ul>
            </div>
            <div class="navbar-container content container-fluid">
                <div id="navbar-mobile">
                    <h2><?php echo $pageName ?></h2>
                </div>
            </div>
        </div>
    </nav>

    <div class="app-content content container-fluid">
        <div class="content-wrapper">
            <div class="content-header row"></div>

            <div class="content-body">

                <div class="row">
                    <div class="col-sm-12">
                        
                        <img src="/year20/save-date.jpg" class="img-fluid" alt="Save the Date" style="width: 100%;">
                                
                    </div>
                </div>

                <div class="row mt-2">
                    <div class="col-sm-12">
                        <div class="card">
                            <div class="card-header">
                                <h4>Draft Weekend Details</h4>
                            </div>
                            <div class="card-body">
                                <div class="card-block" style="direction: ltr; text-align: center;">
                                    <h2>20 Seasons. 10 Dudes. <br />5 Competitions. 17 Draft Rounds. <br />1 Awesome Weekend.</h2>

                                    <p>Celebrating 20 years together as a league, this year's draft order and draft will be determined in consecutive days, August 23 & 24,
                                        at Tyler's home in Cheney. There will be accomodations for all ten of us, and whoever wants to come a day early, can.</p>

                                    <p>On Saturday, we will share a solid breakfast before "lacing up the cleats" to determine the draft order. 
                                        It will be decided by competing in a series of events that are still being designed. 
                                        There will be elements of both skill and luck involved in each event, and prior practice is NOT required. 
                                        We'll break for lunch and then continue the events until the draft order is set. 
                                        The day will end with a some good food, drinks, games, and a bonfire.
                                    </p>

                                    <p>On Sunday, we will again share a good breakfast and then be treated to a special 20th Anniversary Presentation.
                                        The new punishment, Matt's draft shirt, will be revealed and donned, as well as his new team name. 
                                        This is the time for any last minute draft preparation or play some yard games (Kubb, Beersbie, Cornhole, 4 Square Volleyball, etc.)
                                        We'll have a BBQ lunch and then start the draft in a similar fashion to years past.
                                    </p>

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
