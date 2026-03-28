<?php
require_once(__DIR__ . "/config/config.php");
$page_css = "product.css";
include(__DIR__ . "/includes/header.php");


$category_id = intval($_GET['id'] ?? 0);
$search = $_GET['search'] ?? '';
$min = isset($_GET['min']) && $_GET['min'] !== '' ? intval($_GET['min']) : 0;
$max = isset($_GET['max']) && $_GET['max'] !== '' ? intval($_GET['max']) : 1000000;
$sort = $_GET['sort'] ?? 'newest';


if ($min > $max) {
    $tmp = $min; $min = $max; $max = $tmp;
}

$search_esc = mysqli_real_escape_string($conn, $search);


$sql = "SELECT id, name, price, image, description, stock FROM products WHERE price BETWEEN {$min} AND {$max}";

if ($category_id > 0) {
    $sql .= " AND category_id = {$category_id}";
}

if ($search_esc !== '') {
    $sql .= " AND name LIKE '%{$search_esc}%'";
}

// Xử lý sắp xếp
switch ($sort) {
    case 'price_asc':
        $sql .= " ORDER BY price ASC";
        break;
    case 'price_desc':
        $sql .= " ORDER BY price DESC";
        break;
    default:
        $sql .= " ORDER BY id DESC";
        break;
}

$result = mysqli_query($conn, $sql);

// Lấy tên danh mục nếu có
$category_name = "Đồ uống phổ biến";
if ($category_id > 0) {
    $res_cat = mysqli_query($conn, "SELECT name FROM categories WHERE id = $category_id");
    if ($cat_row = mysqli_fetch_assoc($res_cat)) {
        $category_name = $cat_row['name'];
    }
}

// Kiểm tra xem có đang lọc hay không
$is_filtering = !empty($search) || $min > 0 || $max < 1000000 || $sort !== 'newest';
?>

