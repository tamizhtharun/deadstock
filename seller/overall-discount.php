<?php
require_once("header.php");
session_start();

if(!isset($_SESSION['seller_session'])) {
    header("Location: login.php");
    exit;
}

$seller_id = $_SESSION['seller_session']['seller_id'];

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['discount'])) {
    $discount = (int)$_POST['discount'];

    if($discount >= 0 && $discount <= 100) {
   
        $statement = $pdo->prepare("SELECT id, p_old_price FROM tbl_product WHERE seller_id = ?");
        $statement->execute([$seller_id]);
        $products = $statement->fetchAll(PDO::FETCH_ASSOC);

        foreach ($products as $product) {
            $old_price = $product['p_old_price'];
            $new_price = round($old_price - (($discount / 100) * $old_price));
            $update = $pdo->prepare("UPDATE tbl_product 
                                     SET p_current_price = ?, p_is_approve = 0 
                                     WHERE id = ?");
            $update->execute([$new_price, $product['id']]);
        }

        header("Location: discount.php?success=1");
        exit;
    }
}

header("Location: discount.php?error=1");
exit;
