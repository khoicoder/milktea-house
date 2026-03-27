<?php


function sendAdminNotification($conn, $title, $message, $link = '', $product_id = null) {

    $title = mysqli_real_escape_string($conn, $title);
    $message = mysqli_real_escape_string($conn, $message);
    $link = mysqli_real_escape_string($conn, $link);

    if($product_id){
        $check = mysqli_query($conn, "
            SELECT id FROM notifications 
            WHERE type = 'stock'
            AND ref_id = $product_id
            AND created_at > NOW() - INTERVAL 10 MINUTE
        ");

        if(mysqli_num_rows($check) > 0){
            return;
        }
    }

    $admins = mysqli_query($conn, "
        SELECT id FROM users WHERE role = 'admin'
    ");

    $ref_id = $product_id ? (int)$product_id : "NULL";

    while($admin = mysqli_fetch_assoc($admins)){
        $admin_id = $admin['id'];

        mysqli_query($conn, "
            INSERT INTO notifications (user_id, title, message, link, type, ref_id)
            VALUES ($admin_id, '$title', '$message', '$link', 'stock', $ref_id)
        ");
    }
}