<?php require_once "includes/auth.php"; ?>
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
        <span class="brand-mark">CAFE</span>
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
        <button type="button" class="menu-category-btn active" data-menu-category="all">All <span>12</span></button>
        <button type="button" class="menu-category-btn" data-menu-category="hot-drinks">Hot Drinks <span>2</span></button>
        <button type="button" class="menu-category-btn" data-menu-category="cold-drinks">Cold Drinks <span>3</span></button>
        <button type="button" class="menu-category-btn" data-menu-category="pastries">Pastries <span>3</span></button>
        <button type="button" class="menu-category-btn" data-menu-category="all-day-bites">All-Day Bites <span>2</span></button>
        <button type="button" class="menu-category-btn" data-menu-category="fruit-blends">Fruit Blends <span>2</span></button>
      </div>

      <div class="menu-layout">
        <aside class="menu-sidebar">
          <h2 class="menu-sidebar-title">Categories</h2>

          <div class="menu-category-list">
            <button type="button" class="menu-category-btn active" data-menu-category="all">All <span>12</span></button>
            <button type="button" class="menu-category-btn" data-menu-category="hot-drinks">Hot Drinks <span>2</span></button>
            <button type="button" class="menu-category-btn" data-menu-category="cold-drinks">Cold Drinks <span>3</span></button>
            <button type="button" class="menu-category-btn" data-menu-category="pastries">Pastries <span>3</span></button>
            <button type="button" class="menu-category-btn" data-menu-category="all-day-bites">All-Day Bites <span>2</span></button>
            <button type="button" class="menu-category-btn" data-menu-category="fruit-blends">Fruit Blends <span>2</span></button>
          </div>
        </aside>

        <main class="menu-content-panel">
          <div class="menu-top-tools">
            <div class="search-wrap">
              <span>🔎</span>
              <input type="text" id="menuSearch" placeholder="Search menu items...">
            </div>

            <select class="menu-sort" id="menuSort">
              <option value="default">Sort: Featured</option>
              <option value="price-low">Price: Low to High</option>
              <option value="price-high">Price: High to Low</option>
              <option value="name">Name: A to Z</option>
            </select>
          </div>

          <p class="menu-active-label">
            Showing: <strong id="activeCategoryLabel">All Items</strong>
            <span>•</span>
            <span><strong data-menu-count>0</strong> items found</span>
          </p>

          <div class="grid menu-grid compact-products mt-24" id="menuGrid"></div>
        </main>
      </div>
    </div>
  </section>

  <footer class="site-footer">
    <div class="container footer-inner">
      <div>
        <div class="footer-brand">
          <span class="brand-mark">CAFE</span>
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
      const menuGrid = document.getElementById("menuGrid");
      const searchInput = document.getElementById("menuSearch");
      const sortSelect = document.getElementById("menuSort");
      const categoryButtons = document.querySelectorAll("[data-menu-category]");
      const activeCategoryLabel = document.getElementById("activeCategoryLabel");
      const countEl = document.querySelector("[data-menu-count]");

      const products = window.FurrfectCafe.products;
      const categories = window.FurrfectCafe.categories;

      const params = new URLSearchParams(window.location.search);
      let activeCategory = params.get("category") || "all";
      let activeSearch = "";
      let activeSort = "default";

      function getCategoryLabel(categoryId) {
        const category = categories.find(item => item.id === categoryId);
        return category ? category.name : "All Items";
      }

      function syncCategoryButtons() {
        categoryButtons.forEach(button => {
          button.classList.toggle("active", button.dataset.menuCategory === activeCategory);
        });
      }

      function getFilteredProducts() {
        let items = products.filter(product => {
          const matchesCategory = activeCategory === "all" || product.category === activeCategory;
          const haystack = `${product.name} ${product.categoryLabel} ${product.description}`.toLowerCase();
          const matchesSearch = haystack.includes(activeSearch.toLowerCase());

          return matchesCategory && matchesSearch;
        });

        if (activeSort === "price-low") {
          items = [...items].sort((a, b) => a.price - b.price);
        }

        if (activeSort === "price-high") {
          items = [...items].sort((a, b) => b.price - a.price);
        }

        if (activeSort === "name") {
          items = [...items].sort((a, b) => a.name.localeCompare(b.name));
        }

        return items;
      }

      function renderMenu() {
        const items = getFilteredProducts();

        window.FurrfectCafe.renderProductGrid("#menuGrid", items);

        countEl.textContent = items.length;
        activeCategoryLabel.textContent = activeCategory === "all" ? "All Items" : getCategoryLabel(activeCategory);

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