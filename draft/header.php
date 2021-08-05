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

    <!-- BEGIN VENDOR CSS-->
    <link rel="stylesheet" type="text/css" href="/assets/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="/assets/bootstrap-extended.min.css">
    <link rel="stylesheet" type="text/css" href="/assets/app.min.css">

    <!-- BEGIN Custom CSS-->
    <link rel="stylesheet" href="/assets/dataTables.min.css" >
    <link rel="stylesheet" type="text/css" href="/assets/suntown.css">

    <script src="/assets/dataTables.js"></script>
    <script src="/assets/tether.min.js" type="text/javascript"></script>
    <script src="/assets/bootstrap.min.js" type="text/javascript"></script>

</head>

<body data-open="click" data-menu="vertical-menu" data-col="2-columns" class="vertical-layout vertical-menu 2-columns  fixed-navbar">

    <!-- navbar-fixed-top-->
    <nav class="header-navbar navbar navbar-with-menu navbar-fixed-top navbar-semi-dark navbar-shadow">
        <div class="navbar-wrapper">
            <div class="navbar-header">
                <ul class="nav navbar-nav">
                    <li class="nav-item mobile-menu hidden-md-up float-xs-left"><a class="nav-link nav-menu-main menu-toggle hidden-xs"><i class="icon-menu5 font-large-1"></i></a></li>
                    <li class="nav-item">
                        <h2>Suntown FFB</h2>
                    </li>
                    <li class="nav-item hidden-md-up float-xs-right"><a data-toggle="collapse" data-target="#navbar-mobile" class="nav-link open-navbar-container"><i class="icon-ellipsis pe-2x icon-icon-rotate-right-right"></i></a></li>
                </ul>
            </div>
            <div class="navbar-container content container-fluid">
                <div id="navbar-mobile">
                    <h2><?php echo $pageName ?></h2>
                </div>
            </div>
        </div>
    </nav>

    <?php

    function desigIcon($id, $hasNote)
    {
        $note = '';
        if ($hasNote) {
            $note = '<i class="icon-file-text" title="Note"></i>';
        }
        if ($id == 'bust') {
            return '<i class="icon-aid-kit" title="Bust"></i>'.$note;
        }
        if ($id == 'value') {
            return '<i class="icon-price-tag" title="Value"></i>'.$note;
        }
        if ($id == 'sleeper') {
            return '<i class="icon-sleepy2" title="Sleeper"></i>'.$note;
        }
        if ($id == 'breakout') {
            return '<i class="icon-star-full" title="Breakout"></i>'.$note;
        }
        return $note;
    }

    include '../connections.php';

    ?>