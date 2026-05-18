<?php
require_once "includes/auth.php";
require_once "includes/db.php";

function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function peso($value) {
    return "₱" . number_format((float)$value, 2);
}

function product_image($path) {
    if (!$path) {
        return "https://images.unsplash.com/photo-1495474472287-4d71bcdd2085?auto=format&fit=crop&w=1200&q=80";
    }

    return $path;
}

$productId = isset($_GET["id"]) ? (int)$_GET["id"] : 0;

$productStmt = $conn->prepare("
    SELECT
        p.product_id,
        p.product_name,
        p.description,
        p.price,
        p.image_path,
        p.promo_badge,
        p.is_available,
        c.category_name,
        c.category_slug
    FROM products p
    INNER JOIN categories c ON p.category_id = c.category_id
    WHERE p.product_id = :product_id
    LIMIT 1
");
$productStmt->execute([":product_id" => $productId]);
$product = $productStmt->fetch();

if (!$product || (int)$product["is_available"] !== 1) {
    http_response_code(404);
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <title>Product Not Found | FurrfectCafe</title>
      <link rel="stylesheet" href="style.css">
    </head>
    <body>
      <main class="section">
        <div class="container">
          <div class="empty-state">
            <h1>Product not found</h1>
            <p class="section-text">This product is unavailable or may have been removed.</p>
            <a href="menu.php" class="btn btn-primary mt-16">Back to Menu</a>
          </div>
        </div>
      </main>
    </body>
    </html>
    <?php
    exit;
}

$sizeStmt = $conn->prepare("
    SELECT size_id, size_name, price_modifier, is_default
    FROM product_sizes
    WHERE product_id = :product_id
    AND is_available = 1
    ORDER BY display_order ASC, size_id ASC
");
$sizeStmt->execute([":product_id" => $productId]);
$sizes = $sizeStmt->fetchAll();

if (!$sizes) {
    $sizes = [
        [
            "size_id" => 0,
            "size_name" => "Regular",
            "price_modifier" => 0,
            "is_default" => 1
        ]
    ];
}

$addonStmt = $conn->prepare("
    SELECT addon_id, addon_name, addon_price
    FROM product_addons
    WHERE product_id = :product_id
    AND is_available = 1
    ORDER BY display_order ASC, addon_id ASC
");
$addonStmt->execute([":product_id" => $productId]);
$addons = $addonStmt->fetchAll();

$basePrice = (float)$product["price"];
$imagePath = product_image($product["image_path"]);
$defaultSizeIndex = 0;

foreach ($sizes as $index => $size) {
    if ((int)$size["is_default"] === 1) {
        $defaultSizeIndex = $index;
        break;
    }
}

$defaultUnitPrice = $basePrice + (float)$sizes[$defaultSizeIndex]["price_modifier"];
$productForJs = [
    "id" => (int)$product["product_id"],
    "name" => $product["product_name"],
    "categoryLabel" => $product["category_name"],
    "description" => $product["description"] ?? "",
    "basePrice" => $basePrice,
    "image" => $imagePath,
    "badge" => $product["promo_badge"] ?? ""
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo e($product["product_name"]); ?> | FurrfectCafe</title>
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
      border: 1px solid var(--border);
      border-radius: 16px;
      background: var(--bg-soft);
      padding: 12px;
      display: grid;
      gap: 3px;
      text-align: left;
      transition: var(--transition);
    }

    .size-btn strong {
      font-family: Arial, Helvetica, sans-serif;
    }

    .size-btn span {
      font-family: Arial, Helvetica, sans-serif;
      color: var(--text-muted);
      font-size: 0.9rem;
    }

    .size-btn.active,
    .size-btn:hover {
      border-color: var(--accent);
      background: #fff8ef;
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
      border: 1px solid var(--border);
      border-radius: 16px;
      background: var(--bg-soft);
      padding: 12px;
      font-family: Arial, Helvetica, sans-serif;
    }

    .qty-actions {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 16px;
      margin-top: 18px;
      flex-wrap: wrap;
    }

    .qty-selector {
      display: inline-flex;
      align-items: center;
      gap: 10px;
      border: 1px solid var(--border);
      background: var(--bg-soft);
      border-radius: 999px;
      padding: 6px;
      font-family: Arial, Helvetica, sans-serif;
      font-weight: 700;
    }

    .qty-selector button {
      width: 38px;
      height: 38px;
      border-radius: 50%;
      background: var(--surface);
      color: var(--primary);
      font-size: 1.2rem;
    }

    .qty-selector span {
      min-width: 28px;
      text-align: center;
    }

    .action-stack {
      display: grid;
      gap: 12px;
      margin-top: 22px;
    }

    .product-notes {
      display: grid;
      gap: 10px;
      margin-top: 20px;
      font-family: Arial, Helvetica, sans-serif;
      color: var(--text-muted);
      font-size: 0.95rem;
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
        <button type="button" class="btn btn-secondary btn-sm" data-customer-logout>Logout</button>
      </div>

      <button class="menu-toggle" aria-label="Open menu" aria-expanded="false">☰</button>
    </div>
  </header>

  <main class="product-page">
    <div class="container">
      <nav class="breadcrumb" aria-label="Breadcrumb">
        <a href="menu.php">Menu</a>
        <span>›</span>
        <span><?php echo e($product["product_name"]); ?></span>
      </nav>

      <div class="product-layout">
        <section class="product-gallery">
          <div class="product-main-image-wrap">
            <div class="product-badge-wrap">
              <?php if (!empty($product["promo_badge"])): ?>
                <span class="badge badge-accent"><?php echo e($product["promo_badge"]); ?></span>
              <?php endif; ?>
            </div>
            <img
              id="mainProductImage"
              class="product-main-image"
              src="<?php echo e($imagePath); ?>"
              alt="<?php echo e($product["product_name"]); ?>"
              onerror="this.src='https://images.unsplash.com/photo-1495474472287-4d71bcdd2085?auto=format&fit=crop&w=1200&q=80';"
            >
          </div>

          <div class="thumb-row" id="thumbnailGallery">
            <button class="thumb-btn active" type="button" data-image="<?php echo e($imagePath); ?>">
              <img
                src="<?php echo e($imagePath); ?>"
                alt="<?php echo e($product["product_name"]); ?> thumbnail"
                onerror="this.src='https://images.unsplash.com/photo-1495474472287-4d71bcdd2085?auto=format&fit=crop&w=300&q=80';">
            </button>
          </div>
        </section>

        <section class="product-info-card">
          <div class="product-header-meta">
            <span class="product-category"><?php echo e($product["category_name"]); ?></span>
            <?php if ((int)$product["is_available"] === 1): ?>
              <span class="badge badge-soft">Available</span>
            <?php endif; ?>
          </div>

          <h1 class="product-big-title"><?php echo e($product["product_name"]); ?></h1>

          <p class="section-text">
            <?php echo e($product["description"] ?? "Freshly prepared FurrfectCafe favorite."); ?>
          </p>

          <div class="price-row">
            <span class="price" id="displayPrice"><?php echo peso($defaultUnitPrice); ?></span>
            <?php if (!empty($product["promo_badge"])): ?>
              <span class="old-price"><?php echo peso($defaultUnitPrice + 10); ?></span>
              <span class="badge badge-accent"><?php echo e($product["promo_badge"]); ?></span>
            <?php endif; ?>
          </div>

          <div class="option-block">
            <label class="option-label">Size</label>
            <div class="size-options" id="sizeOptions">
              <?php foreach ($sizes as $index => $size): ?>
                <?php $unitPrice = $basePrice + (float)$size["price_modifier"]; ?>
                <button
                  class="size-btn <?php echo $index === $defaultSizeIndex ? 'active' : ''; ?>"
                  type="button"
                  data-size="<?php echo e($size["size_name"]); ?>"
                  data-price="<?php echo e($unitPrice); ?>">
                  <strong><?php echo e($size["size_name"]); ?></strong>
                  <span><?php echo peso($unitPrice); ?></span>
                </button>
              <?php endforeach; ?>
            </div>
          </div>

          <?php if ($addons): ?>
            <div class="option-block">
              <label class="option-label">Add-ons <span style="font-weight:400;">(optional)</span></label>
              <div class="addon-grid">
                <?php foreach ($addons as $addon): ?>
                  <label class="addon-item">
                    <input
                      type="checkbox"
                      class="addon-check"
                      data-name="<?php echo e($addon["addon_name"]); ?>"
                      data-price="<?php echo e($addon["addon_price"]); ?>">
                    <span>
                      <strong><?php echo e($addon["addon_name"]); ?></strong>
                      <?php echo peso($addon["addon_price"]); ?>
                    </span>
                  </label>
                <?php endforeach; ?>
              </div>
            </div>
          <?php endif; ?>

          <div class="option-block">
            <label class="option-label">Quantity</label>
            <div class="qty-actions">
              <div class="qty-selector" aria-label="Quantity selector">
                <button type="button" id="decreaseQty">−</button>
                <span id="quantityValue">1</span>
                <button type="button" id="increaseQty">+</button>
              </div>
              <div class="section-text">Total updates automatically as you customize.</div>
            </div>
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
              <span class="brand-mark">FC</span>
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

  <script>
    window.FURRFECT_DB_PRODUCT = <?php echo json_encode($productForJs, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT); ?>;
  </script>
  <script src="script.js"></script>
  <script>
    document.addEventListener("DOMContentLoaded", () => {
      const product = window.FURRFECT_DB_PRODUCT;
      const mainImage = document.getElementById("mainProductImage");
      const thumbnailGallery = document.getElementById("thumbnailGallery");
      const quantityValue = document.getElementById("quantityValue");
      const displayPrice = document.getElementById("displayPrice");
      const sizeButtons = document.querySelectorAll(".size-btn");
      const addonChecks = document.querySelectorAll(".addon-check");

      let quantity = 1;
      let selectedSize = {
        label: sizeButtons[0]?.dataset.size || "Regular",
        price: Number(sizeButtons[0]?.dataset.price || product.basePrice)
      };
      const selectedAddons = new Map();

      function formatPeso(value) {
        return `₱${Number(value).toFixed(2)}`;
      }

      function getAddonTotal() {
        return Array.from(selectedAddons.values()).reduce((sum, value) => sum + value, 0);
      }

      function getUnitPrice() {
        return selectedSize.price + getAddonTotal();
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

      function addCurrentProductToCart() {
        const addons = Array.from(selectedAddons.entries()).map(([name, price]) => ({
          name,
          price
        }));

        const cart = getCart();
        const cartKey = `${product.id}-${selectedSize.label}-${addons.map(addon => addon.name).join("|")}`;

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
            selectedAddons: addons
          });
        }

        saveCart(cart);
        alert(`${product.name} added to cart.`);
      }

      thumbnailGallery?.addEventListener("click", event => {
        const btn = event.target.closest(".thumb-btn");
        if (!btn) return;

        mainImage.src = btn.dataset.image;
        thumbnailGallery.querySelectorAll(".thumb-btn").forEach(item => item.classList.remove("active"));
        btn.classList.add("active");
      });

      sizeButtons.forEach(button => {
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
