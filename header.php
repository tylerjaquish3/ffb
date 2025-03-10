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
    <link rel="stylesheet" type="text/css" href="assets/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="assets/icomoon.css">
    <link rel="stylesheet" type="text/css" href="assets/vertical-menu.min.css">
    <link rel="stylesheet" type="text/css" href="assets/bootstrap-extended.min.css">
    <link rel="stylesheet" type="text/css" href="assets/app.min.css">
    <link rel="stylesheet" type="text/css" href="assets/colors.min.css">
    <link rel="stylesheet" type="text/css" href="assets/custom-rtl.min.css">
    <link rel="stylesheet" type="text/css" href="assets/owl.carousel.min.css">
    <link rel="stylesheet" type="text/css" href="assets/datatables.min.css">

    <!-- BEGIN Page Level CSS-->
    <link rel="stylesheet" type="text/css" href="assets/suntown.css?v=<?php echo $version; ?>">
    <link rel="stylesheet" type="text/css" href="assets/responsive.css?v=<?php echo $version; ?>">

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

    <?php include 'functions.php'; ?>