<section class="products product-page">
  <div class="container">

    <div class="products-header" style="text-align: center; margin-bottom: 35px;">
        <h2 class="products-title" style="margin-bottom: 8px;">
            <?php if (!empty($search)): ?>
                🔍 Kết quả cho "<?= htmlspecialchars($search) ?>"
            <?php else: ?>
                <?= htmlspecialchars($category_name) ?>
            <?php endif; ?>
        </h2>
        <p style="color: #718096; font-size: 15px;">Khám phá hương vị trà sữa tuyệt vời nhất</p>
    </div>

    <!-- Filter form -->
    <div class="filter-section">
      <form method="GET" class="filter-form">
        <input type="hidden" name="id" value="<?= htmlspecialchars($category_id) ?>">

        <div class="search-wrapper">
          <span class="search-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
          </span>
          <input
            type="text"
            name="search"
            placeholder="Tìm kiếm trà sữa, đồ uống..."
            value="<?= htmlspecialchars($search) ?>"
          >
        </div>

        <div class="price-filter">
          <label>Giá:</label>
          <input type="number" name="min" value="<?= $min > 0 ? htmlspecialchars($min) : '' ?>" min="0" placeholder="Từ">
          <span class="separator">-</span>
          <input type="number" name="max" value="<?= $max < 1000000 ? htmlspecialchars($max) : '' ?>" min="0" placeholder="Đến">
        </div>

        <div class="sort-filter" style="display: flex; align-items: center; gap: 10px; background: #f9fafb; padding: 8px 16px; border-radius: 12px; border: 1.5px solid #eef0f2;">
            <label style="font-size: 14px; color: #718096; font-weight: 600; white-space: nowrap;">Sắp xếp:</label>
            <select name="sort" onchange="this.form.submit()" style="border: none; background: transparent; font-size: 14px; font-weight: 600; color: var(--text); outline: none; cursor: pointer;">
                <option value="newest" <?= $sort == 'newest' ? 'selected' : '' ?>>Mới nhất</option>
                <option value="price_asc" <?= $sort == 'price_asc' ? 'selected' : '' ?>>Giá thấp đến cao</option>
                <option value="price_desc" <?= $sort == 'price_desc' ? 'selected' : '' ?>>Giá cao đến thấp</option>
            </select>
        </div>

        <div style="display: flex; gap: 10px; margin-left: auto;">
            <button type="submit" class="btn-submit">Áp dụng</button>
            <?php if ($is_filtering): ?>
                <a href="<?= BASE_URL ?>?id=<?= $category_id ?>" class="btn btn-outline" style="padding: 11px 18px; border-radius: 12px; height: 46px; box-sizing: border-box; display: flex; align-items: center; border-color: #e2e8f0; color: #718096; text-decoration: none;">
                    Xóa lọc
                </a>
            <?php endif; ?>
        </div>
      </form>
    </div>

    <!-- Grid -->
    <div class="product-grid">
      <?php if ($result && mysqli_num_rows($result) > 0): ?>
        <?php while ($row = mysqli_fetch_assoc($result)): ?>
          <article class="product-card" aria-labelledby="prod-<?= $row['id'] ?>">
            <a class="product-thumb" href="<?= BASE_URL ?>pages/product_detail.php?id=<?= $row['id'] ?>" aria-label="<?= htmlspecialchars($row['name']) ?>">
              <img
                src="<?= BASE_URL ?>images/<?= htmlspecialchars($row['image'] ?? '') ?>"
                alt="<?= htmlspecialchars($row['name']) ?>"
                onerror="this.onerror=null;this.src='<?= BASE_URL ?>images/no-image.png';"
              >
            </a>

            <div class="product-info">
              <a id="prod-<?= $row['id'] ?>" class="product-name" href="<?= BASE_URL ?>pages/product_detail.php?id=<?= $row['id'] ?>">
                <?= htmlspecialchars($row['name']) ?>
              </a>

              <p class="product-desc"><?= htmlspecialchars($row['description'] ?? '') ?></p>

              <div class="product-meta">
                <div class="price-wrap">
                  <div class="price-sale"><?= number_format($row['price'], 0, ',', '.') ?>đ</div>
                </div>

                <?php if (isset($row['stock']) && $row['stock'] > 0): ?>
                  <span class="status-badge status-in">Còn hàng</span>
                <?php else: ?>
                  <span class="status-badge status-out">Hết hàng</span>
                <?php endif; ?>
              </div>
          
              <div class="card-footer" style="margin-top:10px;">
                <button 
                    class="btn btn-primary" 
                    type="button" 
                    onclick="addCart(<?= (int)$row['id'] ?>)"
                    <?= (isset($row['stock']) && $row['stock'] <= 0) ? 'disabled style="background: #cbd5e0; cursor: not-allowed;"' : '' ?>
                >
                  Thêm vào giỏ
                </button>

                <a class="btn btn-outline" href="<?= BASE_URL ?>pages/product_detail.php?id=<?= $row['id'] ?>" style="text-decoration:none;">
                  Chi tiết
                </a>
              </div>
            </div>
          </article>
        <?php endwhile; ?>
      <?php else: ?>
        <div class="u-center" style="grid-column:1/-1;padding:60px 0; background: #fff; border-radius: 20px; box-shadow: var(--surface-shadow);">
          <div style="font-size: 60px; margin-bottom: 20px;">🏜️</div>
          <h3 style="color: #2d3748; margin-bottom: 10px;">Không tìm thấy kết quả</h3>
          <p class="u-muted" style="margin-bottom: 25px;">Thử thay đổi từ khóa tìm kiếm hoặc khoảng giá xem sao bạn nhé!</p>
          <a href="<?= BASE_URL ?>" class="btn btn-primary" style="max-width: 200px; margin: 0 auto;">Xem tất cả sản phẩm</a>
        </div>
      <?php endif; ?>
    </div>

  </div>
</section>
 
<?php include(__DIR__ . "/includes/footer.php"); ?>

