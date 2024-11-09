<?php 
ob_start();
session_start();
include '../db_connection.php'; 
unset($_SESSION['seller']);
header("location: ../index.php"); 
?>