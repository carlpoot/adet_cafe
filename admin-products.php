<?php
require_once "includes/admin-auth.php";
require_once "includes/db.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["delete_product_id"])) {
    $deleteId = (int) $_POST["delete_product_id"];

    if ($deleteId > 0) {
        try {
            $stmt = $conn->prepare("DELETE FROM products WHERE product_id = ?");
            $stmt->execute([$deleteId]);
            $message = "Product deleted successfully.";
        } catch (Exception $e) {
            $message = "Could not delete product. It may already be connected to existing orders.";
        }
    }
}

$categoriesStmt = $conn->query("SELECT category_name FROM categories WHERE is_active = 1 ORDER BY display_order, category_name");
$categories = $categoriesStmt->fetchAll();

$productsStmt = $conn->query("
    SELECT 
        p.product_id,
        p.product_name,
        p.description,
        p.price,
        p.image_path,
        p.promo_badge,
        p.is_available,
        c.category_name,
        COALESCE(SUM(oi.quantity), 0) AS orders_count
    FROM products p
    JOIN categories c ON p.category_id = c.category_id
    LEFT JOIN order_items oi ON p.product_id = oi.product_id
    GROUP BY 
        p.product_id,
        p.product_name,
        p.description,
        p.price,
        p.image_path,
        p.promo_badge,
        p.is_available,
        c.category_name
    ORDER BY p.updated_at DESC, p.product_id DESC
");
$products = $productsStmt->fetchAll();

if (isset($_GET["saved"])) {
    $message = "Product saved successfully.";
}

function h($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>FurrfectCafe Admin | Products</title>
  <link rel="stylesheet" href="style.css">
  <style>
    .products-shell {
      background: var(--surface);
      border: 1px solid var(--border);
      border-radius: 26px;
      box-shadow: var(--shadow-sm);
      padding: 18px;
    }

    .products-toolbar {
      display: grid;
      grid-template-columns: 1.4fr 0.8fr 0.8fr;
      gap: 12px;
      margin-bottom: 16px;
    }

    .admin-search,
    .admin-select {
      width: 100%;
      min-height: 52px;
      border: 1px solid var(--border);
      border-radius: 16px;
      background: var(--surface);
      padding: 14px 16px;
      outline: none;
      font-family: Arial, Helvetica, sans-serif;
    }

    .product-cell {
      display: flex;
      align-items: center;
      gap: 12px;
      min-width: 220px;
    }

    .product-thumb {
      width: 52px;
      height: 52px;
      border-radius: 12px;
      object-fit: cover;
      flex-shrink: 0;
      background: #f1e6d8;
      border: 1px solid var(--border);
    }

    .product-cell small {
      display: block;
      color: var(--text-muted);
      font-family: Arial, Helvetica, sans-serif;
      margin-top: 3px;
      max-width: 260px;
    }

    .action-row {
      display: flex;
      gap: 8px;
      flex-wrap: wrap;
    }

    .mini-btn {
      min-height: 34px;
      padding: 8px 12px;
      border-radius: 999px;
      font-family: Arial, Helvetica, sans-serif;
      font-weight: 700;
      border: 1px solid var(--border);
      background: #fff;
      display: inline-flex;
      align-items: center;
      justify-content: center;
    }

    .mini-btn.edit {
      color: var(--accent);
    }

    .mini-btn.delete {
      color: white;
      background: #d25454;
      border-color: #d25454;
    }

    .alert {
      padding: 14px 16px;
      border-radius: 16px;
      margin-bottom: 16px;
      font-family: Arial, Helvetica, sans-serif;
      font-weight: 700;
      background: rgba(75, 155, 99, 0.12);
      color: var(--success);
      border: 1px solid rgba(75, 155, 99, 0.25);
    }

    .empty-row {
      text-align: center;
      padding: 32px;
      color: var(--text-muted);
      font-family: Arial, Helvetica, sans-serif;
    }

    @media (max-width: 980px) {
      .products-toolbar {
        grid-template-columns: 1fr;
      }
    }
  </style>
</head>
<body>
  <div class="admin-layout">
    <aside class="admin-sidebar">
      <a href="admin-dashboard.php" class="brand">
        <span class="brand-mark">CAFE</span>
        <span class="brand-name">FurrfectCafe</span>
      </a>

      <div class="eyebrow" style="color: rgba(255,255,255,0.45); margin-top: 10px;">Main</div>
      <nav class="admin-nav">
        <a href="admin-dashboard.php">Dashboard</a>
      </nav>

      <div class="eyebrow" style="color: rgba(255,255,255,0.45); margin-top: 20px;">Orders</div>
      <nav class="admin-nav">
        <a href="admin-orders.php">Orders</a>
      </nav>

      <div class="eyebrow" style="color: rgba(255,255,255,0.45); margin-top: 20px;">Catalog</div>
      <nav class="admin-nav">
        <a href="admin-products.php" class="active">Products</a>
        <a href="admin-product-form.php">+ Add / Edit Product</a>
      </nav>

      <div class="admin-sidebar-footer">
        <div class="admin-user-mini">
          <div class="admin-user-avatar">A</div>
          <div>
            <strong style="display:block;">Admin</strong>
            <span style="font-family:Arial, Helvetica, sans-serif; color:rgba(255,255,255,0.65); font-size:0.9rem;">Administrator</span>
          </div>
        </div>
        <a href="logout.php" class="admin-logout-btn" style="display:flex; align-items:center; justify-content:center;">Logout</a>
      </div>
    </aside>

    <main class="admin-content">
      <div class="admin-topbar">
        <h1 class="admin-page-title">☰ Products</h1>
        <div class="admin-toolbar-right">
          <a href="admin-product-form.php" class="btn btn-primary btn-sm">＋ Add New Product</a>
        </div>
      </div>

      <?php if ($message): ?>
        <div class="alert"><?php echo h($message); ?></div>
      <?php endif; ?>

      <section class="products-shell">
        <div class="products-toolbar">
          <input type="text" class="admin-search" id="productSearch" placeholder="Search product...">

          <select class="admin-select" id="categoryFilter">
            <option value="all">All Categories</option>
            <?php foreach ($categories as $category): ?>
              <option value="<?php echo h($category["category_name"]); ?>"><?php echo h($category["category_name"]); ?></option>
            <?php endforeach; ?>
          </select>

          <select class="admin-select" id="availabilityFilter">
            <option value="all">All Status</option>
            <option value="Available">Available</option>
            <option value="Hidden">Hidden</option>
          </select>
        </div>

        <div class="panel-header">
          <h2 class="form-title" style="margin:0;">All Products (<?php echo count($products); ?>)</h2>
          <span style="font-family:Arial, Helvetica, sans-serif; color:var(--text-muted);">Loaded from database</span>
        </div>

        <div class="table-wrap">
          <table>
            <thead>
              <tr>
                <th>Image</th>
                <th>Product Name</th>
                <th>Category</th>
                <th>Price</th>
                <th>Badge</th>
                <th>Available</th>
                <th>Orders</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody id="productsTableBody">
              <?php if (!$products): ?>
                <tr>
                  <td colspan="8" class="empty-row">No products found.</td>
                </tr>
              <?php endif; ?>

              <?php foreach ($products as $product): ?>
                <?php
                  $availabilityText = ((int)$product["is_available"] === 1) ? "Available" : "Hidden";
                  $imagePath = $product["image_path"] ?: "assets/images/placeholder-product.jpg";
                ?>
                <tr 
                  data-product-name="<?php echo h(strtolower($product["product_name"])); ?>"
                  data-category="<?php echo h($product["category_name"]); ?>"
                  data-availability="<?php echo h($availabilityText); ?>"
                >
                  <td>
                    <img src="<?php echo h($imagePath); ?>" alt="<?php echo h($product["product_name"]); ?>" class="product-thumb">
                  </td>

                  <td>
                    <div class="product-cell" style="gap:0;">
                      <div>
                        <strong><?php echo h($product["product_name"]); ?></strong>
                        <?php if (!empty($product["description"])): ?>
                          <small><?php echo h(mb_strimwidth($product["description"], 0, 70, "...")); ?></small>
                        <?php endif; ?>
                      </div>
                    </div>
                  </td>

                  <td><span class="product-category" style="margin:0;"><?php echo h($product["category_name"]); ?></span></td>
                  <td>₱<?php echo number_format((float)$product["price"], 2); ?></td>

                  <td>
                    <?php if (!empty($product["promo_badge"])): ?>
                      <span class="badge badge-accent"><?php echo h($product["promo_badge"]); ?></span>
                    <?php else: ?>
                      —
                    <?php endif; ?>
                  </td>

                  <td>
                    <?php if ((int)$product["is_available"] === 1): ?>
                      <span class="badge badge-success">Yes</span>
                    <?php else: ?>
                      <span class="badge badge-danger">Hidden</span>
                    <?php endif; ?>
                  </td>

                  <td><?php echo (int)$product["orders_count"]; ?></td>

                  <td>
                    <div class="action-row">
                      <a href="admin-product-form.php?id=<?php echo (int)$product["product_id"]; ?>" class="mini-btn edit">✎ Edit</a>

                      <form method="POST">
                        <input type="hidden" name="delete_product_id" value="<?php echo (int)$product["product_id"]; ?>">
                        <button type="submit" class="mini-btn delete">🗑 Delete</button>
                      </form>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </section>
    </main>
  </div>

  <script src="script.js"></script>
  <script>
    document.addEventListener("DOMContentLoaded", () => {
      const rows = Array.from(document.querySelectorAll("#productsTableBody tr[data-product-name]"));
      const productSearch = document.getElementById("productSearch");
      const categoryFilter = document.getElementById("categoryFilter");
      const availabilityFilter = document.getElementById("availabilityFilter");

      function filterProducts() {
        const searchValue = productSearch.value.trim().toLowerCase();
        const categoryValue = categoryFilter.value;
        const availabilityValue = availabilityFilter.value;

        rows.forEach(row => {
          const matchesSearch = row.dataset.productName.includes(searchValue);
          const matchesCategory = categoryValue === "all" || row.dataset.category === categoryValue;
          const matchesAvailability = availabilityValue === "all" || row.dataset.availability === availabilityValue;

          row.style.display = matchesSearch && matchesCategory && matchesAvailability ? "" : "none";
        });
      }

      productSearch.addEventListener("input", filterProducts);
      categoryFilter.addEventListener("change", filterProducts);
      availabilityFilter.addEventListener("change", filterProducts);
    });
  </script>
</body>
</html>
