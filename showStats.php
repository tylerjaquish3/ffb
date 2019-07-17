<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">

    <!-- Meta, title, CSS, favicons, etc. -->
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Suntown FFB</title>

    <link rel="icon" href="images/vb-favicon.ico">
    <link rel="apple-touch-icon-precomposed" href="/images/favicon-152.png">

    <script src="https://code.jquery.com/jquery-3.4.1.min.js" integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo=" crossorigin="anonymous"></script>

    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>

    <link href="https://cdn.datatables.net/1.10.19/css/jquery.dataTables.min.css" rel="stylesheet">
    <script src="https://cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js"></script>
    
</head>

<body>
    <div class="container">

        <?php
            //local connection
            $servername = "localhost";
            $username = "root";
            $password = "";
            $dbname = "ffb";

            // Create connection
            $conn = new mysqli($servername, $username, $password, $dbname, '3307');
            // Check connection
            if ($conn->connect_error) {
                die("Connection failed: " . $conn->connect_error);
            } 
        ?>

        <nav class="navbar navbar-expand-lg navbar-light bg-light">
            <a class="navbar-brand" href="#">Home</a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav mr-auto">
                    <li class="nav-item active">
                        <a class="nav-link" href="#">Regular Season</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">Playoffs</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            Profiles
                        </a>
                        <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                            <a class="dropdown-item" href="#">Tyler</a>
                            <a class="dropdown-item" href="#">Andy</a>
                            <a class="dropdown-item" href="#">AJ</a>
                            <a class="dropdown-item" href="#">Gavin</a>
                            <a class="dropdown-item" href="#">Everett</a>
                            <a class="dropdown-item" href="#">Cole</a>
                            <a class="dropdown-item" href="#">Justin</a>
                            <a class="dropdown-item" href="#">Cameron</a>
                            <a class="dropdown-item" href="#">Matt</a>
                            <a class="dropdown-item" href="#">Ben</a>
                        </div>
                    </li>
                </ul>
            </div>
        </nav>

        <div class="row">
            <div class="col-xs-4">
                <div class="title_left">
                    <h1>Finishes</h1>
                </div>
            </div>
        </div>

        <div class="clearfix"></div>

        <div class="row">
            <div class="col-xs-12">
                <div class="x_panel table_panel">
                    <div class="x_content">
                        <div class="col-xs-12">
                            <table class="table  table-bordered table-striped table-responsive stripe compact" id="datatable-events">
                                <thead>
                                    <th>Year</th>
                                    <th>Manager</th>
                                    <th>Finish</th>
                                </thead>
                                <tbody>
                                    <?php 
                                    $result = mysqli_query($conn,"SELECT * FROM finishes JOIN managers on managers.id = finishes.manager_id");
                                    while($event = mysqli_fetch_array($result)) 
                                    { ?>
                                        <tr>
                                            <td><?php echo $event['year']; ?></td>
                                            <td><?php echo $event['name']; ?></td>
                                            <td><?php echo $event['finish']; ?></td>
                                        </tr>

                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>


<script type="text/javascript">

    $(document).ready(function(){
        
        $('#datatable-events').DataTable({
            stateSave: true,
            "order": [[ 1, "desc" ]]
        });
    });

</script>