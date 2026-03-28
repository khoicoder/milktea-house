<?php
require_once(__DIR__ . "/../../config/config.php");
require_once(__DIR__ . "/../auth_admin.php");

// Lấy danh sách user để admin chọn (nếu muốn gửi riêng)
$sql_users = "SELECT id, username FROM users WHERE role != 'admin'";

$admins = mysqli_query($conn, "
    SELECT id FROM users WHERE role = 'admin'
");
$res_users = mysqli_query($conn, $sql_users);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý Thông báo - Admin</title>
    <link rel="stylesheet" href="../../css/base.css">
    <link rel="stylesheet" href="../css/admin.css">
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f4f7f6; padding: 20px; }
        .admin-content { max-width: 900px; margin: 0 auto; }
        .admin-form { background: #fff; padding: 25px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.08); margin-bottom: 30px; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; font-weight: 600; margin-bottom: 8px; color: #333; }
        .form-group input, .form-group select, .form-group textarea { 
            width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; box-sizing: border-box; font-size: 14px;
        }
        .btn-submit { 
            background: #ff5a5f; color: white; border: none; padding: 12px 25px; border-radius: 8px; 
            cursor: pointer; font-weight: 600; transition: background 0.3s; 
        }
        .btn-submit:hover { background: #e04a4f; }
        
        .alert { padding: 15px; margin-bottom: 20px; border-radius: 8px; font-weight: 500; }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }

        /* Table styles */
        .noti-table-container { background: #fff; padding: 25px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.08); }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        table th { background: #f8f9fa; padding: 15px; text-align: left; border-bottom: 2px solid #eee; color: #666; font-size: 14px; }
        table td { padding: 15px; border-bottom: 1px solid #eee; vertical-align: top; }
        .btn-delete { color: #ff5a5f; text-decoration: none; font-weight: 600; font-size: 14px; }
        .btn-delete:hover { text-decoration: underline; }
        .badge-all { background: #fff0f0; color: #ff5a5f; padding: 3px 8px; border-radius: 4px; font-size: 12px; font-weight: bold; }
    </style>
</head>
<body>
    <div class="admin-content">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h2>Quản lý Thông báo</h2>
            <a href="../dashboard.php" style="text-decoration: none; color: #666; font-weight: 500;">← Quay lại Dashboard</a>
        </div>

        <?php if (isset($_GET['status'])): ?>
            <div class="alert alert-<?= htmlspecialchars($_GET['status']) ?>">
                <?= htmlspecialchars($_GET['msg']) ?>
            </div>
        <?php endif; ?>

        <!-- FORM GỬI THÔNG BÁO -->
        <div class="admin-form">
            <h3>Gửi thông báo mới</h3>
            <form action="../services/notification_service.php" method="POST" style="margin-top: 15px;">
                <div class="form-group">
                    <label>Người nhận:</label>
                    <select name="user_id">
                        <option value="0">Tất cả người dùng (Public)</option>
                        <?php while($u = mysqli_fetch_assoc($res_users)): ?>
                            <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['username']) ?> (ID: <?= $u['id'] ?>)</option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Tiêu đề:</label>
                    <input type="text" name="title" placeholder="Ví dụ: Khuyến mãi cuối tuần cực sốc!" required>
                </div>

                <div class="form-group">
                    <label>Nội dung thông báo:</label>
                    <textarea name="message" rows="3" placeholder="Nhập nội dung chi tiết thông báo..." required></textarea>
                </div>

                <div class="form-group">
                    <label>Đường dẫn khi nhấn vào (Link):</label>
                    <input type="text" name="link" placeholder="Ví dụ: pages/category.php?id=1">
                </div>

                <button type="submit" class="btn-submit">Gửi thông báo ngay</button>
            </form>
        </div>

        <!-- DANH SÁCH THÔNG BÁO ĐÃ GỬI -->
        <div class="noti-table-container">
            <h3>Danh sách thông báo đã gửi</h3>
            <table>
                <thead>
                    <tr>
                        <th>Người nhận</th>
                        <th>Nội dung</th>
                        <th>Ngày gửi</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sql_list = "SELECT n.*, u.username FROM notifications n 
                                 LEFT JOIN users u ON n.user_id = u.id 
                                 ORDER BY n.created_at DESC";
                    $res_list = mysqli_query($conn, $sql_list);
                    if ($res_list && mysqli_num_rows($res_list) > 0):
                        while ($row = mysqli_fetch_assoc($res_list)):
                    ?>
                        <tr>
                            <td>
                                <?= $row['user_id'] == null ? '<span class="badge-all">TẤT CẢ</span>' : '👤 ' . htmlspecialchars($row['username']) ?>
                            </td>
                            <td>
                                <div style="font-weight: 600; margin-bottom: 4px;"><?= htmlspecialchars($row['title']) ?></div>
                                <div style="font-size: 13px; color: #666; line-height: 1.4;"><?= htmlspecialchars($row['message']) ?></div>
                            </td>
                            <td style="font-size: 12px; color: #888; white-space: nowrap;">
                                <?= date('d/m/Y H:i', strtotime($row['created_at'])) ?>
                            </td>
                            <td>
                                <a href="../services/delete_notification.php?id=<?= $row['id'] ?>" 
                                   class="btn-delete"
                                   onclick="return confirm('Bạn có chắc chắn muốn xóa thông báo này không?')">Xóa</a>
                            </td>
                        </tr>
                    <?php 
                        endwhile; 
                    else:
                    ?>
                        <tr><td colspan="4" style="text-align: center; color: #999; padding: 40px;">Chưa có thông báo nào được gửi.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>