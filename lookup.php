<?php

$pageName = "Standings Lookup";
include 'header.php';
include 'sidebar.html';

?>

<div class="app-content content container-fluid">
    <div class="content-wrapper">
        <div class="content-header row"></div>

        <div class="content-body">
            <div class="row">

                <div class="col-xs-12 col-md-4 table-padding">
                    <div class="card">
                        <div class="card-header">
                            <h4>Results</h4>
                            <span id="count"></span>
                        </div>
                        <div class="card-body" style="background: #fff; direction: ltr">
                            <table class="table table-responsive" id="datatable-results">
                                <thead>
                                    <th>Year</th>
                                    <th>Week</th>
                                    <th>Record</th>
                                    <th>Points</th>
                                </thead>
                                <tbody id="postData"></tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-xs-12 col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h4 style="float: right">Standings Lookup</h4>
                        </div>
                        <div class="card-body" style="background: #fff; direction: ltr; text-align: center;">
                            <h3>When was the last time ... </h3>
                            <select id="manager1-select">
                                <?php
                                $result = mysqli_query($conn, "SELECT * FROM managers ORDER BY name ASC");
                                while ($row = mysqli_fetch_array($result)) {
                                    echo '<option value="'.$row['id'].'">'.$row['name'].'</option>';
                                }
                                ?>
                            </select><br>
                            <h3>... was in ...</h3>
                            <select id="place1">
                                <option value="1">First</option>
                                <option value="2">Second</option>
                                <option value="3">Third</option>
                                <option value="4">Fourth</option>
                                <option value="5">Fifth</option>
                                <option value="6">Sixth</option>
                                <option value="7">Seventh</option>
                                <option value="8">Eighth</option>
                                <option value="9">Ninth</option>
                                <option value="10">Tenth</option>
                            </select><br>
                            <h3>... place?</h3>
                            <br /><br />
                            <button id="lookup-btn">Search</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.html'; ?>

<script type="text/javascript">
    
    $('#lookup-btn').click(function () {

        manager1Id = $('#manager1-select').val();
        place1 = $('#place1').val();

        $.ajax({
            url : 'dataLookup.php',
            method: 'POST',
            dataType: 'text',
            data: {
                dataType: "standings",
                manager1: manager1Id,
                place: place1
            },
            cache: false,
            success: function(response) {
                let data = JSON.parse(response);
                $("#postData").html(data.return);
                $("#count").html('Count: '+data.count);
            }
        });
    });

    $('#datatable-results').DataTable({
        "searching": false,
        "paging": false,
        "info": false,
        "order": [
            [0, "desc"],
            [1, "desc"],
        ]
    });

</script>

<style>
   
</style>