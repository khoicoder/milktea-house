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
    $tmp = $min;
    $min = $max;
    $max = $tmp;
}

$search_esc = mysqli_real_escape_string($conn, $search);

$sql = "SELECT id, name, price, image, description, stock FROM products WHERE price BETWEEN {$min} AND {$max}";

if ($category_id > 0) {
    $sql .= " AND category_id = {$category_id}";
}

if ($search_esc !== '') {
    $sql .= " AND name LIKE '%{$search_esc}%'";
}

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

$category_name = "Đồ uống phổ biến";
if ($category_id > 0) {
    $res_cat = mysqli_query($conn, "SELECT name FROM categories WHERE id = $category_id");
    if ($cat_row = mysqli_fetch_assoc($res_cat)) {
        $category_name = $cat_row['name'];
    }
}

$is_filtering = !empty($search) || $min > 0 || $max < 1000000 || $sort !== 'newest';
?>

<style>
.product-card .size-options {
    margin-top: 12px;
    display: grid;
    gap: 8px;
}
.product-card .size-options label {
    font-size: 14px;
    font-weight: 600;
    color: #4a5568;
}
.product-card .size-options select {
    width: 100%;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 10px 12px;
    outline: none;
    background: #fff;
    font-size: 14px;
}
.product-card .selected-price {
    margin-top: 10px;
    font-size: 15px;
    font-weight: 700;
    color: #e53e3e;
}
.product-card .selected-price.ok {
    color: #16a34a;
}
.product-card .selected-price.warn {
    color: #e53e3e;
}
.product-card .card-footer {
    display: flex;
    gap: 10px;
    align-items: center;
    flex-wrap: wrap;
}
.product-card .card-footer .btn {
    flex: 1;
    justify-content: center;
}
</style>

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

    <div class="product-grid">
      <?php if ($result && mysqli_num_rows($result) > 0): ?>
        <?php while ($row = mysqli_fetch_assoc($result)): ?>
          <?php
            $product_id = (int)$row['id'];

            $sizes = [];
            $total_stock = 0;
            $min_price = null;

            $res_size = mysqli_query($conn, "
                SELECT ps.id, s.name AS size_name, ps.price, ps.stock
                FROM product_sizes ps
                JOIN sizes s ON ps.size_id = s.id
                WHERE ps.product_id = {$product_id}
                ORDER BY s.id ASC
            ");

            if ($res_size && mysqli_num_rows($res_size) > 0) {
                while ($size_row = mysqli_fetch_assoc($res_size)) {
                    $sizes[] = $size_row;
                    $total_stock += (int)$size_row['stock'];
                    if ($min_price === null || (int)$size_row['price'] < $min_price) {
                        $min_price = (int)$size_row['price'];
                    }
                }
            } else {
                $total_stock = (int)$row['stock'];
                $min_price = (int)$row['price'];
            }

            $has_sizes = count($sizes) > 0;
          ?>

          <article class="product-card" aria-labelledby="prod-<?= $product_id ?>">
            <a class="product-thumb" href="<?= BASE_URL ?>pages/product_detail.php?id=<?= $product_id ?>" aria-label="<?= htmlspecialchars($row['name']) ?>">
              <img
                src="<?= BASE_URL ?>images/<?= htmlspecialchars($row['image'] ?? '') ?>"
                alt="<?= htmlspecialchars($row['name']) ?>"
                onerror="this.onerror=null;this.src='<?= BASE_URL ?>images/no-image.png';"
              >
            </a>

            <div class="product-info">
              <a id="prod-<?= $product_id ?>" class="product-name" href="<?= BASE_URL ?>pages/product_detail.php?id=<?= $product_id ?>">
                <?= htmlspecialchars($row['name']) ?>
              </a>

              <p class="product-desc"><?= htmlspecialchars($row['description'] ?? '') ?></p>

              <div class="product-meta">
                <div class="price-wrap">
                  <div id="price-display-<?= $product_id ?>" class="price-sale">
                    <?= $has_sizes ? ('Từ ' . number_format($min_price, 0, ',', '.') . 'đ') : number_format($min_price, 0, ',', '.') . 'đ' ?>
                  </div>
                </div>

                <?php if ($total_stock > 0): ?>
                  <span class="status-badge status-in">Còn hàng</span>
                <?php else: ?>
                  <span class="status-badge status-out">Hết hàng</span>
                <?php endif; ?>
              </div>

              <?php if ($has_sizes): ?>
                <div class="size-options">
                  <label for="size-select-<?= $product_id ?>">Chọn size:</label>
                  <select id="size-select-<?= $product_id ?>" onchange="updateCardPrice(<?= $product_id ?>)">
                    <option value="">-- Chọn size --</option>
                    <?php foreach ($sizes as $size): ?>
                      <option
                        value="<?= (int)$size['id'] ?>"
                        data-price="<?= (int)$size['price'] ?>"
                        data-stock="<?= (int)$size['stock'] ?>"
                        <?= ((int)$size['stock'] <= 0) ? 'disabled' : '' ?>
                      >
                        <?= htmlspecialchars($size['size_name']) ?> - <?= number_format((int)$size['price'], 0, ',', '.') ?>đ
                        <?= ((int)$size['stock'] <= 0) ? ' (Hết hàng)' : '' ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>

                <div id="selected-info-<?= $product_id ?>" class="selected-price warn">
                  Vui lòng chọn size
                </div>
              <?php endif; ?>

              <div class="card-footer" style="margin-top:10px;">
                <button
                    id="add-cart-btn-<?= $product_id ?>"
                    class="btn btn-primary"
                    type="button"
                    onclick="addCart(<?= $product_id ?>)"
                    <?= ($total_stock <= 0) ? 'disabled style="background: #cbd5e0; cursor: not-allowed;"' : ($has_sizes ? 'disabled' : '') ?>
                >
                  Thêm vào giỏ
                </button>

                <a class="btn btn-outline" href="<?= BASE_URL ?>pages/product_detail.php?id=<?= $product_id ?>" style="text-decoration:none;">
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

<script>
    window.BASE_URL = '<?= rtrim(BASE_URL, '/') . '/' ?>';

    function updateCardPrice(productId) {
        const sizeSelect = document.getElementById(`size-select-${productId}`);
        const priceDisplay = document.getElementById(`price-display-${productId}`);
        const infoDisplay = document.getElementById(`selected-info-${productId}`);
        const addCartBtn = document.getElementById(`add-cart-btn-${productId}`);

        if (!sizeSelect || !priceDisplay || !addCartBtn) return;

        const selectedOption = sizeSelect.options[sizeSelect.selectedIndex];

        if (!selectedOption || !selectedOption.value) {
            priceDisplay.textContent = 'Vui lòng chọn size';
            priceDisplay.classList.remove('ok');
            priceDisplay.classList.add('warn');

            if (infoDisplay) {
                infoDisplay.textContent = 'Vui lòng chọn size';
                infoDisplay.classList.remove('ok');
                infoDisplay.classList.add('warn');
            }

            addCartBtn.disabled = true;
            return;
        }

        const price = parseInt(selectedOption.getAttribute('data-price')) || 0;
        const stock = parseInt(selectedOption.getAttribute('data-stock')) || 0;

        priceDisplay.textContent = `Giá: ${price.toLocaleString('vi-VN')}đ`;

        if (stock > 0) {
            priceDisplay.classList.add('ok');
            priceDisplay.classList.remove('warn');

            if (infoDisplay) {
                infoDisplay.textContent = `Còn hàng size này (${stock})`;
                infoDisplay.classList.add('ok');
                infoDisplay.classList.remove('warn');
            }

            addCartBtn.disabled = false;
            addCartBtn.style.background = '';
            addCartBtn.style.cursor = 'pointer';
        } else {
            priceDisplay.classList.remove('ok');
            priceDisplay.classList.add('warn');

            if (infoDisplay) {
                infoDisplay.textContent = 'Size này đã hết hàng';
                infoDisplay.classList.remove('ok');
                infoDisplay.classList.add('warn');
            }

            addCartBtn.disabled = true;
            addCartBtn.style.background = '#cbd5e0';
            addCartBtn.style.cursor = 'not-allowed';
        }
    }

    function setHeaderCartCount(count) {
        const countText = String(count);

        const possibleTargets = [
            document.getElementById('cart-count'),
            document.getElementById('header-cart-count'),
            document.querySelector('.cart-count'),
            document.querySelector('[data-cart-count]')
        ].filter(Boolean);

        possibleTargets.forEach(el => {
            el.textContent = countText;
        });
    }

    async function addCart(productId) {
        const sizeSelect = document.getElementById(`size-select-${productId}`);
        let productSizeId = '';

        if (sizeSelect) {
            productSizeId = sizeSelect.value;

            if (!productSizeId) {
                alert('Vui lòng chọn size trước khi thêm vào giỏ hàng');
                return;
            }
        }

        try {
            const body = new URLSearchParams();
            body.append('id', productId);

            if (productSizeId) {
                body.append('product_size_id', productSizeId);
            }

            const res = await fetch(window.BASE_URL + 'ajax/add_cart.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: body.toString()
            });

            if (!res.ok) {
                throw new Error(`Server response: ${res.status} ${res.statusText}`);
            }

            const responseText = await res.text();
            const data = JSON.parse(responseText);

            if (data.status === 'success') {
                alert(data.message || 'Đã thêm vào giỏ hàng');

                if (typeof updateCartCount === 'function') {
                    updateCartCount(data.cart_count);
                } else if (data.cart_count !== undefined) {
                    setHeaderCartCount(data.cart_count);
                }
            } else {
                alert(data.message || 'Không thể thêm vào giỏ hàng');
            }
        } catch (error) {
            console.error('Lỗi kết nối:', error);
            alert('Lỗi: ' + error.message);
        }
    }

    window.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('[id^="size-select-"]').forEach(select => {
            const productId = select.id.replace('size-select-', '');
            updateCardPrice(productId);
        });
    });
</script>