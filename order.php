<?php include('db_connection.php'); 

if (!isset($_SESSION['user_session']['id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_session']['id'];

