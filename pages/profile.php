<?php
require_once(__DIR__ . "/../config/config.php");

// Bắt buộc đăng nhập
if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "pages/login.php");
    exit;
}

$uid = (int)$_SESSION['user_id'];
$msg = "";
$msg_type = "";

// --- TỰ ĐỘNG ĐỒNG BỘ CỘT CHO NHIỀU ĐỊA CHỈ & SỐ ĐIỆN THOẠI ---
$conn->query("ALTER TABLE users MODIFY COLUMN phone TEXT");
$conn->query("ALTER TABLE users MODIFY COLUMN address TEXT");

// Lấy dữ liệu user hiện tại
$stmt_user = mysqli_prepare($conn, "SELECT * FROM users WHERE id = ?");
mysqli_stmt_bind_param($stmt_user, "i", $uid);
mysqli_stmt_execute($stmt_user);
$user = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_user));

// Xử lý cập nhật thông tin
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profile'])) {
        $display_name = mysqli_real_escape_string($conn, $_POST['display_name']);
        $email = mysqli_real_escape_string($conn, $_POST['email']);
        
        // Xử lý mảng phone & address
        $phones = isset($_POST['phones']) ? array_filter($_POST['phones']) : [];
        $addresses = isset($_POST['addresses']) ? array_filter($_POST['addresses']) : [];
        
        $phone_json = json_encode($phones, JSON_UNESCAPED_UNICODE);
        $address_json = json_encode($addresses, JSON_UNESCAPED_UNICODE);

        // Xử lý upload Avatar
        $avatar_sql = "";
        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === 0) {
            $allowed = ['image/jpeg', 'image/png', 'image/webp'];
            $file = $_FILES['avatar'];
            if (in_array($file['type'], $allowed) && $file['size'] <= 2 * 1024 * 1024) {
                $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                $newname = 'u' . $uid . '_' . time() . '.' . $ext;
                $target_path = __DIR__ . '/../uploads/' . $newname;
                
                if (move_uploaded_file($file['tmp_name'], $target_path)) {
                    // Xóa ảnh cũ nếu không phải mặc định
                    if (!empty($user['avatar']) && file_exists(__DIR__ . '/../uploads/' . $user['avatar'])) {
                        unlink(__DIR__ . '/../uploads/' . $user['avatar']);
                    }
                    $avatar_sql = ", avatar = '$newname'";
                }
            } else {
                $msg = "Ảnh không hợp lệ hoặc quá lớn (Max 2MB)!";
                $msg_type = "error";
            }
        }

        if ($msg_type !== "error") {
            $sql = "UPDATE users SET display_name = ?, email = ?, phone = ?, address = ? $avatar_sql WHERE id = ?";
            $stmt = mysqli_prepare($conn, $sql);
            
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, "ssssi", $display_name, $email, $phone_json, $address_json, $uid);
                if (mysqli_stmt_execute($stmt)) {
                    $msg = "Cập nhật thông tin thành công!";
                    $msg_type = "success";
                    
                    // Refresh data
                    mysqli_stmt_execute($stmt_user);
                    $user = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_user));
                } else {
                    $msg = "Lỗi thực thi: " . mysqli_stmt_error($stmt);
                    $msg_type = "error";
                }
                mysqli_stmt_close($stmt);
            }
        }
    }

    if (isset($_POST['change_password'])) {
        $old_pass = $_POST['old_password'];
        $new_pass = $_POST['new_password'];
        $confirm_pass = $_POST['confirm_password'];

        if (!password_verify($old_pass, $user['password'])) {
            $msg = "Mật khẩu hiện tại không chính xác!";
            $msg_type = "error";
        } elseif ($new_pass !== $confirm_pass) {
            $msg = "Mật khẩu xác nhận không khớp!";
            $msg_type = "error";
        } elseif (strlen($new_pass) < 6) {
            $msg = "Mật khẩu mới phải từ 6 ký tự!";
            $msg_type = "error";
        } else {
            $hashed_pass = password_hash($new_pass, PASSWORD_DEFAULT);
            $stmt_up = mysqli_prepare($conn, "UPDATE users SET password = ? WHERE id = ?");
            mysqli_stmt_bind_param($stmt_up, "si", $hashed_pass, $uid);
            mysqli_stmt_execute($stmt_up);
            $msg = "Đổi mật khẩu thành công!";
            $msg_type = "success";
        }
    }
}

$page_css = "profile.css";
include(__DIR__ . "/../includes/header.php");

// Giải mã JSON phone & address
$user_phones = json_decode($user['phone'] ?? '[]', true);
if (!is_array($user_phones)) $user_phones = $user['phone'] ? [$user['phone']] : [];

$user_addresses = json_decode($user['address'] ?? '[]', true);
if (!is_array($user_addresses)) $user_addresses = $user['address'] ? [$user['address']] : [];

