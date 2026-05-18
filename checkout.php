<?php require_once "includes/auth.php"; ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>FurrfectCafe | Checkout</title>
  <link rel="stylesheet" href="style.css">
  <style>
    .checkout-page {
      padding: 28px 0 56px;
    }

    .checkout-layout {
      display: grid;
      grid-template-columns: 1.05fr 0.95fr;
      gap: 28px;
      align-items: start;
    }

    .checkout-stack {
      display: grid;
      gap: 18px;
    }

    .checkout-card,
    .summary-card {
      background: var(--surface);
      border: 1px solid var(--border);
      border-radius: 28px;
      box-shadow: var(--shadow-md);
      padding: 22px;
    }

    .selection-grid {
      display: grid;
      grid-template-columns: repeat(2, minmax(0, 1fr));
      gap: 14px;
    }

    .select-card {
      border: 1.5px solid var(--border);
      border-radius: 22px;
      background: var(--bg-soft);
      padding: 18px;
      transition: var(--transition);
      font-family: Arial, Helvetica, sans-serif;
      text-align: center;
    }

    .select-card strong {
      display: block;
      color: var(--text);
      margin-bottom: 4px;
      font-size: 1rem;
    }

    .select-card span {
      color: var(--text-muted);
      font-size: 0.9rem;
    }

    .select-card.active,
    .select-card:hover {
      border-color: var(--accent);
      background: #fff7ef;
      box-shadow: 0 0 0 4px rgba(201, 122, 53, 0.1);
    }

    .summary-card {
      position: sticky;
      top: 100px;
    }

    .summary-items {
      display: grid;
      gap: 10px;
      margin: 18px 0;
      font-family: Arial, Helvetica, sans-serif;
    }

    .summary-item {
      display: flex;
      justify-content: space-between;
      gap: 16px;
      color: var(--text-muted);
      font-size: 0.94rem;
    }

    .summary-line {
      display: flex;
      justify-content: space-between;
      gap: 16px;
      font-family: Arial, Helvetica, sans-serif;
      color: var(--text-muted);
      margin-top: 10px;
    }

    .summary-total {
      border-top: 1px solid var(--border);
      padding-top: 16px;
      margin-top: 14px;
      color: var(--text);
      font-weight: 700;
    }

    .payment-note {
      font-family: Arial, Helvetica, sans-serif;
      color: var(--text-muted);
      font-size: 0.92rem;
      margin-top: 10px;
    }

    @media (max-width: 920px) {
      .checkout-layout {
        grid-template-columns: 1fr;
      }

      .summary-card {
        position: static;
      }
    }

    @media (max-width: 620px) {
      .selection-grid {
        grid-template-columns: 1fr;
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

  <main class="checkout-page">
    <div class="container">
      <div class="checkout-layout">
        <section class="checkout-stack">
          <div class="checkout-card">
            <span class="eyebrow">Almost There</span>
            <h1 class="section-title">Checkout</h1>
          </div>

          <div class="checkout-card">
            <h2 class="form-title">🚚 Delivery Options</h2>
            <div class="selection-grid" id="deliveryOptions">
              <button type="button" class="select-card active" data-type="Delivery">
                <strong>Delivery</strong>
                <span>We bring it to you</span>
              </button>
              <button type="button" class="select-card" data-type="Pick-up">
                <strong>Pick-up</strong>
                <span>Collect at the café</span>
              </button>
            </div>
          </div>

          <div class="checkout-card">
            <h2 class="form-title">🕒 Schedule</h2>
            <div class="selection-grid" id="scheduleOptions">
              <button type="button" class="select-card active" data-schedule="Order Now">
                <strong>Order Now</strong>
                <span>Prepare as soon as possible</span>
              </button>
              <button type="button" class="select-card" data-schedule="Pre-order">
                <strong>Pre-order</strong>
                <span>Soon...</span>
              </button>
            </div>
          </div>

          <div class="checkout-card">
            <h2 class="form-title">📍 Delivery Address</h2>
            <form id="checkoutForm" class="form-grid" novalidate>
              <div class="field">
                <label for="fullName">Full Name *</label>
                <input class="input" type="text" id="fullName" required>
              </div>

              <div class="field">
                <label for="contactNumber">Contact Number *</label>
                <input class="input" type="tel" id="contactNumber" required>
              </div>

              <div class="field">
                <label for="streetAddress">Street Address *</label>
                <input class="input" type="text" id="streetAddress" placeholder="House #, Street, Barangay" required>
              </div>

              <div class="field">
                <label for="orderNotes">Order Notes <span style="font-weight:400;">(optional)</span></label>
                <textarea class="textarea" id="orderNotes" placeholder="Leave a note for the rider, gate, or unit number"></textarea>
              </div>
            </form>
          </div>

          <div class="checkout-card">
            <h2 class="form-title">💵 Payment Method</h2>
            <div id="paymentOptions">
              <button type="button" class="select-card active" data-payment="Cash on Delivery" style="width:100%;">
                <strong>Cash on Delivery</strong>
                <span>Pay when your order arrives</span>
              </button>
            </div>
            <p class="payment-note">Front-end demo only. Additional payment methods can be connected later.</p>
          </div>
        </section>

        <aside class="summary-card">
          <span class="eyebrow">Order Summary</span>
          <h2 class="form-title">Your Order</h2>

          <div class="summary-items" id="summaryItems"></div>

          <div class="summary-line">
            <span>Subtotal</span>
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

          <button class="btn btn-primary btn-full mt-24" id="placeOrderBtn">✔ Place Order</button>
        </aside>
      </div>
    </div>
  </main>

  <script src="script.js"></script>
  <script>
    document.addEventListener("DOMContentLoaded", () => {
      const summaryItems = document.getElementById("summaryItems");
      const subtotalEl = document.getElementById("summarySubtotal");
      const deliveryEl = document.getElementById("summaryDelivery");
      const totalEl = document.getElementById("summaryTotal");
      const placeOrderBtn = document.getElementById("placeOrderBtn");

      const deliveryOptions = document.getElementById("deliveryOptions");
      const scheduleOptions = document.getElementById("scheduleOptions");

      let orderType = "Delivery";
      let scheduleType = "Order Now";
      let paymentMethod = "Cash on Delivery";

      function formatPeso(value) {
        return `₱${Number(value).toFixed(2)}`;
      }

      function getCart() {
        if (window.FurrfectCafe && typeof FurrfectCafe.getCart === "function") {
          return FurrfectCafe.getCart();
        }

        return JSON.parse(localStorage.getItem("furrfectcafe_cart") || "[]");
      }

      function clearCart() {
        if (window.FurrfectCafe && typeof FurrfectCafe.clearCart === "function") {
          FurrfectCafe.clearCart();
        } else {
          localStorage.setItem("furrfectcafe_cart", JSON.stringify([]));
        }
      }

      function getUser() {
        return JSON.parse(localStorage.getItem("furrfectcafe_user") || "{}");
      }

      function getOrderTotals() {
        const cart = getCart();
        const subtotal = cart.reduce((sum, item) => sum + item.price * item.quantity, 0);
        const deliveryFee = orderType === "Delivery" ? 50 : 0;
        const total = subtotal + deliveryFee;

        return {
          subtotal,
          deliveryFee,
          total
        };
      }

      function renderSummary() {
        const cart = getCart();
        const totals = getOrderTotals();

        if (!cart.length) {
          summaryItems.innerHTML = `
            <div class="empty-state">
              <h3>Your cart is empty</h3>
              <p class="section-text">Please add an item before checking out.</p>
              <a href="menu.php" class="btn btn-primary mt-16">Go to Menu</a>
            </div>
          `;

          subtotalEl.textContent = formatPeso(0);
          deliveryEl.textContent = formatPeso(0);
          totalEl.textContent = formatPeso(0);

          placeOrderBtn.disabled = true;
          placeOrderBtn.style.opacity = "0.6";
          placeOrderBtn.style.cursor = "not-allowed";
          return;
        }

        placeOrderBtn.disabled = false;
        placeOrderBtn.style.opacity = "1";
        placeOrderBtn.style.cursor = "pointer";

        summaryItems.innerHTML = cart.map(item => `
          <div class="summary-item">
            <span>${item.name} × ${item.quantity}</span>
            <strong>${formatPeso(item.price * item.quantity)}</strong>
          </div>
        `).join("");

        subtotalEl.textContent = formatPeso(totals.subtotal);
        deliveryEl.textContent = formatPeso(totals.deliveryFee);
        totalEl.textContent = formatPeso(totals.total);
      }

      function activateCard(container, target, dataKey) {
        container.querySelectorAll(".select-card").forEach(card => {
          card.classList.remove("active");
        });

        target.classList.add("active");
        return target.dataset[dataKey];
      }

      deliveryOptions.addEventListener("click", event => {
        const btn = event.target.closest(".select-card");
        if (!btn) return;

        orderType = activateCard(deliveryOptions, btn, "type");
        renderSummary();
      });

      scheduleOptions.addEventListener("click", event => {
        const btn = event.target.closest(".select-card");
        if (!btn) return;

        scheduleType = activateCard(scheduleOptions, btn, "schedule");
      });

      function prefillUser() {
        const user = getUser();

        document.getElementById("fullName").value = user.name || "Demo Customer";
        document.getElementById("contactNumber").value = user.contactNumber || "09123456789";
        document.getElementById("streetAddress").value = user.fullAddress || "Legazpi City, Albay";
      }

      function createOrderNumber() {
        const randomNumber = Math.floor(Math.random() * 9000) + 1000;
        return `FC-${new Date().getFullYear()}-${randomNumber}`;
      }

      function placeDemoOrder() {
        const cart = getCart();

        if (!cart.length) {
          alert("Your cart is empty. Please add an item first.");
          window.location.href = "menu.php";
          return;
        }

        const fullName = document.getElementById("fullName").value.trim();
        const contactNumber = document.getElementById("contactNumber").value.trim();
        const streetAddress = document.getElementById("streetAddress").value.trim();
        const orderNotes = document.getElementById("orderNotes").value.trim();

        if (!fullName) {
          alert("Please enter your full name.");
          return;
        }

        if (!contactNumber) {
          alert("Please enter your contact number.");
          return;
        }

        if (!streetAddress) {
          alert("Please enter your address.");
          return;
        }

        const totals = getOrderTotals();
        const orderNumber = createOrderNumber();

        const newOrder = {
          id: orderNumber,
          date: new Date().toLocaleString(),
          customerName: fullName,
          contactNumber,
          address: streetAddress,
          orderNotes,
          type: orderType,
          schedule: scheduleType,
          paymentMethod,
          paymentStatus: paymentMethod === "Cash on Delivery" ? "Unpaid" : "Paid",
          status: "Pending",
          subtotal: totals.subtotal,
          deliveryFee: totals.deliveryFee,
          total: totals.total,
          items: cart.map(item => ({
            productId: item.productId,
            name: item.name,
            quantity: item.quantity,
            price: item.price,
            image: item.image || "",
            categoryLabel: item.categoryLabel || ""
          }))
        };

        const existingOrders = JSON.parse(localStorage.getItem("furrfectcafe_orders") || "[]");

        existingOrders.unshift(newOrder);

        localStorage.setItem("furrfectcafe_orders", JSON.stringify(existingOrders));
        localStorage.setItem("furrfectcafe_last_order", JSON.stringify(newOrder));

        clearCart();

        window.location.href = "order-confirmation.php";
      }

      placeOrderBtn.addEventListener("click", placeDemoOrder);

      prefillUser();
      renderSummary();
    });
  </script>
</body>
</html>