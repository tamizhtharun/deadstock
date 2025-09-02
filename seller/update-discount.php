<?php
require_once("header.php");

if (isset($_POST['id']) && isset($_POST['discount'])) {
    $id = (int) $_POST['id'];
    $discount = (int) $_POST['discount'];

    $statement = $pdo->prepare("SELECT p_old_price FROM tbl_product WHERE id = ?");
    $statement->execute([$id]);
    $row = $statement->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        $old_price = $row['p_old_price'];

        if ($discount >= 0 && $discount <= 100) {
            $new_price = round($old_price - (($discount / 100) * $old_price));


            $update = $pdo->prepare("UPDATE tbl_product SET p_current_price = ?,p_is_approve= 0 WHERE id = ?");
            $update->execute([$new_price, $id]);

            header("Location: discount.php?success=1");
            exit;
        }
    }
}

header("Location: discount.php?error=1");
exit;
