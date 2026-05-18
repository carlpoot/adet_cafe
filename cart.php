<?php require_once "includes/auth.php"; ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>FurrfectCafe | Your Cart</title>
  <link rel="stylesheet" href="style.css">
  <style>
    .cart-page {
      padding: 28px 0 56px;
    }

    .cart-layout {
      display: grid;
      grid-template-columns: 1.15fr 0.85fr;
      gap: 28px;
      align-items: start;
    }

    .cart-list-card,
    .summary-card {
      background: var(--surface);
      border: 1px solid var(--border);
      border-radius: 28px;
      box-shadow: var(--shadow-md);
    }

    .cart-list-card {
      padding: 22px;
    }

    .cart-header {
      display: flex;
      justify-content: space-between;
      align-items: end;
      gap: 16px;
      margin-bottom: 18px;
      flex-wrap: wrap;
    }

    .cart-item {
      display: grid;
      grid-template-columns: 92px 1fr auto;
      gap: 16px;
      padding: 16px;
      border: 1px solid var(--border);
      border-radius: 22px;
      background: var(--bg-soft);
    }

    .cart-item + .cart-item {
      margin-top: 14px;
    }

    .cart-thumb {
      width: 92px;
      height: 92px;
      border-radius: 18px;
      overflow: hidden;
      background: linear-gradient(135deg, #f1e6d8, #e8d4bc);
    }

    .cart-thumb img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }

    .cart-item-name {
      font-size: 1.2rem;
      margin-bottom: 4px;
    }

    .cart-meta {
      font-family: Arial, Helvetica, sans-serif;
      color: var(--text-muted);
      font-size: 0.92rem;
    }

    .cart-item-actions {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 14px;
      margin-top: 14px;
      flex-wrap: wrap;
    }

    .qty-selector {
      display: inline-flex;
      align-items: center;
      background: #fff;
      border: 1px solid var(--border);
      border-radius: 999px;
      overflow: hidden;
      min-height: 46px;
    }

    .qty-selector button {
      width: 42px;
      height: 46px;
      font-size: 1.1rem;
      color: var(--primary);
    }

    .qty-selector span {
      min-width: 40px;
      text-align: center;
      font-family: Arial, Helvetica, sans-serif;
      font-weight: 700;
    }

    .remove-btn {
      font-family: Arial, Helvetica, sans-serif;
      color: var(--danger);
      font-weight: 700;
    }

    .line-total {
      min-width: 120px;
      text-align: right;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      align-items: end;
      gap: 8px;
    }

    .summary-card {
      padding: 22px;
      position: sticky;
      top: 100px;
    }

    .summary-lines {
      display: grid;
      gap: 14px;
      margin-top: 18px;
      font-family: Arial, Helvetica, sans-serif;
    }

    .summary-line {
      display: flex;
      justify-content: space-between;
      gap: 18px;
      color: var(--text-muted);
    }

    .summary-total {
      border-top: 1px solid var(--border);
      padding-top: 16px;
      margin-top: 4px;
      font-size: 1.05rem;
      color: var(--text);
      font-weight: 700;
    }

    .continue-link {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      margin-top: 18px;
      font-family: Arial, Helvetica, sans-serif;
      color: var(--text-muted);
      font-weight: 700;
    }

    .empty-cart {
      text-align: center;
      padding: 42px 20px;
      border: 1px dashed var(--border);
      border-radius: 22px;
      background: var(--bg-soft);
    }

    @media (max-width: 920px) {
      .cart-layout {
        grid-template-columns: 1fr;
      }

      .summary-card {
        position: static;
      }
    }

    @media (max-width: 620px) {
      .cart-item {
        grid-template-columns: 1fr;
      }

      .line-total {
        align-items: start;
        text-align: left;
      }
    }
  </style>
