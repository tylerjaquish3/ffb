<?php

include 'connections.php';

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname, $port);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_POST['data'])) {

    $return = ['type' => 'success', 'message' => 'Successfully updated.'];

    // var_dump($_POST['data']);
    try {

        $data = $_POST['data'];

        $player = $data[2];
        $mine = ($data[8] == 'taken') ? 0 : 1;

        $result = mysqli_query($conn,"SELECT * FROM 2019_rankings WHERE player = '".$player."'");
        if (mysqli_num_rows($result) > 0) {
            while($row = mysqli_fetch_array($result)) {
                $playerId = $row['id'];
            }
        }

        $picks = 0;

        $result = mysqli_query($conn,"SELECT count(id) as pick FROM 2019_selections");
        if (mysqli_num_rows($result) > 0) {
            while($row = mysqli_fetch_array($result)) {
                $picks = $row['pick'];
            }
        }

        $picks++;

        $sql = $conn->prepare("INSERT INTO 2019_selections (ranking_id, is_mine, pick_number) VALUES (?,?,?)");
        $sql->bind_param('iii', $playerId, $mine, $picks);
        $sql->execute();
    } catch (Exception $ex) {
        $return = ['type' => 'error', 'message' => $ex->getMessage()];
    }

    echo json_encode($return);
    die;
}