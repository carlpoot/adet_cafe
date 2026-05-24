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


    .cart-item-detail {
      margin-top: 8px;
      display: grid;
      gap: 4px;
      font-family: Arial, Helvetica, sans-serif;
      color: var(--text-muted);
      font-size: 0.88rem;
    }

    .cart-item-note {
      color: var(--primary);
    }

    .modify-btn {
      font-family: Arial, Helvetica, sans-serif;
      font-weight: 700;
      color: var(--accent);
      padding: 0;
    }

    .cart-modal-backdrop {
      position: fixed;
      inset: 0;
      background: rgba(19, 13, 9, 0.48);
      display: none;
      place-items: center;
      z-index: 9999;
      padding: 18px;
    }

    .cart-modal-backdrop.active {
      display: grid;
    }

    .cart-modal {
      width: min(100%, 560px);
      background: var(--surface);
      border: 1px solid var(--border);
      border-radius: 26px;
      box-shadow: var(--shadow-lg);
      padding: 22px;
    }

    .cart-modal-header {
      display: flex;
      align-items: flex-start;
      justify-content: space-between;
      gap: 16px;
      margin-bottom: 18px;
    }

    .cart-modal-close {
      width: 38px;
      height: 38px;
      border-radius: 999px;
      background: var(--bg-soft);
      border: 1px solid var(--border);
      color: var(--primary);
      font-weight: 900;
    }

    .modal-form-grid {
      display: grid;
      gap: 14px;
    }

    .modal-addon-grid {
      display: grid;
      grid-template-columns: repeat(2, minmax(0, 1fr));
      gap: 10px;
    }

    .modal-addon-grid label {
      display: flex;
      gap: 8px;
      align-items: center;
      font-family: Arial, Helvetica, sans-serif;
      background: var(--bg-soft);
      border: 1px solid var(--border);
      border-radius: 14px;
      padding: 10px 12px;
    }

    @media (max-width: 560px) {
      .modal-addon-grid {
        grid-template-columns: 1fr;
      }
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


  <div class="cart-modal-backdrop" id="modifyCartModal" aria-hidden="true">
    <div class="cart-modal" role="dialog" aria-modal="true" aria-labelledby="modifyCartTitle">
      <div class="cart-modal-header">
        <div>
          <span class="eyebrow">Customize Item</span>
          <h2 class="form-title mb-0" id="modifyCartTitle">Modify Item</h2>
          <p class="section-text mb-0" id="modifyCartItemName"></p>
        </div>
        <button type="button" class="cart-modal-close" id="closeModifyModal" aria-label="Close modify item modal">×</button>
      </div>

      <form class="modal-form-grid" id="modifyCartForm">
        <input type="hidden" id="modifyItemIndex">

        <div class="field">
          <label for="modifyQuantity">Quantity</label>
          <input class="input" type="number" id="modifyQuantity" min="1" value="1">
        </div>

        <div class="field">
          <label for="modifySize">Size</label>
          <select class="select" id="modifySize">
            <option value="Small">Small</option>
            <option value="Regular">Regular</option>
            <option value="Large">Large</option>
          </select>
        </div>

        <div class="field">
          <label>Add-ons</label>
          <div class="modal-addon-grid">
            <label><input type="checkbox" class="modify-addon" value="Extra Shot" data-price="20"> Extra Shot ₱20</label>
            <label><input type="checkbox" class="modify-addon" value="Oat Milk" data-price="15"> Oat Milk ₱15</label>
            <label><input type="checkbox" class="modify-addon" value="Vanilla Syrup" data-price="10"> Vanilla Syrup ₱10</label>
            <label><input type="checkbox" class="modify-addon" value="Caramel Drizzle" data-price="10"> Caramel Drizzle ₱10</label>
            <label><input type="checkbox" class="modify-addon" value="Cream Cloud" data-price="20"> Cream Cloud ₱20</label>
          </div>
        </div>

        <div class="field">
          <label for="modifyInstructions">Special Instructions</label>
          <textarea class="textarea" id="modifyInstructions" maxlength="180" placeholder="Example: less ice, no whipped cream, extra napkins, allergy note"></textarea>
        </div>

        <button type="submit" class="btn btn-primary btn-full">Update Item</button>
      </form>
    </div>
  </div>

  <script src="script.js"></script>
  <script>
    document.addEventListener("DOMContentLoaded", () => {
      const container = document.getElementById("cartItemsContainer");
      const subtotalEl = document.getElementById("summarySubtotal");
      const deliveryEl = document.getElementById("summaryDelivery");
      const totalEl = document.getElementById("summaryTotal");
      const itemCountEl = document.getElementById("summaryItemCount");
      const checkoutBtn = document.getElementById("checkoutBtn");

      const modal = document.getElementById("modifyCartModal");
      const closeModifyModal = document.getElementById("closeModifyModal");
      const modifyForm = document.getElementById("modifyCartForm");
      const modifyItemIndex = document.getElementById("modifyItemIndex");
      const modifyCartItemName = document.getElementById("modifyCartItemName");
      const modifyQuantity = document.getElementById("modifyQuantity");
      const modifySize = document.getElementById("modifySize");
      const modifyInstructions = document.getElementById("modifyInstructions");
      const modifyAddons = document.querySelectorAll(".modify-addon");

      function formatPeso(value) {
        return `₱${Number(value).toFixed(2)}`;
      }

      function getCart() {
        if (window.FurrfectCafe && typeof FurrfectCafe.getCart === "function") {
          return FurrfectCafe.getCart();
        }

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

      function getBaseProduct(productId) {
        if (!window.FurrfectCafe || !Array.isArray(FurrfectCafe.products)) return null;
        return FurrfectCafe.products.find(product => Number(product.id) === Number(productId));
      }

      function getSizePrice(item, size) {
        const baseProduct = getBaseProduct(item.productId);
        const basePrice = baseProduct ? Number(baseProduct.price) : Number(item.price || 0);
        const sizeAdjustments = { Small: 0, Regular: 10, Large: 25 };

        return basePrice + (sizeAdjustments[size] || 0);
      }

      function makeCartKey(item) {
        const addonKey = (item.selectedAddons || []).map(addon => addon.name).sort().join("|");
        const noteKey = (item.specialInstructions || "").trim().toLowerCase();

        return `${item.productId}-${item.selectedSize || "Small"}-${addonKey}-${noteKey}`;
      }

      function getAddonText(item) {
        const addons = item.selectedAddons || [];
        if (!addons.length) return "";

        return addons.map(addon => `${addon.name} +${formatPeso(addon.price)}`).join(", ");
      }

      function renderCart() {
        const cart = getCart();
        const subtotal = cart.reduce((sum, item) => sum + (Number(item.price) * Number(item.quantity)), 0);
        const deliveryFee = cart.length ? 50 : 0;
        const total = subtotal + deliveryFee;
        const count = cart.reduce((sum, item) => sum + Number(item.quantity), 0);

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

        container.innerHTML = cart.map((item, index) => {
          const addonText = getAddonText(item);
          const noteText = item.specialInstructions ? item.specialInstructions : "";

          return `
            <article class="cart-item">
              <a class="cart-thumb" href="product.php?id=${item.productId}">
                <img src="${item.image}" alt="${item.name}">
              </a>

              <div>
                <h3 class="cart-item-name">${item.name}</h3>
                <div class="cart-meta">${item.categoryLabel || ""}</div>

                <div class="cart-item-detail">
                  ${item.selectedSize ? `<span>Size: <strong>${item.selectedSize}</strong></span>` : ""}
                  ${addonText ? `<span>Add-ons: ${addonText}</span>` : ""}
                  ${noteText ? `<span class="cart-item-note">Note: ${noteText}</span>` : ""}
                </div>

                <div class="cart-item-actions">
                  <div class="qty-selector">
                    <button type="button" class="qty-minus" data-index="${index}">−</button>
                    <span>${item.quantity}</span>
                    <button type="button" class="qty-plus" data-index="${index}">+</button>
                  </div>

                  <button type="button" class="modify-btn" data-index="${index}">Modify</button>
                  <button type="button" class="remove-btn" data-index="${index}">Remove</button>
                </div>
              </div>

              <div class="line-total">
                <div class="cart-meta">Subtotal</div>
                <div class="price">${formatPeso(Number(item.price) * Number(item.quantity))}</div>
              </div>
            </article>
          `;
        }).join("");
      }

      function openModifyModal(index) {
        const cart = getCart();
        const item = cart[index];

        if (!item) return;

        modifyItemIndex.value = index;
        modifyCartItemName.textContent = item.name;
        modifyQuantity.value = item.quantity || 1;
        modifySize.value = item.selectedSize || "Small";
        modifyInstructions.value = item.specialInstructions || "";

        const currentAddons = (item.selectedAddons || []).map(addon => addon.name);

        modifyAddons.forEach(addon => {
          addon.checked = currentAddons.includes(addon.value);
        });

        modal.classList.add("active");
        modal.setAttribute("aria-hidden", "false");
      }

      function closeModal() {
        modal.classList.remove("active");
        modal.setAttribute("aria-hidden", "true");
      }

      container.addEventListener("click", (event) => {
        const minusBtn = event.target.closest(".qty-minus");
        const plusBtn = event.target.closest(".qty-plus");
        const removeBtn = event.target.closest(".remove-btn");
        const modifyBtn = event.target.closest(".modify-btn");
        let cart = getCart();

        if (minusBtn) {
          const index = Number(minusBtn.dataset.index);
          if (!cart[index]) return;

          cart[index].quantity = Math.max(1, Number(cart[index].quantity) - 1);
          saveCart(cart);
          renderCart();
        }

        if (plusBtn) {
          const index = Number(plusBtn.dataset.index);
          if (!cart[index]) return;

          cart[index].quantity = Number(cart[index].quantity) + 1;
          saveCart(cart);
          renderCart();
        }

        if (modifyBtn) {
          openModifyModal(Number(modifyBtn.dataset.index));
        }

        if (removeBtn) {
          const index = Number(removeBtn.dataset.index);
          cart = cart.filter((_, itemIndex) => itemIndex !== index);
          saveCart(cart);
          renderCart();
        }
      });

      modifyForm.addEventListener("submit", event => {
        event.preventDefault();

        const cart = getCart();
        const index = Number(modifyItemIndex.value);

        if (!cart[index]) return;

        const selectedAddons = Array.from(modifyAddons)
          .filter(addon => addon.checked)
          .map(addon => ({
            name: addon.value,
            price: Number(addon.dataset.price)
          }));

        const addonTotal = selectedAddons.reduce((sum, addon) => sum + addon.price, 0);
        const updatedSize = modifySize.value;
        const updatedItem = {
          ...cart[index],
          quantity: Math.max(1, Number(modifyQuantity.value || 1)),
          selectedSize: updatedSize,
          selectedAddons,
          specialInstructions: modifyInstructions.value.trim()
        };

        updatedItem.price = getSizePrice(updatedItem, updatedSize) + addonTotal;
        updatedItem.cartKey = makeCartKey(updatedItem);

        cart[index] = updatedItem;

        saveCart(cart);
        renderCart();
        closeModal();
      });

      closeModifyModal.addEventListener("click", closeModal);

      modal.addEventListener("click", event => {
        if (event.target === modal) {
          closeModal();
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