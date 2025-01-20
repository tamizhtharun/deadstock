<?php
session_start();
$_SESSION = array();
session_destroy();

$referrer = $_SERVER['HTTP_REFERER'];
                   header("Location: $referrer");
                   exit();
?>