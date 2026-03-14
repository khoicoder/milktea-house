<?php   

if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['avatar'])){
  $file = $_FILES['avatar'];
  if($file['error'] === 0){
    $allowed = ['image/jpeg','image/png','image/webp'];
    if(in_array($file['type'], $allowed) && $file['size'] <= 2*1024*1024){
      $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
      $newname = 'u' . $uid . '_' . time() . '.' . $ext;
      move_uploaded_file($file['tmp_name'], __DIR__ . '/../uploads/' . $newname);
      // update db
      $stmt = mysqli_prepare($conn, "UPDATE users SET avatar = ? WHERE id = ?");
      mysqli_stmt_bind_param($stmt, "si", $newname, $uid);
      mysqli_stmt_execute($stmt);
    }
  }
} 