<?php

include '../connections.php';

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

if (isset($_POST['profile-update'])) {

    $return = ['type' => 'success', 'message' => 'Successfully updated.'];

    try {

        $sql = $conn->prepare("UPDATE preseason_rankings SET team = ?, position = ?, bye = ?, my_rank = ?, depth = ?, sos = ?, line = ?, tier = ?, notes = ? WHERE id = ?");
        $sql->bind_param('ssiiiiiisi', 
            $_POST['team'],
            $_POST['position'],
            $_POST['bye'],
            $_POST['my_rank'],
            $_POST['depth'],
            $_POST['sos'],
            $_POST['line'],
            $_POST['tier'],
            $_POST['notes'],
            $_POST['id']
        );
        $sql->execute();

    } catch (Exception $ex) {
        $return = ['type' => 'error', 'message' => $ex->getMessage()];
    }

    header('Location: ' . $_SERVER['HTTP_REFERER']);
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

if (isset($_POST['player-history'])) {

    var_dump($_POST);die;
}