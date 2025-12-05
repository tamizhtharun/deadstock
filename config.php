<?php
$env = parse_ini_file('.env');
define('RAZORPAY_KEY_ID', $env['RAZORPAY_KEY_ID']);
define('RAZORPAY_KEY_SECRET', $env['RAZORPAY_KEY_SECRET']);
?>