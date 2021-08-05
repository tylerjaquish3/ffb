<?php

include '../connections.php';

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname, $port);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// var_dump($_POST);die;

if (isset($_POST['item'])) {

    $return = ['type' => 'success', 'message' => 'Successfully updated.'];

    // var_dump($_POST['item']);die;
    try {

        $rank = 1;
        foreach ($_POST['item'] as $key => $value) {

            $sql = $conn->prepare("UPDATE preseason_rankings SET my_rank = ? WHERE id = ?");
            $sql->bind_param('ii', $rank, $value);
            $sql->execute();
            $rank++;
        }


    } catch (Exception $ex) {
        $return = ['type' => 'error', 'message' => $ex->getMessage()];
    }

    echo json_encode($return);
    die;
}

if (isset($_POST['tier'])) {

    $return = ['type' => 'success', 'message' => 'Successfully updated.'];

    // var_dump($_POST['tier']);die;
    try {

        $sql = $conn->prepare("UPDATE preseason_rankings SET tier = ? WHERE id = ?");
        $sql->bind_param('ii', $_POST['tier'], $_POST['playerId']);
        $sql->execute();

    } catch (Exception $ex) {
        $return = ['type' => 'error', 'message' => $ex->getMessage()];
    }

    echo json_encode($return);
    die;
}