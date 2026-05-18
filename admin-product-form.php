<?php require_once "includes/admin-auth.php"; ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>FurrfectCafe Admin | Add / Edit Product</title>
  <link rel="stylesheet" href="style.css">
  <style>
    .form-shell {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 18px;
      align-items: start;
    }

    .stack {
      display: grid;
      gap: 18px;
    }

    .upload-box {
      min-height: 230px;
      border: 2px dashed #dcc7af;
      border-radius: 22px;
      background: #faf4ec;
      display: grid;
      place-items: center;
      text-align: center;
      padding: 20px;
      font-family: Arial, Helvetica, sans-serif;
      color: var(--text-muted);
    }

    .hint {
      display: block;
      margin-top: 6px;
      font-size: 0.88rem;
    }

    .action-footer {
      display: grid;
      gap: 12px;
      margin-top: 8px;
    }

    @media (max-width: 980px) {
      .form-shell {
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
        <a href="admin-orders.php">Orders <span style="float:right; opacity:0.8;">5</span></a>
      </nav>

      <div class="eyebrow" style="color: rgba(255,255,255,0.45); margin-top: 20px;">Catalog</div>
      <nav class="admin-nav">
        <a href="admin-products.php">Products</a>
        <a href="admin-product-form.php" class="active">+ Add / Edit Product</a>
      </nav>

      <div style="margin-top:auto; padding-top:22px; border-top:1px solid rgba(255,255,255,0.08);">
        <div style="display:flex; align-items:center; gap:12px;">
          <div class="brand-mark" style="width:42px;height:42px;background:rgba(255,255,255,0.12); color:#fff; border:none;">A</div>
          <div>
            <strong style="display:block;">Admin</strong>
            <span style="font-family:Arial, Helvetica, sans-serif; color:rgba(255,255,255,0.65); font-size:0.9rem;">Administrator</span>
          </div>
        </div>
      </div>
    </aside>

    <main class="admin-content">
      <div class="admin-topbar">
        <div>
          <div class="eyebrow" style="margin-bottom:6px;">Products</div>
          <h1 class="admin-page-title">☰ Add / Edit Product</h1>
        </div>
        <a href="admin-products.php" class="btn btn-secondary btn-sm">← Back to Products</a>
      </div>

      <form id="productForm" class="form-shell" novalidate>
        <div class="stack">
          <section class="form-card">
            <h2 class="form-title">📦 Product Information</h2>

            <div class="form-grid">
              <div class="field">
                <label for="productName">Product Name *</label>
                <input class="input" type="text" id="productName" value="Signature Cat Latte" required>
              </div>

              <div class="field">
                <label for="productDescription">Description</label>
                <textarea class="textarea" id="productDescription" placeholder="Short description shown on the menu card.">Rich espresso with velvety steamed milk and adorable cat latte art.</textarea>
              </div>

              <div class="form-grid form-grid-2">
                <div class="field">
                  <label for="productPrice">Price (₱) *</label>
                  <input class="input" type="number" id="productPrice" value="85" required>
                </div>

                <div class="field">
                  <label for="productCategory">Category *</label>
                  <select class="select" id="productCategory" required>
                    <option>Hot Drinks</option>
                    <option>Cold Drinks</option>
                    <option>Pastries</option>
                    <option>All-Day Bites</option>
                    <option>Fruit Blends</option>
                  </select>
                </div>
              </div>
            </div>
          </section>

          <section class="form-card">
            <h2 class="form-title">⚙ Customization Options <span style="font-weight:400; color:var(--text-muted); font-size:0.95rem;">(optional)</span></h2>

            <div class="form-grid">
              <div class="field">
                <label for="sizeOptions">Size Options</label>
                <input class="input" type="text" id="sizeOptions" value="Small, Regular, Large">
                <div class="form-help">Comma-separated. Leave blank if no size options.</div>
              </div>

              <div class="field">
                <label for="addOns">Add-ons</label>
                <textarea class="textarea" id="addOns">Extra Shot +20, Oat Milk +15, Vanilla Syrup +10, Caramel Drizzle +10</textarea>
                <div class="form-help">One per line or comma-separated. Format: Name +Price</div>
              </div>
            </div>
          </section>
        </div>

        <div class="stack">
          <section class="form-card">
            <h2 class="form-title">🖼 Product Image</h2>
            <div class="upload-box">
              <div>
                <strong>Drop image here or click to browse</strong>
                <span class="hint">PNG, JPG, WEBP up to 5MB • Recommended 800×600</span>
              </div>
            </div>
          </section>

          <section class="form-card">
            <h2 class="form-title">🏷 Visibility & Badge</h2>

            <div class="form-grid">
              <div class="field">
                <label for="productBadge">Promo Badge</label>
                <input class="input" type="text" id="productBadge" value="10% OFF" placeholder="e.g. New, 10% OFF, Buy 2 Get 1">
              </div>

              <div class="field">
                <label for="productAvailability">Availability</label>
                <select class="select" id="productAvailability">
                  <option>Available</option>
                  <option>Hidden</option>
                </select>
              </div>
            </div>

            <div class="action-footer">
              <button type="submit" class="btn btn-primary btn-full">💾 Save Product</button>
              <a href="admin-products.php" class="btn btn-secondary btn-full">Cancel</a>
            </div>
          </section>
        </div>
      </form>
    </main>
  </div>

  <script src="script.js"></script>
<script>
  document.addEventListener("DOMContentLoaded", () => {
    const form = document.getElementById("productForm");

    const productName = document.getElementById("productName");
    const productDescription = document.getElementById("productDescription");
    const productPrice = document.getElementById("productPrice");
    const productCategory = document.getElementById("productCategory");
    const productBadge = document.getElementById("productBadge");
    const productAvailability = document.getElementById("productAvailability");
    const sizeOptions = document.getElementById("sizeOptions");
    const addOns = document.getElementById("addOns");

    const urlParams = new URLSearchParams(window.location.search);
    const editId = urlParams.get("id");

    function getAdminProducts() {
      const storedProducts = JSON.parse(localStorage.getItem("furrfectcafe_admin_products") || "[]");

      if (storedProducts.length) {
        return storedProducts;
      }

      const defaultProducts = [
        {
          id: 1,
          name: "Signature Cat Latte",
          description: "Rich espresso with velvety steamed milk and adorable cat latte art.",
          price: 85,
          category: "Hot Drinks",
          badge: "10% OFF",
          availability: "Available",
          sizeOptions: "Small, Regular, Large",
          addOns: "Extra Shot +20, Oat Milk +15, Vanilla Syrup +10, Caramel Drizzle +10",
          image: "https://images.unsplash.com/photo-1509042239860-f550ce710b93?auto=format&fit=crop&w=900&q=80",
          orders: 142
        },
        {
          id: 2,
          name: "Matcha Cloud",
          description: "Premium matcha blended with oat milk and soft cream cloud topping.",
          price: 110,
          category: "Cold Drinks",
          badge: "",
          availability: "Available",
          sizeOptions: "Small, Regular, Large",
          addOns: "Oat Milk +15, Cream Cloud +20",
          image: "https://images.unsplash.com/photo-1515823064-d6e0c04616a7?auto=format&fit=crop&w=900&q=80",
          orders: 98
        },
        {
          id: 3,
          name: "Matcha Cheesecake",
          description: "Creamy New York style cheesecake with a dreamy matcha finish.",
          price: 120,
          category: "Pastries",
          badge: "Buy 2 Get 1",
          availability: "Available",
          sizeOptions: "",
          addOns: "",
          image: "https://images.unsplash.com/photo-1563729784474-d77dbb933a9e?auto=format&fit=crop&w=900&q=80",
          orders: 76
        }
      ];

      localStorage.setItem("furrfectcafe_admin_products", JSON.stringify(defaultProducts));
      return defaultProducts;
    }

    function saveAdminProducts(products) {
      localStorage.setItem("furrfectcafe_admin_products", JSON.stringify(products));
    }

    function fillEditForm() {
      if (!editId) return;

      const products = getAdminProducts();
      const product = products.find(item => String(item.id) === String(editId));

      if (!product) {
        alert("Product not found. You can create a new product instead.");
        return;
      }

      productName.value = product.name || "";
      productDescription.value = product.description || "";
      productPrice.value = product.price || "";
      productCategory.value = product.category || "Hot Drinks";
      productBadge.value = product.badge || "";
      productAvailability.value = product.availability || "Available";
      sizeOptions.value = product.sizeOptions || "";
      addOns.value = product.addOns || "";
    }

    form.addEventListener("submit", event => {
      event.preventDefault();

      const name = productName.value.trim();
      const description = productDescription.value.trim();
      const price = Number(productPrice.value);
      const category = productCategory.value.trim();
      const badge = productBadge.value.trim();
      const availability = productAvailability.value.trim();

      if (!name) {
        alert("Please enter a product name.");
        return;
      }

      if (!price || price <= 0) {
        alert("Please enter a valid product price.");
        return;
      }

      if (!category) {
        alert("Please select a category.");
        return;
      }

      const products = getAdminProducts();

      if (editId) {
        const index = products.findIndex(item => String(item.id) === String(editId));

        if (index !== -1) {
          products[index] = {
            ...products[index],
            name,
            description,
            price,
            category,
            badge,
            availability,
            sizeOptions: sizeOptions.value.trim(),
            addOns: addOns.value.trim()
          };

          saveAdminProducts(products);
          alert("Product updated successfully.");
          window.location.href = "admin-products.php";
          return;
        }
      }

      const newProduct = {
        id: Date.now(),
        name,
        description,
        price,
        category,
        badge,
        availability,
        sizeOptions: sizeOptions.value.trim(),
        addOns: addOns.value.trim(),
        image: "https://images.unsplash.com/photo-1495474472287-4d71bcdd2085?auto=format&fit=crop&w=900&q=80",
        orders: 0
      };

      products.unshift(newProduct);
      saveAdminProducts(products);

      alert("Product added successfully.");
      window.location.href = "admin-products.php";
    });

    fillEditForm();
  });

  </script>
</body>
</html>