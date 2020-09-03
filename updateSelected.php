<?php

include 'connections.php';

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname, $port);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// var_dump($_POST);die;

if (isset($_POST['data'])) {

    $return = ['type' => 'success', 'message' => 'Successfully updated.'];

    // var_dump($_POST['data']);
    try {

        $data = $_POST['data'];

        $player = mysqli_real_escape_string($conn, $data[2]);
        $mine = ($data[15] == 'taken') ? 0 : 1;

        $result = mysqli_query($conn,"SELECT * FROM preseason_rankings WHERE player = '".$player."'");
        if (mysqli_num_rows($result) > 0) {
            while($row = mysqli_fetch_array($result)) {
                $playerId = $row['id'];
            }
        }

        $picks = 0;

        $result = mysqli_query($conn,"SELECT count(id) as pick FROM draft_selections");
        if (mysqli_num_rows($result) > 0) {
            while($row = mysqli_fetch_array($result)) {
                $picks = $row['pick'];
            }
        }

        $picks++;

        $sql = $conn->prepare("INSERT INTO draft_selections (ranking_id, is_mine, pick_number) VALUES (?,?,?)");
        $sql->bind_param('iii', $playerId, $mine, $picks);
        $sql->execute();
    } catch (Exception $ex) {
        $return = ['type' => 'error', 'message' => $ex->getMessage()];
    }

    echo json_encode($return);
    die;
}

// Add a new player to the db because someone drafted an unranked player
if (isset($_POST['newname'])) {

    $return = ['type' => 'success', 'message' => 'Successfully updated.'];

    try {
        $player = $_POST['newname'];
        $rank = 999;
        $adp = 999;

        $sql = $conn->prepare("INSERT INTO preseason_rankings (player, my_rank, adp) VALUES (?,?,?)");
        $sql->bind_param('sii', $player, $rank, $adp);
        $sql->execute();
    } catch (Exception $ex) {
        $return = ['type' => 'error', 'message' => $ex->getMessage()];
    }

    echo json_encode($return);
    die;
}