</head>
<body>
  <header class="site-header">
    <div class="container navbar">
      <a href="index.php" class="brand">
        <span class="brand-mark">CAFE</span>
        <span class="brand-name">FurrfectCafe</span>
      </a>

      <nav class="nav-links">
        <a href="index.php">Home</a>
        <a href="menu.php">Menu</a>
        <a href="orders.php">My Orders</a>
      </nav>

      <div class="nav-actions">
        <a href="cart.php" class="cart-pill">
          <span>🛒 Cart</span>
          <span class="cart-pill-count" data-cart-count>0</span>
        </a>
        <a href="profile.php" class="btn btn-secondary btn-sm">👤 Profile</a>
      </div>

      <button class="menu-toggle" aria-label="Open menu" aria-expanded="false">☰</button>
    </div>
  </header>

  <main class="cart-page">
    <div class="container">
      <div class="cart-layout">
        <section class="cart-list-card">
          <div class="cart-header">
            <div>
              <span class="eyebrow">Shopping</span>
              <h1 class="section-title">Your Cart</h1>
            </div>
          </div>

          <div id="cartItemsContainer"></div>

          <a href="menu.php" class="continue-link">← Continue Shopping</a>
        </section>

        <aside class="summary-card">
          <span class="eyebrow">Order Summary</span>
          <h2 class="form-title">Summary</h2>

          <div class="summary-lines">
            <div class="summary-line">
              <span>Subtotal (<span id="summaryItemCount">0</span> items)</span>
              <strong id="summarySubtotal">₱0.00</strong>
            </div>
            <div class="summary-line">
              <span>Delivery fee</span>
              <strong id="summaryDelivery">₱0.00</strong>
            </div>
            <div class="summary-line summary-total">
              <span>Total</span>
              <strong id="summaryTotal">₱0.00</strong>
            </div>
          </div>

          <button class="btn btn-primary btn-full mt-24" id="checkoutBtn">→ Proceed to Checkout</button>
        </aside>
      </div>
    </div>
  </main>

  <script src="script.js"></script>
  <script>
    document.addEventListener("DOMContentLoaded", () => {
      const container = document.getElementById("cartItemsContainer");
      const subtotalEl = document.getElementById("summarySubtotal");
      const deliveryEl = document.getElementById("summaryDelivery");
      const totalEl = document.getElementById("summaryTotal");
      const itemCountEl = document.getElementById("summaryItemCount");
      const checkoutBtn = document.getElementById("checkoutBtn");

      function formatPeso(value) {
        return `₱${Number(value).toFixed(2)}`;
      }

      function getCart() {
        if (window.FurrfectCafe && typeof FurrfectCafe.getCart === "function") {
          return FurrfectCafe.getCart();
        }
        return JSON.parse(localStorage.getItem("furrfectcafe_cart") || "[]");
      }

      function saveCart(cart) {
        localStorage.setItem("furrfectcafe_cart", JSON.stringify(cart));
        if (window.FurrfectCafe && typeof FurrfectCafe.initGlobal === "function") {
          FurrfectCafe.initGlobal();
        }
      }

      function renderCart() {
        const cart = getCart();
        const subtotal = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
        const deliveryFee = cart.length ? 50 : 0;
        const total = subtotal + deliveryFee;
        const count = cart.reduce((sum, item) => sum + item.quantity, 0);

        itemCountEl.textContent = count;
        subtotalEl.textContent = formatPeso(subtotal);
        deliveryEl.textContent = formatPeso(deliveryFee);
        totalEl.textContent = formatPeso(total);

        if (!cart.length) {
          container.innerHTML = `
            <div class="empty-cart">
              <h3>Your cart is empty</h3>
              <p class="section-text">Browse the menu and add your favorite drinks and bites first.</p>
              <a href="menu.php" class="btn btn-primary mt-16">Go to Menu</a>
            </div>
          `;
          checkoutBtn.disabled = true;
          checkoutBtn.style.opacity = "0.6";
          checkoutBtn.style.cursor = "not-allowed";
          return;
        }

        checkoutBtn.disabled = false;
        checkoutBtn.style.opacity = "1";
        checkoutBtn.style.cursor = "pointer";

        container.innerHTML = cart.map(item => `
          <article class="cart-item">
            <a class="cart-thumb" href="product.php?id=${item.productId}">
              <img src="${item.image}" alt="${item.name}">
            </a>

            <div>
              <h3 class="cart-item-name">${item.name}</h3>
              <div class="cart-meta">${item.categoryLabel}</div>

              <div class="cart-item-actions">
                <div class="qty-selector">
                  <button type="button" class="qty-minus" data-id="${item.productId}">−</button>
                  <span>${item.quantity}</span>
                  <button type="button" class="qty-plus" data-id="${item.productId}">+</button>
                </div>

                <button type="button" class="remove-btn" data-id="${item.productId}">Remove</button>
              </div>
            </div>

            <div class="line-total">
              <div class="cart-meta">Subtotal</div>
              <div class="price">${formatPeso(item.price * item.quantity)}</div>
            </div>
          </article>
        `).join("");
      }

      container.addEventListener("click", (event) => {
        const minusBtn = event.target.closest(".qty-minus");
        const plusBtn = event.target.closest(".qty-plus");
        const removeBtn = event.target.closest(".remove-btn");
        let cart = getCart();

        if (minusBtn) {
          const id = Number(minusBtn.dataset.id);
          cart = cart.map(item => item.productId === id ? { ...item, quantity: Math.max(1, item.quantity - 1) } : item);
          saveCart(cart);
          renderCart();
        }

        if (plusBtn) {
          const id = Number(plusBtn.dataset.id);
          cart = cart.map(item => item.productId === id ? { ...item, quantity: item.quantity + 1 } : item);
          saveCart(cart);
          renderCart();
        }

        if (removeBtn) {
          const id = Number(removeBtn.dataset.id);
          cart = cart.filter(item => item.productId !== id);
          saveCart(cart);
          renderCart();
        }
      });

      checkoutBtn.addEventListener("click", () => {
        if (!getCart().length) return;
        window.location.href = "checkout.php";
      });

      renderCart();
    });
  </script>
</body>
</html>