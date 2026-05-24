<?php require_once "includes/auth.php"; ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>FurrfectCafe | Product</title>
  <link rel="stylesheet" href="style.css">
  <style>
    .product-page {
      padding: 28px 0 56px;
    }

    .breadcrumb {
      display: flex;
      align-items: center;
      gap: 8px;
      font-family: Arial, Helvetica, sans-serif;
      font-size: 0.92rem;
      color: var(--text-muted);
      margin-bottom: 18px;
      flex-wrap: wrap;
    }

    .breadcrumb a:hover {
      color: var(--primary);
    }

    .product-layout {
      display: grid;
      grid-template-columns: 1.05fr 0.95fr;
      gap: 30px;
      align-items: start;
    }

    .product-gallery {
      background: var(--surface);
      border: 1px solid var(--border);
      border-radius: 28px;
      box-shadow: var(--shadow-md);
      padding: 18px;
    }

    .product-main-image-wrap {
      position: relative;
      overflow: hidden;
      border-radius: 24px;
      background: linear-gradient(135deg, #f1e6d8, #e8d4bc);
      aspect-ratio: 1.05 / 1;
    }

    .product-main-image {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }

    .thumb-row {
      display: flex;
      gap: 12px;
      margin-top: 14px;
      flex-wrap: wrap;
    }

    .thumb-btn {
      width: 78px;
      height: 78px;
      padding: 0;
      border-radius: 18px;
      overflow: hidden;
      border: 2px solid transparent;
      background: var(--surface-2);
      box-shadow: var(--shadow-sm);
      transition: var(--transition);
    }

    .thumb-btn img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }

    .thumb-btn.active,
    .thumb-btn:hover {
      border-color: var(--accent);
      transform: translateY(-2px);
    }

    .product-info-card {
      background: var(--surface);
      border: 1px solid var(--border);
      border-radius: 28px;
      box-shadow: var(--shadow-md);
      padding: 24px;
    }

    .product-header-meta {
      display: flex;
      align-items: center;
      gap: 12px;
      flex-wrap: wrap;
      margin-bottom: 12px;
    }

    .product-big-title {
      font-size: clamp(2rem, 4vw, 3.4rem);
      line-height: 0.98;
      margin-bottom: 12px;
    }

    .price-row {
      display: flex;
      align-items: center;
      gap: 12px;
      flex-wrap: wrap;
      margin: 14px 0 20px;
    }

    .old-price {
      font-family: Arial, Helvetica, sans-serif;
      color: var(--text-muted);
      text-decoration: line-through;
      font-size: 1rem;
    }

    .option-block {
      padding-top: 18px;
      margin-top: 18px;
      border-top: 1px solid var(--border);
    }

    .option-label {
      display: block;
      font-family: Arial, Helvetica, sans-serif;
      font-size: 0.78rem;
      font-weight: 700;
      color: var(--text-muted);
      margin-bottom: 12px;
    }

    .size-options {
      display: grid;
      grid-template-columns: repeat(3, minmax(0, 1fr));
      gap: 10px;
    }

    .size-btn {
      min-height: 52px;
      padding: 12px;
      border-radius: 16px;
      border: 1.5px solid var(--border);
      background: var(--bg-soft);
      text-align: center;
      transition: var(--transition);
      font-family: Arial, Helvetica, sans-serif;
      color: var(--text);
    }

    .size-btn strong {
      display: block;
      font-size: 0.82rem;
      margin-bottom: 2px;
    }

    .size-btn span {
      font-size: 0.82rem;
      color: var(--text-muted);
    }

    .size-btn.active,
    .size-btn:hover {
      border-color: var(--accent);
      background: #fff7ef;
      box-shadow: 0 0 0 4px rgba(201, 122, 53, 0.10);
    }

    .addon-grid {
      display: grid;
      grid-template-columns: repeat(2, minmax(0, 1fr));
      gap: 10px;
    }

    .addon-item {
      display: flex;
      align-items: center;
      gap: 10px;
      background: var(--bg-soft);
      border: 1px solid var(--border);
      border-radius: 16px;
      padding: 12px 14px;
      font-family: Arial, Helvetica, sans-serif;
      font-size: 12px;
      transition: var(--transition);
    }

    .add-item strong {
      font-weight: 100;
    }

    .addon-item:hover {
      border-color: rgba(201, 122, 53, 0.5);
      background: #fff8f1;
    }

    .addon-item input {
      accent-color: var(--accent);
      width: 16px;
      height: 16px;
      margin: 0;
    }

    .qty-actions {
      display: flex;
      align-items: center;
      gap: 14px;
      flex-wrap: wrap;
      margin-top: 16px;
    }

    .qty-selector {
      display: inline-flex;
      align-items: center;
      background: var(--bg-soft);
      border: 1px solid var(--border);
      border-radius: 999px;
      overflow: hidden;
      min-height: 50px;
    }

    .qty-selector button {
      width: 48px;
      height: 50px;
      font-size: 1.2rem;
      color: var(--primary);
    }

    .qty-selector span {
      min-width: 46px;
      text-align: center;
      font-family: Arial, Helvetica, sans-serif;
      font-weight: 700;
    }

    .action-stack {
      display: grid;
      gap: 12px;
      margin-top: 18px;
    }

    .product-notes {
      display: grid;
      gap: 10px;
      margin-top: 20px;
      padding-top: 18px;
      border-top: 1px solid var(--border);
      font-family: Arial, Helvetica, sans-serif;
      color: var(--text-muted);
      font-size: 0.95rem;
    }

    .product-notes span {
      display: inline-flex;
      align-items: center;
      gap: 10px;
    }

    .mini-footer-card {
      margin-top: 24px;
      background: linear-gradient(135deg, #4b2617 0%, #2f180e 100%);
      color: #fff7ef;
      border-radius: 24px;
      padding: 22px;
    }

    .mini-footer-brand {
      display: flex;
      align-items: center;
      gap: 12px;
      margin-bottom: 12px;
    }

    .mini-footer-card p {
      font-family: Arial, Helvetica, sans-serif;
      color: rgba(255,255,255,0.82);
      margin-bottom: 16px;
    }


    .special-instructions-box {
      width: 100%;
      min-height: 92px;
      border-radius: 16px;
      border: 1px solid var(--border);
      background: var(--bg-soft);
      padding: 14px 16px;
      resize: vertical;
      outline: none;
      font-family: Arial, Helvetica, sans-serif;
      color: var(--text);
      transition: var(--transition);
    }

    .special-instructions-box:focus {
      border-color: rgba(201, 122, 53, 0.65);
      box-shadow: 0 0 0 4px rgba(201, 122, 53, 0.12);
      background: #fffaf5;
    }

    @media (max-width: 920px) {
      .product-layout {
        grid-template-columns: 1fr;
      }
    }

    @media (max-width: 620px) {
      .size-options,
      .addon-grid {
        grid-template-columns: 1fr;
      }

      .thumb-btn {
        width: 68px;
        height: 68px;
      }
    }
  </style>
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
        <a href="login.php" class="btn btn-secondary btn-sm">👤 Fuji</a>
      </div>

      <button class="menu-toggle" aria-label="Open menu" aria-expanded="false">☰</button>
    </div>
  </header>

  <main class="product-page">
    <div class="container">
      <nav class="breadcrumb" aria-label="Breadcrumb">
        <a href="menu.php">Menu</a>
        <span>›</span>
        <span id="breadcrumbProductName">Signature Cat Latte</span>
      </nav>

      <div class="product-layout">
        <section class="product-gallery">
          <div class="product-main-image-wrap">
            <div class="product-badge-wrap" id="productBadgeWrap"></div>
            <img
              id="mainProductImage"
              class="product-main-image"
              src="https://images.unsplash.com/photo-1509042239860-f550ce710b93?auto=format&fit=crop&w=1200&q=80"
              alt="Signature Cat Latte"
            >
          </div>

          <div class="thumb-row" id="thumbnailGallery">
            <button class="thumb-btn active" type="button" data-image="https://images.unsplash.com/photo-1509042239860-f550ce710b93?auto=format&fit=crop&w=1200&q=80">
              <img src="https://images.unsplash.com/photo-1509042239860-f550ce710b93?auto=format&fit=crop&w=300&q=80" alt="Thumbnail 1">
            </button>
            <button class="thumb-btn" type="button" data-image="https://images.unsplash.com/photo-1495474472287-4d71bcdd2085?auto=format&fit=crop&w=1200&q=80">
              <img src="https://images.unsplash.com/photo-1495474472287-4d71bcdd2085?auto=format&fit=crop&w=300&q=80" alt="Thumbnail 2">
            </button>
            <button class="thumb-btn" type="button" data-image="https://images.unsplash.com/photo-1517701550927-30cf4ba1f59d?auto=format&fit=crop&w=1200&q=80">
              <img src="https://images.unsplash.com/photo-1517701550927-30cf4ba1f59d?auto=format&fit=crop&w=300&q=80" alt="Thumbnail 3">
            </button>
          </div>
        </section>

        <section class="product-info-card">
          <div class="product-header-meta">
            <span class="product-category" id="productCategory">Hot Drinks</span>
            <span class="badge badge-soft">Popular</span>
          </div>

          <h1 class="product-big-title" id="productName">Signature Cat Latte</h1>

          <p class="section-text" id="productDescription">
            Rich espresso with velvety steamed milk, topped with adorable cat latte art.
            Made with premium locally sourced coffee beans, this is our bestselling drink
            that keeps customers coming back every day.
          </p>

          <div class="price-row">
            <span class="price" id="displayPrice">₱85.00</span>
            <span class="old-price" id="oldPrice">₱95.00</span>
            <span class="badge badge-accent" id="promoBadge">10% OFF</span>
          </div>

          <div class="option-block">
            <label class="option-label">Size</label>
            <div class="size-options" id="sizeOptions">
              <button class="size-btn active" type="button" data-size="Small" data-price="85">
                <strong>Small</strong>
                <span>₱85</span>
              </button>
              <button class="size-btn" type="button" data-size="Regular" data-price="95">
                <strong>Regular</strong>
                <span>₱95</span>
              </button>
              <button class="size-btn" type="button" data-size="Large" data-price="110">
                <strong>Large</strong>
                <span>₱110</span>
              </button>
            </div>
          </div>

          <div class="option-block">
            <label class="option-label">Add-ons <span style="font-weight:400;">(optional)</span></label>
            <div class="addon-grid">
              <label class="addon-item">
                <input type="checkbox" class="addon-check" data-name="Extra Shot" data-price="20">
                <span><strong>Extra Shot</strong> ₱20</span>
              </label>
              <label class="addon-item">
                <input type="checkbox" class="addon-check" data-name="Oat Milk" data-price="15">
                <span><strong>Oat Milk </strong>₱15</span>
              </label>
              <label class="addon-item">
                <input type="checkbox" class="addon-check" data-name="Vanilla Syrup" data-price="10">
                <span><strong>Vanilla Syrup</strong> ₱10</span>
              </label>
              <label class="addon-item">
                <input type="checkbox" class="addon-check" data-name="Caramel Drizzle" data-price="10">
                <span><strong>Caramel Drizzle</strong> ₱10</span>
              </label>
            </div>

            <div class="qty-actions">
              <div class="qty-selector" aria-label="Quantity selector">
                <button type="button" id="decreaseQty">−</button>
                <span id="quantityValue">1</span>
                <button type="button" id="increaseQty">+</button>
              </div>
              <div class="section-text">Total updates automatically as you customize.</div>
            </div>
          </div>


          <div class="option-block">
            <label class="option-label" for="specialInstructions">Special Instructions <span style="font-weight:400;">(optional)</span></label>
            <textarea
              id="specialInstructions"
              class="special-instructions-box"
              maxlength="180"
              placeholder="Example: less ice, no whipped cream, extra napkins, allergy note"
            ></textarea>
          </div>

          <div class="action-stack">
            <button class="btn btn-primary btn-full" id="addToCartProductBtn">🛒 Add to Cart</button>
            <button class="btn btn-accent btn-full" id="buyNowBtn">⚡ Buy Now</button>
          </div>

          <div class="product-notes">
            <span>⏱️ Ready in 10–15 minutes</span>
            <span>🚚 Available for delivery & pick-up</span>
            <span>🌿 Locally sourced ingredients</span>
          </div>

          <div class="mini-footer-card">
            <div class="mini-footer-brand">
              <span class="brand-mark">CAFE</span>
              <strong class="brand-name">FurrfectCafe</strong>
            </div>
            <p>
              A cozy cat café in Legazpi City, Albay. Order online and enjoy your favorites at home.
            </p>
            <div class="socials">
              <a href="#" aria-label="Facebook">f</a>
              <a href="#" aria-label="Instagram">◎</a>
              <a href="#" aria-label="TikTok">♪</a>
            </div>
          </div>
        </section>
      </div>
    </div>
  </main>

  <script src="script.js"></script>
  <script>
    document.addEventListener("DOMContentLoaded", () => {
      const productId = Number(new URLSearchParams(window.location.search).get("id")) || 1;
      const allProducts = (window.FurrfectCafe && Array.isArray(FurrfectCafe.products))
        ? FurrfectCafe.products
        : [];

      const fallbackProduct = {
        id: 1,
        name: "Signature Cat Latte",
        categoryLabel: "Hot Drinks",
        description: "Rich espresso with velvety steamed milk, topped with adorable latte art. Made with premium locally sourced coffee beans.",
        price: 85,
        badge: "10% OFF",
        image: "https://images.unsplash.com/photo-1509042239860-f550ce710b93?auto=format&fit=crop&w=1200&q=80"
      };

      const product = allProducts.find(item => item.id === productId) || fallbackProduct;

      const galleryImages = {
        1: [
          "https://images.unsplash.com/photo-1509042239860-f550ce710b93?auto=format&fit=crop&w=1200&q=80",
          "https://images.unsplash.com/photo-1495474472287-4d71bcdd2085?auto=format&fit=crop&w=1200&q=80",
          "https://images.unsplash.com/photo-1517701550927-30cf4ba1f59d?auto=format&fit=crop&w=1200&q=80"
        ],
        2: [
          "https://images.unsplash.com/photo-1517705008128-361805f42e86?auto=format&fit=crop&w=1200&q=80",
          "https://images.unsplash.com/photo-1461023058943-07fcbe16d735?auto=format&fit=crop&w=1200&q=80",
          "https://images.unsplash.com/photo-1495474472287-4d71bcdd2085?auto=format&fit=crop&w=1200&q=80"
        ],
        3: [
          "https://images.unsplash.com/photo-1563729784474-d77dbb933a9e?auto=format&fit=crop&w=1200&q=80",
          "https://images.unsplash.com/photo-1488477181946-6428a0291777?auto=format&fit=crop&w=1200&q=80",
          "https://images.unsplash.com/photo-1551024601-bec78aea704b?auto=format&fit=crop&w=1200&q=80"
        ]
      };

      const images = galleryImages[product.id] || [product.image, product.image, product.image];

      const mainImage = document.getElementById("mainProductImage");
      const thumbnailGallery = document.getElementById("thumbnailGallery");
      const productName = document.getElementById("productName");
      const breadcrumbProductName = document.getElementById("breadcrumbProductName");
      const productCategory = document.getElementById("productCategory");
      const productDescription = document.getElementById("productDescription");
      const displayPrice = document.getElementById("displayPrice");
      const oldPrice = document.getElementById("oldPrice");
      const promoBadge = document.getElementById("promoBadge");
      const productBadgeWrap = document.getElementById("productBadgeWrap");
      const quantityValue = document.getElementById("quantityValue");
      const specialInstructions = document.getElementById("specialInstructions");

      let quantity = 1;
      let selectedSize = { label: "Small", price: Number(product.price) };
      const selectedAddons = new Map();

      productName.textContent = product.name;
      breadcrumbProductName.textContent = product.name;
      productCategory.textContent = product.categoryLabel;
      productDescription.textContent = product.description;
      mainImage.src = images[0];
      mainImage.alt = product.name;

      if (product.badge) {
        promoBadge.textContent = product.badge;
        productBadgeWrap.innerHTML = `<span class="badge badge-accent">${product.badge}</span>`;
      } else {
        promoBadge.style.display = "none";
        oldPrice.style.display = "none";
      }

      oldPrice.textContent = `₱${(Number(product.price) + 10).toFixed(2)}`;

      thumbnailGallery.innerHTML = images.map((image, index) => `
        <button class="thumb-btn ${index === 0 ? "active" : ""}" type="button" data-image="${image}">
          <img src="${image}" alt="${product.name} thumbnail ${index + 1}">
        </button>
      `).join("");

      thumbnailGallery.addEventListener("click", (event) => {
        const btn = event.target.closest(".thumb-btn");
        if (!btn) return;

        mainImage.src = btn.dataset.image;
        thumbnailGallery.querySelectorAll(".thumb-btn").forEach(item => item.classList.remove("active"));
        btn.classList.add("active");
      });

      const sizeButtons = document.querySelectorAll(".size-btn");
      const addonChecks = document.querySelectorAll(".addon-check");

      function formatPeso(value) {
        return `₱${Number(value).toFixed(2)}`;
      }

      function getAddonTotal() {
        return Array.from(selectedAddons.values()).reduce((sum, value) => sum + Number(value), 0);
      }

      function getUnitPrice() {
        return Number(selectedSize.price) + getAddonTotal();
      }

      function getTotalPrice() {
        return getUnitPrice() * quantity;
      }

      function updatePriceDisplay() {
        displayPrice.textContent = formatPeso(getTotalPrice());
        quantityValue.textContent = quantity;
      }

      function getCart() {
        try {
          return JSON.parse(localStorage.getItem("furrfectcafe_cart") || "[]");
        } catch (error) {
          return [];
        }
      }

      function saveCart(cart) {
        localStorage.setItem("furrfectcafe_cart", JSON.stringify(cart));

        if (window.FurrfectCafe && typeof FurrfectCafe.updateCartCountUI === "function") {
          FurrfectCafe.updateCartCountUI();
        }
      }

      function makeCartKey(addons, instructions) {
        const addonKey = addons.map(addon => addon.name).sort().join("|");
        return `${product.id}-${selectedSize.label}-${addonKey}-${instructions.trim().toLowerCase()}`;
      }

      function addCurrentProductToCart() {
        const addons = Array.from(selectedAddons.entries()).map(([name, price]) => ({
          name,
          price: Number(price)
        }));

        const instructions = specialInstructions ? specialInstructions.value.trim() : "";
        const cartKey = makeCartKey(addons, instructions);
        const cart = getCart();
        const existing = cart.find(item => item.cartKey === cartKey);

        if (existing) {
          existing.quantity += quantity;
        } else {
          cart.push({
            cartKey,
            productId: product.id,
            quantity,
            name: product.name,
            price: getUnitPrice(),
            image: product.image,
            categoryLabel: product.categoryLabel,
            selectedSize: selectedSize.label,
            selectedAddons: addons,
            specialInstructions: instructions
          });
        }

        saveCart(cart);
      }

      sizeButtons.forEach(button => {
        if (Number(button.dataset.price) === Number(product.price)) {
          sizeButtons.forEach(btn => btn.classList.remove("active"));
          button.classList.add("active");
          selectedSize = {
            label: button.dataset.size,
            price: Number(button.dataset.price)
          };
        }

        button.addEventListener("click", () => {
          sizeButtons.forEach(btn => btn.classList.remove("active"));
          button.classList.add("active");

          selectedSize = {
            label: button.dataset.size,
            price: Number(button.dataset.price)
          };

          updatePriceDisplay();
        });
      });

      addonChecks.forEach(check => {
        check.addEventListener("change", () => {
          const addonName = check.dataset.name;
          const addonPrice = Number(check.dataset.price);

          if (check.checked) {
            selectedAddons.set(addonName, addonPrice);
          } else {
            selectedAddons.delete(addonName);
          }

          updatePriceDisplay();
        });
      });

      document.getElementById("increaseQty").addEventListener("click", () => {
        quantity += 1;
        updatePriceDisplay();
      });

      document.getElementById("decreaseQty").addEventListener("click", () => {
        quantity = Math.max(1, quantity - 1);
        updatePriceDisplay();
      });

      document.getElementById("addToCartProductBtn").addEventListener("click", () => {
        addCurrentProductToCart();
      });

      document.getElementById("buyNowBtn").addEventListener("click", () => {
        addCurrentProductToCart();
        window.location.href = "cart.php";
      });

      updatePriceDisplay();
    });
  </script>
</body>
</html>