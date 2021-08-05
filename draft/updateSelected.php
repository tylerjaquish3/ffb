<?php

include '../connections.php';

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
        $mine = ($data[count($data)-1] == 'taken') ? 0 : 1;

        $result = mysqli_query($conn,"SELECT * FROM preseason_rankings WHERE my_rank = $data[0]");
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

        $picker = $data[count($data)-2];
        $result = mysqli_query($conn,"SELECT id FROM managers WHERE name = '{$picker}'");
        if (mysqli_num_rows($result) > 0) {
            while($row = mysqli_fetch_array($result)) {
                $managerId = $row['id'];
            }
        }

        $picks++;

        $sql = $conn->prepare("INSERT INTO draft_selections (ranking_id, is_mine, pick_number, manager_id) VALUES (?,?,?,?)");
        $sql->bind_param('iiii', $playerId, $mine, $picks, $managerId);
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

if (isset($_POST['request'])) {

    if ($_POST['request'] == 'undo') {

        $result = mysqli_query($conn,"SELECT id FROM draft_selections order by id desc limit 1");
        if (mysqli_num_rows($result) > 0) {
            while($row = mysqli_fetch_array($result)) {
                $lastPick = $row['id'];
            }
        }

        $sql = $conn->prepare("DELETE FROM draft_selections WHERE id = ?");
        $sql->bind_param('i', $lastPick);
        $sql->execute();
    } elseif ($_POST['request'] == 'restart') {
        $sql = $conn->prepare("DELETE FROM draft_selections");
        $sql->execute();
    }

    if ($_POST['request'] == 'player_data') {
        $id = $_POST['id'];
        $data = [];

        $result = mysqli_query($conn, "SELECT * FROM preseason_rankings WHERE id = $id");
        if (mysqli_num_rows($result) > 0) {
            while($row = mysqli_fetch_array($result)) {
                $data[] = $row;
            }
        }

        $result = mysqli_query($conn, "SELECT * FROM player_data WHERE preseason_ranking_id = $id and type = 'REG' order by year desc");
        if (mysqli_num_rows($result) > 0) {
            while($row = mysqli_fetch_array($result)) {
                $data[] = $row;
            }
        }

        echo json_encode($data);
        die;
    }

    if ($_POST['request'] == 'notes') {
        $sql = $conn->prepare("UPDATE preseason_rankings SET notes = ? WHERE id = ?");
        $sql->bind_param('si', $_POST['notes'], $_POST['id']);
        $sql->execute();
    }

    echo json_encode(true);
    die;
}