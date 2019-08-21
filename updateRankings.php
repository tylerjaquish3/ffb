<?php

include 'connections.php';

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname, $port);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_POST)) {

    $return = ['type' => 'success', 'message' => 'Successfully updated.'];

    // var_dump($_POST['item']);die;
    try {

        $rank = 1;
        foreach ($_POST['item'] as $key => $value) {

            $sql = $conn->prepare("UPDATE 2019_rankings SET my_rank = ? WHERE id = ?");
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