// Lấy lịch sử mua hàng
$sql_orders = "SELECT o.id, o.total, o.status, o.created_at, 
               GROUP_CONCAT(p.name SEPARATOR ', ') as items
               FROM orders o
               JOIN order_items oi ON o.id = oi.order_id
               JOIN products p ON oi.product_id = p.id
               WHERE o.user_id = ?
               GROUP BY o.id
               ORDER BY o.created_at DESC";
$stmt_orders = mysqli_prepare($conn, $sql_orders);
mysqli_stmt_bind_param($stmt_orders, "i", $uid);
mysqli_stmt_execute($stmt_orders);
$orders_res = mysqli_stmt_get_result($stmt_orders);
?>

<section class="profile-page">
    <div class="container">
        
        <div class="profile-header-flex">
            <h2 class="products-title" style="text-align: left; margin: 0;">Tài khoản của tôi</h2>
            <a href="<?= BASE_URL ?>pages/logout.php" class="btn btn-logout-alt">Đăng xuất</a>
        </div>

        <?php if($msg): ?>
            <div class="alert alert-<?= $msg_type ?>" style="margin-top: 20px;"><?= $msg ?></div>
        <?php endif; ?>

        <div class="profile-tabs-nav">
            <button class="tab-link active" onclick="openTab(event, 'info')">Thông tin cá nhân</button>
            <button class="tab-link" onclick="openTab(event, 'history')">Lịch sử đơn hàng</button>
            <button class="tab-link" onclick="openTab(event, 'pass')">Bảo mật & Mật khẩu</button>
        </div>

        <!-- Tab: Thông tin -->
        <div id="info" class="tab-pane active">
            <form action="" method="POST" enctype="multipart/form-data">
                <div class="profile-layout">
                    <div class="profile-sidebar">
                        <div class="avatar-card-new">
                            <div class="avatar-circle">
                                <img src="<?= ($user['avatar'] ?? '') ? BASE_URL.'uploads/'.$user['avatar'] : BASE_URL.'images/user.jpg' ?>" alt="Avatar" id="preview-avatar">
                            </div>
                            <label for="avatar-input" class="btn btn-outline btn-sm" style="margin-top: 15px;">Thay đổi ảnh</label>
                            <input type="file" name="avatar" id="avatar-input" hidden onchange="previewImage(this)">
                            <p class="u-muted" style="margin-top: 10px;">Định dạng: JPG, PNG, WEBP (Max 2MB)</p>
                        </div>
                    </div>

                    <div class="profile-main">
                        <div class="form-card">
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Tên đăng nhập</label>
                                    <input type="text" value="<?= htmlspecialchars($user['username'] ?? '') ?>" readonly class="input-readonly">
                                </div>
                                <div class="form-group">
                                    <label>Tên hiển thị</label>
                                    <input type="text" name="display_name" value="<?= htmlspecialchars($user['display_name'] ?? '') ?>" placeholder="Nhập tên hiển thị">
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Email</label>
                                <div class="input-with-icon">
                                    <input type="email" name="email" value="<?= htmlspecialchars($user['email'] ?? '') ?>" id="email-field">
                                    <span class="icon-eye" onclick="toggleVisibility('email-field')">👁️</span>
                                </div>
                            </div>

                            <!-- NHIỀU SỐ ĐIỆN THOẠI -->
                            <div class="form-group">
                                <label>Số điện thoại</label>
                                <div id="phone-container" class="dynamic-list">
                                    <?php if(empty($user_phones)): ?>
                                        <div class="input-with-icon item-row">
                                            <input type="text" name="phones[]" value="" placeholder="Chưa cập nhật">
                                            <span class="btn-remove" onclick="removeItem(this)">×</span>
                                        </div>
                                    <?php else: ?>
                                        <?php foreach($user_phones as $phone): ?>
                                            <div class="input-with-icon item-row">
                                                <input type="text" name="phones[]" value="<?= htmlspecialchars($phone) ?>">
                                                <span class="btn-remove" onclick="removeItem(this)">×</span>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                                <button type="button" class="btn btn-outline btn-sm" style="margin-top: 10px;" onclick="addItem('phone-container', 'phones[]')">+ Thêm số điện thoại</button>
                            </div>

                            <!-- NHIỀU ĐỊA CHỈ -->
                            <div class="form-group">
                                <label>Địa chỉ nhận hàng</label>
                                <div id="address-container" class="dynamic-list">
                                    <?php if(empty($user_addresses)): ?>
                                        <div class="input-with-icon item-row">
                                            <textarea name="addresses[]" rows="2" placeholder="Chưa có địa chỉ"></textarea>
                                            <span class="btn-remove" onclick="removeItem(this)">×</span>
                                        </div>
                                    <?php else: ?>
                                        <?php foreach($user_addresses as $address): ?>
                                            <div class="input-with-icon item-row">
                                                <textarea name="addresses[]" rows="2"><?= htmlspecialchars($address) ?></textarea>
                                                <span class="btn-remove" onclick="removeItem(this)">×</span>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                                <button type="button" class="btn btn-outline btn-sm" style="margin-top: 10px;" onclick="addItem('address-container', 'addresses[]', true)">+ Thêm địa chỉ mới</button>
                            </div>

                            <div style="text-align: right;">
                                <button type="submit" name="update_profile" class="btn btn-primary btn-large">Lưu thông tin</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- Tab: Lịch sử -->
        <div id="history" class="tab-pane">
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Mã Đơn</th>
                            <th>Sản phẩm</th>
                            <th>Tổng tiền</th>
                            <th>Trạng thái</th>
                            <th>Ngày đặt</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($orders_res && mysqli_num_rows($orders_res) > 0): ?>
                            <?php while ($order = mysqli_fetch_assoc($orders_res)): ?>
                                <tr>
                                    <td class="fw-bold">#<?= $order['id'] ?></td>
                                    <td class="td-truncate" title="<?= htmlspecialchars($order['items'] ?? '') ?>"><?= htmlspecialchars($order['items'] ?? '') ?></td>
                                    <td class="fw-bold color-brand"><?= number_format($order['total'], 0, ',', '.') ?>đ</td>
                                    <td><span class="badge-status <?= $order['status'] ?>"><?= $order['status'] ?></span></td>
                                    <td><?= date('d/m/Y', strtotime($order['created_at'])) ?></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="5" class="u-center" style="padding: 50px;">Bạn chưa có đơn hàng nào.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Tab: Mật khẩu -->
        <div id="pass" class="tab-pane">
            <div class="form-card" style="max-width: 600px; margin: 0 auto;">
                <h3 style="margin-bottom: 20px; color: var(--secondary-color);">Thay đổi mật khẩu</h3>
                <form action="" method="POST">
                    <div class="form-group">
                        <label>Mật khẩu hiện tại</label>
                        <input type="password" name="old_password" required placeholder="••••••••">
                    </div>
                    <div class="form-group">
                        <label>Mật khẩu mới</label>
                        <input type="password" name="new_password" required placeholder="Tối thiểu 6 ký tự">
                    </div>
                    <div class="form-group">
                        <label>Xác nhận mật khẩu mới</label>
                        <input type="password" name="confirm_password" required placeholder="Nhập lại mật khẩu mới">
                    </div>
                    <button type="submit" name="change_password" class="btn btn-primary btn-block">Cập nhật mật khẩu</button>
                </form>
            </div>
        </div>

    </div>
