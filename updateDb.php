<?php

$pageName = "Update Database";
include 'header.php';
include 'sidebar.html';

?>

<div class="app-content content container-fluid">
    <div class="content-wrapper">
        <div class="content-header row"></div>

        <div class="content-body">
            <div class="row">
                <div class="col-sm-12">
                    <div class="card">
                        <div class="card-header">
                            <h3>SQL Inserts</h3>
                        </div>
                        <div class="card-body">
                            <div class="card-block" style="background: #fff; direction: ltr">
                                <form action="functions.php" method="POST">
                                    <button type="submit">Save</button><br />
                                    <textarea name="sql-stmt" rows="100" cols="150"></textarea>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>