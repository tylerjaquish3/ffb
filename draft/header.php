<!DOCTYPE html>
<html lang="en" data-textdirection="rtl" class="loading">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta http-equiv="Cache-Control" content="public">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">

    <title>Suntown FFB</title>

    <link rel="icon" type="image/png" href="/images/favicon.jpg">

    <meta property="og:title" content="Suntown Fantasy Football League" />
    <meta property="og:description" content="The best league in all the land" />
    <meta property="og:url" content="http://suntownffb.us" />
    <meta property="og:image" content="http://suntownffb.us/images/favicon.jpg" />

    <!-- BEGIN VENDOR CSS-->
    <link rel="stylesheet" type="text/css" href="/assets/bootstrap.min.css" defer>
    <!-- <link rel="stylesheet" type="text/css" href="/assets/bootstrap-extended.min.css" defer> -->
    <link rel="stylesheet" type="text/css" href="/assets/app.min.css" defer>
    <link rel="stylesheet" type="text/css" href="/assets/icomoon.css" defer>
    <link rel="stylesheet" type="text/css" href="/assets/dataTables.min.css" defer>

    <!-- BEGIN Custom CSS-->
    <link rel="stylesheet" type="text/css" href="/assets/suntown.css">

    <script src="/assets/dataTables.js" type="text/javascript"></script>
    <script src="/assets/tether.min.js" type="text/javascript"></script>
    <script src="/assets/bootstrap.min.js" type="text/javascript"></script>

</head>

<body>

    <?php
    include '../connections.php';

    function dd($text) {
        var_dump($text);die;
    }

    function desigIcon($id, $hasNote)
    {
        $note = '';
        if ($hasNote) {
            $note = '&nbsp;<i class="icon-file-text" title="Note"></i>';
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

    function getManagerName($id) 
    {
        // These are in order by ID
        $managers = ['Tyler', 'AJ', 'Gavin', 'Matt', 'Cameron', 'Andy', 'Everett', 'Justin', 'Cole', 'Ben'];
    
        return $managers[$id-1];
    }

    // This should happen after each pick so the baseline could move up/down/stay based on picks that happen
    function updateVols()
    {
        global $conn;

        $positions = [
            ['pos' => 'QB', 'limit' => 20, 'base' => null],
            ['pos' => 'RB', 'limit' => 30, 'base' => null],
            ['pos' => 'WR', 'limit' => 30, 'base' => null],
            ['pos' => 'TE', 'limit' => 10, 'base' => null],
        ];
        foreach ($positions as &$pos) {
            $pos['base'] = getBaseline($pos['pos'], $pos['limit']);
        }
        
        $sql = $conn->prepare("UPDATE preseason_rankings SET vols = ? WHERE id = ?");
        $result = mysqli_query($conn, "SELECT * FROM preseason_rankings ORDER BY proj_points DESC");
        while ($row = mysqli_fetch_array($result)) {

            $search = array_search($row['position'], array_column($positions, 'pos'));
            // dd($positions[$search]);
            $baseline = $positions[$search]['base'];
            // dd($baseline);
            $vols = $row['proj_points'] - $baseline;
            $sql->bind_param('ii', $vols, $row['id']);
            $sql->execute();
        }
    }

    function getBaseline($pos, $limit)
    {
        global $conn;
        $result = mysqli_query($conn, "SELECT * FROM preseason_rankings where position = '$pos' ORDER BY proj_points DESC LIMIT $limit");
        while ($row = mysqli_fetch_array($result)) {
            $base = $row['proj_points'];
        }

        return (int)$base;
    }

    ?>