</section>

<script>
function openTab(evt, tabName) {
    var i, tabpane, tablinks;
    tabpane = document.getElementsByClassName("tab-pane");
    for (i = 0; i < tabpane.length; i++) {
        tabpane[i].classList.remove("active");
    }
    tablinks = document.getElementsByClassName("tab-link");
    for (i = 0; i < tablinks.length; i++) {
        tablinks[i].classList.remove("active");
    }
    document.getElementById(tabName).classList.add("active");
    evt.currentTarget.classList.add("active");
}

function toggleVisibility(id) {
    const input = document.getElementById(id);
    input.type = (input.type === "password") ? "text" : "password";
}

function addItem(containerId, name, isTextarea = false) {
    const container = document.getElementById(containerId);
    const div = document.createElement('div');
    div.className = 'input-with-icon item-row';
    div.style.marginTop = '10px';
    
    const input = document.createElement(isTextarea ? 'textarea' : 'input');
    input.name = name;
    if (isTextarea) input.rows = 2;
    input.placeholder = isTextarea ? "Nhập địa chỉ mới" : "Nhập số điện thoại mới";
    
    const removeBtn = document.createElement('span');
    removeBtn.className = 'btn-remove';
    removeBtn.innerHTML = '×';
    removeBtn.onclick = function() { removeItem(this); };
    
    div.appendChild(input);
    div.appendChild(removeBtn);
    container.appendChild(div);
}

function removeItem(btn) {
    const row = btn.parentElement;
    const container = row.parentElement;
    if (container.children.length > 1) {
        row.remove();
    } else {
        const input = row.querySelector('input, textarea');
        input.value = '';
    }
}

document.addEventListener("DOMContentLoaded", function() {
    if(document.getElementById('email-field')) document.getElementById('email-field').type = 'password';
});

function previewImage(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('preview-avatar').src = e.target.result;
        }
        reader.readAsDataURL(input.files[0]);
    }
}
</script>

<?php include(__DIR__ . "/../includes/footer.php"); ?>
