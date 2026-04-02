<?php
require_once("../../config/config.php");
require_once("../auth_admin.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name  = trim($_POST['name'] ?? '');
    $price = (float)($_POST['price'] ?? 0);
    $stock = (int)($_POST['stock'] ?? 0);

    $img = '';

    // Xử lý upload ảnh
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = "../../images/";

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $fileTmp  = $_FILES['image']['tmp_name'];
        $fileName = basename($_FILES['image']['name']);
        $ext      = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        $allowedExt = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        if (in_array($ext, $allowedExt)) {
            $newFileName = time() . '_' . uniqid() . '.' . $ext;
            $targetFile = $uploadDir . $newFileName;

            if (move_uploaded_file($fileTmp, $targetFile)) {
                $img = $newFileName; // lưu tên file vào DB
            }
        }
    }

    $stmt = mysqli_prepare($conn, "INSERT INTO products(name, price, stock, image) VALUES (?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "sdis", $name, $price, $stock, $img);
    mysqli_stmt_execute($stmt);

    header("Location: manage_products.php");
    exit;
}
?>

<link rel="stylesheet" href="../css/admin.css">

<div class="dashboard">
    <a class="topbar-link" href="../dashboard.php">🏠 Trang chủ</a>
    <h1>➕ Thêm sản phẩm</h1>

    <div class="box">
        <form method="POST" enctype="multipart/form-data">
            <input name="name" placeholder="Tên sản phẩm" required><br><br>
            <input name="price" type="number" placeholder="Giá" required><br><br>
            <input name="stock" type="number" placeholder="Stock" required><br><br>

            <div style="border: 2px dashed #ccc; padding: 15px; text-align: center; border-radius: 8px; cursor: pointer; background: #fafafa;"
                 onclick="document.getElementById('proof-img').click()">
                <input type="file" id="proof-img" name="image" style="display:none;" accept="image/*" onchange="previewImage(this)">
                <div id="preview-container">
                    <span style="font-size: 30px; color: #aaa;">📷</span>
                    <p style="font-size: 13px; color: #888; margin-top: 5px;">Bấm để chọn ảnh từ thiết bị</p>
                </div>
            </div>

            <br>
            <button type="submit">Thêm</button>
        </form>
    </div>
</div>

<script>
function previewImage(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('preview-container').innerHTML = `
                <img src="${e.target.result}" style="max-width: 100%; max-height: 150px; border-radius: 5px;">
                <p style="font-size: 12px; color: #28a745; margin-top: 5px;">Đã chọn ảnh!</p>
            `;
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>