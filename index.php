<?php
require_once(__DIR__ . "/config/config.php");
$page_css = "product.css";
include(__DIR__ . "/includes/header.php");


$category_id = intval($_GET['id'] ?? 0);
$search = $_GET['search'] ?? '';
$min = isset($_GET['min']) && $_GET['min'] !== '' ? intval($_GET['min']) : 0;
$max = isset($_GET['max']) && $_GET['max'] !== '' ? intval($_GET['max']) : 1000000;


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

$sql .= " ORDER BY id DESC";

$result = mysqli_query($conn, $sql);
?>

<section class="products product-page">
  <div class="container">

    <h2 class="products-title">Đồ uống phổ biến</h2>

    <!-- Filter form -->
    <form method="GET" class="filter-form" style="display:flex;gap:12px;align-items:center;flex-wrap:wrap;margin-bottom:20px;">
      <input type="hidden" name="id" value="<?= htmlspecialchars($category_id) ?>">

      <input
        type="text"
        name="search"
        class="u-muted"
        placeholder="Tìm sản phẩm..."
        value="<?= htmlspecialchars($search) ?>"
        style="flex:1;min-width:180px;padding:8px;border-radius:8px;border:1px solid #e7e7ea;"
      >

      <label class="u-muted" style="margin:0 6px 0 0;">Giá từ</label>
      <input type="number" name="min" value="<?= htmlspecialchars($min) ?>" min="0" style="width:110px;padding:8px;border-radius:8px;border:1px solid #e7e7ea;">

      <label class="u-muted" style="margin:0 6px 0 0;">đến</label>
      <input type="number" name="max" value="<?= htmlspecialchars($max) ?>" min="0" style="width:110px;padding:8px;border-radius:8px;border:1px solid #e7e7ea;">

      <button type="submit" class="btn btn-outline" style="padding:9px 14px;border-radius:10px;">Lọc</button>
    </form>

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
                  <div class="price-sale"><?= number_format($row['price'], 0, ',', '.') ?> VNĐ</div>
                </div>

                <?php if (isset($row['stock']) && $row['stock'] > 0): ?>
                  <span class="status-badge status-in">Còn hàng</span>
                <?php else: ?>
                  <span class="status-badge status-out">Hết hàng</span>
                <?php endif; ?>
              </div>
          
              <div class="card-footer" style="margin-top:10px;">
                <button class="btn btn-primary" type="button" onclick="addCart(<?= (int)$row['id'] ?>)">
                  Thêm vào giỏ
                </button>

                <a class="btn btn-outline" href="<?= BASE_URL ?>pages/product_detail.php?id=<?= $row['id'] ?>" style="text-decoration:none;">
                  Xem chi tiết
                </a>
              </div>
            </div>
          </article>
        <?php endwhile; ?>
      <?php else: ?>
        <div class="u-center" style="grid-column:1/-1;padding:40px 0;">
          <p class="u-muted">Không tìm thấy sản phẩm phù hợp.</p>
          <a href="<?= BASE_URL ?>" class="btn btn-outline">Xem tất cả</a>
        </div>
      <?php endif; ?>
    </div>

  </div>
</section>
 
<?php include(__DIR__ . "/includes/footer.php"); ?>

