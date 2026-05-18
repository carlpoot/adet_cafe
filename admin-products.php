<?php require_once "includes/admin-auth.php"; ?>
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
      width: 46px;
      height: 46px;
      border-radius: 12px;
      object-fit: cover;
      flex-shrink: 0;
    }

    .product-cell small {
      display: block;
      color: var(--text-muted);
      font-family: Arial, Helvetica, sans-serif;
      margin-top: 3px;
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
    }

    .mini-btn.edit {
      color: var(--accent);
    }

    .mini-btn.delete {
      color: white;
      background: #d25454;
      border-color: #d25454;
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
        <a href="admin-orders.php">Orders <span style="float:right; opacity:0.8;">5</span></a>
      </nav>

      <div class="eyebrow" style="color: rgba(255,255,255,0.45); margin-top: 20px;">Catalog</div>
      <nav class="admin-nav">
        <a href="admin-products.php" class="active">Products</a>
        <a href="admin-product-form.php">+ Add / Edit Product</a>
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
        <h1 class="admin-page-title">☰ Products</h1>
        <div class="admin-toolbar-right">
          <a href="admin-product-form.php" class="btn btn-primary btn-sm">＋ Add New Product</a>
        </div>
      </div>

      <section class="products-shell">
        <div class="products-toolbar">
          <input type="text" class="admin-search" id="productSearch" placeholder="Search product...">

          <select class="admin-select" id="categoryFilter">
            <option value="all">All Categories</option>
            <option value="Hot Drinks">Hot Drinks</option>
            <option value="Cold Drinks">Cold Drinks</option>
            <option value="Pastries">Pastries</option>
            <option value="All-Day Bites">All-Day Bites</option>
            <option value="Fruit Blends">Fruit Blends</option>
          </select>

          <select class="admin-select" id="availabilityFilter">
            <option value="all">All Status</option>
            <option value="Available">Available</option>
            <option value="Hidden">Hidden</option>
          </select>
        </div>

        <div class="panel-header">
          <h2 class="form-title" style="margin:0;">All Products (12)</h2>
          <span style="font-family:Arial, Helvetica, sans-serif; color:var(--text-muted);">Last updated today</span>
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
            <tbody id="productsTableBody"></tbody>
          </table>
        </div>
      </section>
    </main>
  </div>

  <script src="script.js"></script>
  <script>
        document.addEventListener("DOMContentLoaded", () => {
          const mockProducts = [
        {
          image: "https://images.unsplash.com/photo-1509042239860-f550ce710b93?auto=format&fit=crop&w=300&q=80",
          name: "Signature Cat Latte",
          category: "Hot Drinks",
          price: 85,
          badge: "10% OFF",
          availability: "Available",
          orders: 142
        },
        {
          image: "https://images.unsplash.com/photo-1517705008128-361805f42e86?auto=format&fit=crop&w=300&q=80",
          name: "Matcha Cloud",
          category: "Cold Drinks",
          price: 110,
          badge: "—",
          availability: "Available",
          orders: 98
        },
        {
          image: "https://images.unsplash.com/photo-1563729784474-d77dbb933a9e?auto=format&fit=crop&w=300&q=80",
          name: "Matcha Cheesecake",
          category: "Pastries",
          price: 120,
          badge: "Buy 2 Get 1",
          availability: "Available",
          orders: 76
        },
        {
          image: "https://images.unsplash.com/photo-1576618148400-f54bed99fcfd?auto=format&fit=crop&w=300&q=80",
          name: "Paw Print Cupcake",
          category: "Pastries",
          price: 65,
          badge: "—",
          availability: "Available",
          orders: 65
        },
        {
          image: "https://images.unsplash.com/photo-1558857563-b371033873b8?auto=format&fit=crop&w=300&q=80",
          name: "Brown Sugar Milk Tea",
          category: "Cold Drinks",
          price: 95,
          badge: "—",
          availability: "Available",
          orders: 89
        },
        {
          image: "https://images.unsplash.com/photo-1568901346375-23c9450c58cd?auto=format&fit=crop&w=300&q=80",
          name: "Cafe Club Sandwich",
          category: "All-Day Bites",
          price: 155,
          badge: "New",
          availability: "Available",
          orders: 34
        },
        {
          image: "https://images.unsplash.com/photo-1546173159-315724a31696?auto=format&fit=crop&w=300&q=80",
          name: "Mango Passion Cooler",
          category: "Fruit Blends",
          price: 90,
          badge: "—",
          availability: "Hidden",
          orders: 22
        },
        {
          image: "https://images.unsplash.com/photo-1495474472287-4d71bcdd2085?auto=format&fit=crop&w=300&q=80",
          name: "Caramel Macchiato",
          category: "Hot Drinks",
          price: 105,
          badge: "—",
          availability: "Available",
          orders: 57
        }
      ];

      const tableBody = document.getElementById("productsTableBody");
      const productSearch = document.getElementById("productSearch");
      const categoryFilter = document.getElementById("categoryFilter");
      const availabilityFilter = document.getElementById("availabilityFilter");

      function renderProducts() {
        const searchValue = productSearch.value.trim().toLowerCase();
        const categoryValue = categoryFilter.value;
        const availabilityValue = availabilityFilter.value;

        const filtered = mockProducts.filter(product => {
          const matchesSearch = product.name.toLowerCase().includes(searchValue);
          const matchesCategory = categoryValue === "all" || product.category === categoryValue;
          const matchesAvailability = availabilityValue === "all" || product.availability === availabilityValue;
          return matchesSearch && matchesCategory && matchesAvailability;
        });

        tableBody.innerHTML = filtered.map(product => `
          <tr>
            <td><img src="${product.image}" alt="${product.name}" class="product-thumb"></td>
            <td>
              <div class="product-cell" style="gap:0;">
                <div>
                  <strong>${product.name}</strong>
                </div>
              </div>
            </td>
            <td><span class="product-category" style="margin:0;">${product.category}</span></td>
            <td>₱${product.price.toFixed(2)}</td>
            <td>${product.badge === "—" ? "—" : `<span class="badge badge-accent">${product.badge}</span>`}</td>
            <td>
              ${product.availability === "Available"
                ? `<span class="badge badge-success">Yes</span>`
                : `<span class="badge badge-danger">Hidden</span>`}
            </td>
            <td>${product.orders}</td>
            <td>
              <div class="action-row">
                <a href="admin-product-form.php" class="mini-btn edit">✎ Edit</a>
                <button class="mini-btn delete" data-delete="${product.name}">🗑</button>
              </div>
            </td>
          </tr>
        `).join("");
      }

      productSearch.addEventListener("input", renderProducts);
      categoryFilter.addEventListener("change", renderProducts);
      availabilityFilter.addEventListener("change", renderProducts);

      tableBody.addEventListener("click", (event) => {
        const deleteBtn = event.target.closest("[data-delete]");
        if (!deleteBtn) return;
        alert(`${deleteBtn.dataset.delete} deleted. Demo only.`);
      });

      renderProducts();
    });
  </script>
</body>
</html>