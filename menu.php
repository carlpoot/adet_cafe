<?php
require_once "includes/auth.php";
require_once "includes/db.php";

$categoryStmt = $conn->prepare("
    SELECT category_id, category_name, category_slug
    FROM categories
    WHERE is_active = 1
    ORDER BY display_order ASC, category_name ASC
");
$categoryStmt->execute();
$categories = $categoryStmt->fetchAll();

$productStmt = $conn->prepare("
    SELECT
        p.product_id,
        p.product_name,
        p.description,
        p.price,
        p.image_path,
        p.promo_badge,
        p.is_available,
        p.is_featured,
        p.is_bestseller,
        c.category_name,
        c.category_slug
    FROM products p
    INNER JOIN categories c ON p.category_id = c.category_id
    WHERE p.is_available = 1
    ORDER BY p.display_order ASC, p.product_name ASC
");
$productStmt->execute();
$products = $productStmt->fetchAll();

function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function peso($value) {
    return "₱" . number_format((float)$value, 2);
}

function product_image($path) {
    if (!$path) {
        return "https://images.unsplash.com/photo-1495474472287-4d71bcdd2085?auto=format&fit=crop&w=900&q=80";
    }

    return $path;
}

$counts = ["all" => count($products)];

foreach ($categories as $category) {
    $counts[$category["category_slug"]] = 0;
}

foreach ($products as $product) {
    $slug = $product["category_slug"];
    if (!isset($counts[$slug])) {
        $counts[$slug] = 0;
    }
    $counts[$slug]++;
}

$activeCategory = $_GET["category"] ?? "all";
$validSlugs = array_merge(["all"], array_column($categories, "category_slug"));

if (!in_array($activeCategory, $validSlugs, true)) {
    $activeCategory = "all";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>FurrfectCafe | Menu</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>

  <header class="site-header">
    <div class="container navbar">
      <a href="index.php" class="brand" aria-label="FurrfectCafe home">
        <span class="brand-mark">FC</span>
        <span class="brand-name">FurrfectCafe</span>
      </a>

      <nav class="nav-links" aria-label="Primary navigation">
        <a href="index.php">Home</a>
        <a href="menu.php" class="active">Menu</a>
        <a href="orders.php">My Orders</a>
      </nav>

      <div class="nav-actions">
        <a href="cart.php" class="cart-pill" aria-label="Cart">
          <span>🛒 Cart</span>
          <span class="cart-pill-count" data-cart-count>0</span>
        </a>

        <a href="profile.php" class="btn btn-secondary btn-sm">👤 Profile</a>

        <button type="button" class="btn btn-secondary btn-sm" data-customer-logout>
          Logout
        </button>
      </div>

      <button class="menu-toggle" aria-label="Open menu" aria-expanded="false">☰</button>
    </div>
  </header>

  <section class="page-hero">
    <div class="container">
      <div class="menu-hero">
        <span class="eyebrow" style="color: #f4bf88;">Our Menu</span>
        <h1 class="section-title" style="color: white;">Browse your café favorites</h1>
        <p class="section-text">
          Use the category list and search bar to quickly find drinks, pastries, and all-day bites.
        </p>
      </div>
    </div>
  </section>

  <section class="section-sm">
    <div class="container">
      <div class="mobile-category-scroll">
        <button type="button" class="menu-category-btn <?php echo $activeCategory === 'all' ? 'active' : ''; ?>" data-menu-category="all">
          All <span><?php echo (int)$counts["all"]; ?></span>
        </button>

        <?php foreach ($categories as $category): ?>
          <button
            type="button"
            class="menu-category-btn <?php echo $activeCategory === $category["category_slug"] ? 'active' : ''; ?>"
            data-menu-category="<?php echo e($category["category_slug"]); ?>">
            <?php echo e($category["category_name"]); ?>
            <span><?php echo (int)($counts[$category["category_slug"]] ?? 0); ?></span>
          </button>
        <?php endforeach; ?>
      </div>

      <div class="menu-layout">
        <aside class="menu-sidebar">
          <h2 class="menu-sidebar-title">Categories</h2>

          <div class="menu-category-list">
            <button type="button" class="menu-category-btn <?php echo $activeCategory === 'all' ? 'active' : ''; ?>" data-menu-category="all">
              All <span><?php echo (int)$counts["all"]; ?></span>
            </button>

            <?php foreach ($categories as $category): ?>
              <button
                type="button"
                class="menu-category-btn <?php echo $activeCategory === $category["category_slug"] ? 'active' : ''; ?>"
                data-menu-category="<?php echo e($category["category_slug"]); ?>">
                <?php echo e($category["category_name"]); ?>
                <span><?php echo (int)($counts[$category["category_slug"]] ?? 0); ?></span>
              </button>
            <?php endforeach; ?>
          </div>
        </aside>

        <main class="menu-content-panel">
          <div class="menu-top-tools">
            <div class="search-wrap">
              <span>🔎</span>
              <input type="search" id="databaseMenuSearch" placeholder="Search coffee, pastries, sandwiches..." autocomplete="off">
            </div>

            <select id="databaseMenuSort" class="menu-sort" aria-label="Sort menu">
              <option value="default">Sort: Featured</option>
              <option value="price-low">Price: Low to High</option>
              <option value="price-high">Price: High to Low</option>
              <option value="name">Name: A to Z</option>
            </select>
          </div>

          <p class="menu-active-label">
            Showing: <strong id="activeCategoryLabel">All Items</strong>
            <span>•</span>
            <span><strong data-menu-count><?php echo (int)count($products); ?></strong> items found</span>
          </p>

          <div class="grid menu-grid compact-products mt-24" id="databaseMenuGrid">
            <?php foreach ($products as $product): ?>
              <article
                class="product-card card simple-menu-card database-product-card"
                data-name="<?php echo e(strtolower($product["product_name"])); ?>"
                data-description="<?php echo e(strtolower($product["description"] ?? "")); ?>"
                data-category="<?php echo e($product["category_slug"]); ?>"
                data-category-label="<?php echo e($product["category_name"]); ?>"
                data-price="<?php echo e($product["price"]); ?>">

                <a href="product.php?id=<?php echo (int)$product["product_id"]; ?>" class="product-media" aria-label="View <?php echo e($product["product_name"]); ?>">
                  <?php if (!empty($product["promo_badge"])): ?>
                    <div class="product-badge-wrap">
                      <span class="badge badge-accent"><?php echo e($product["promo_badge"]); ?></span>
                    </div>
                  <?php endif; ?>

                  <img
                    src="<?php echo e(product_image($product["image_path"])); ?>"
                    alt="<?php echo e($product["product_name"]); ?>"
                    onerror="this.src='https://images.unsplash.com/photo-1495474472287-4d71bcdd2085?auto=format&fit=crop&w=900&q=80';">
                </a>

                <div class="product-body simple-menu-body">
                  <h3 class="product-title">
                    <a href="product.php?id=<?php echo (int)$product["product_id"]; ?>">
                      <?php echo e($product["product_name"]); ?>
                    </a>
                  </h3>

                  <div class="price"><?php echo peso($product["price"]); ?></div>
                </div>
              </article>
            <?php endforeach; ?>
          </div>

          <div class="empty-state hidden" id="databaseMenuEmpty">
            <h3>No items found</h3>
            <p class="section-text">Try another search keyword or category.</p>
          </div>
        </main>
      </div>
    </div>
  </section>

  <footer class="site-footer">
    <div class="container footer-inner">
      <div>
        <div class="footer-brand">
          <span class="brand-mark">FC</span>
          <strong class="brand-name">FurrfectCafe</strong>
        </div>

        <p class="footer-text">
          A cozy cat café in Legazpi City, Albay. Order online and enjoy your favorites at home or pick them up fresh.
        </p>

        <div class="socials">
          <a href="index.php" aria-label="Facebook">f</a>
          <a href="index.php" aria-label="Instagram">◎</a>
          <a href="index.php" aria-label="TikTok">♪</a>
          <a href="index.php" aria-label="Twitter">x</a>
        </div>
      </div>

      <div>
        <h3 class="footer-heading">Quick Links</h3>

        <div class="footer-links">
          <a href="index.php">Home</a>
          <a href="menu.php">Menu</a>
          <a href="index.php#how-it-works">How It Works</a>
          <a href="orders.php">My Orders</a>
        </div>
      </div>

      <div>
        <h3 class="footer-heading">Account</h3>

        <div class="footer-links">
          <a href="profile.php">Profile</a>
          <a href="orders.php">Order History</a>
          <a href="cart.php">Cart</a>
          <a href="login.php">Login Portal</a>
        </div>
      </div>

      <div>
        <h3 class="footer-heading">Contact</h3>

        <div class="footer-contact">
          <span>📍 Legazpi City, Albay</span>
          <a href="tel:+639123456789">📞 +63 912 345 6789</a>
          <a href="mailto:hello@furrfectcafe.ph">✉️ hello@furrfectcafe.ph</a>
          <span>🕘 8AM - 9PM daily</span>
        </div>
      </div>
    </div>

    <div class="container footer-bottom">
      © 2026 FurrfectCafe. All rights reserved.
    </div>
  </footer>

  <script src="script.js"></script>
  <script>
    document.addEventListener("DOMContentLoaded", () => {
      const grid = document.getElementById("databaseMenuGrid");
      const cards = Array.from(document.querySelectorAll(".database-product-card"));
      const searchInput = document.getElementById("databaseMenuSearch");
      const sortSelect = document.getElementById("databaseMenuSort");
      const categoryButtons = document.querySelectorAll("[data-menu-category]");
      const activeCategoryLabel = document.getElementById("activeCategoryLabel");
      const countEl = document.querySelector("[data-menu-count]");
      const emptyState = document.getElementById("databaseMenuEmpty");

      let activeCategory = "<?php echo e($activeCategory); ?>";
      let activeSearch = "";
      let activeSort = "default";

      function getCategoryLabel(categoryId) {
        if (categoryId === "all") return "All Items";

        const button = Array.from(categoryButtons).find(item => item.dataset.menuCategory === categoryId);
        return button ? button.childNodes[0].textContent.trim() : "All Items";
      }

      function syncCategoryButtons() {
        categoryButtons.forEach(button => {
          button.classList.toggle("active", button.dataset.menuCategory === activeCategory);
        });
      }

      function renderMenu() {
        let visibleCards = cards.filter(card => {
          const haystack = `${card.dataset.name} ${card.dataset.description} ${card.dataset.categoryLabel}`.toLowerCase();
          const matchesSearch = haystack.includes(activeSearch.toLowerCase());
          const matchesCategory = activeCategory === "all" || card.dataset.category === activeCategory;

          return matchesSearch && matchesCategory;
        });

        if (activeSort === "price-low") {
          visibleCards = visibleCards.sort((a, b) => Number(a.dataset.price) - Number(b.dataset.price));
        }

        if (activeSort === "price-high") {
          visibleCards = visibleCards.sort((a, b) => Number(b.dataset.price) - Number(a.dataset.price));
        }

        if (activeSort === "name") {
          visibleCards = visibleCards.sort((a, b) => a.dataset.name.localeCompare(b.dataset.name));
        }

        cards.forEach(card => {
          card.classList.add("hidden");
        });

        visibleCards.forEach(card => {
          card.classList.remove("hidden");
          grid.appendChild(card);
        });

        countEl.textContent = visibleCards.length;
        activeCategoryLabel.textContent = getCategoryLabel(activeCategory);
        emptyState.classList.toggle("hidden", visibleCards.length > 0);

        syncCategoryButtons();
      }

      categoryButtons.forEach(button => {
        button.addEventListener("click", () => {
          activeCategory = button.dataset.menuCategory;
          renderMenu();
        });
      });

      searchInput.addEventListener("input", event => {
        activeSearch = event.target.value.trim();
        renderMenu();
      });

      sortSelect.addEventListener("change", event => {
        activeSort = event.target.value;
        renderMenu();
      });

      renderMenu();
    });
  </script>
</body>
</html>
