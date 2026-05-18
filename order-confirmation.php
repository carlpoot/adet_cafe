<?php require_once "includes/auth.php"; ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>FurrfectCafe | Order Confirmation</title>
  <link rel="stylesheet" href="style.css">
  <style>
    .confirmation-page {
      padding: 32px 0 56px;
    }

    .confirmation-layout {
      display: grid;
      grid-template-columns: 0.8fr 1.2fr;
      gap: 28px;
      align-items: start;
    }

    .confirm-card,
    .details-card {
      background: var(--surface);
      border: 1px solid var(--border);
      border-radius: 28px;
      box-shadow: var(--shadow-md);
      padding: 24px;
    }

    .order-number-box {
      background: #fbf4ea;
      border: 1px solid var(--border);
      border-radius: 22px;
      padding: 22px;
      text-align: center;
      margin: 18px 0;
    }

    .order-number-box strong {
      display: block;
      font-size: clamp(2rem, 4vw, 3rem);
      line-height: 1;
      margin-top: 10px;
      color: var(--primary);
    }

    .detail-list {
      display: grid;
      gap: 14px;
      font-family: Arial, Helvetica, sans-serif;
      color: var(--text-muted);
      margin-top: 18px;
    }

    .detail-list strong {
      color: var(--text);
    }

    .order-items {
      display: grid;
      gap: 12px;
      margin: 16px 0;
      font-family: Arial, Helvetica, sans-serif;
    }

    .order-item-row,
    .info-row {
      display: flex;
      justify-content: space-between;
      gap: 16px;
    }

    .total-row {
      border-top: 1px solid var(--border);
      padding-top: 16px;
      margin-top: 8px;
      font-weight: 700;
      color: var(--text);
    }

    .action-grid {
      display: grid;
      gap: 12px;
      margin-top: 22px;
    }

    @media (max-width: 920px) {
      .confirmation-layout {
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
        <a href="orders.php" class="active">My Orders</a>
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

  <main class="confirmation-page">
    <div class="container">
      <div class="confirmation-layout">
        <section class="confirm-card">
          <span class="eyebrow">Order Placed</span>
          <h1 class="section-title">Thank you!</h1>
          <p class="section-text">Your order has been received and is being prepared.</p>

          <div class="order-number-box">
            <div class="section-text">Your Order Number</div>
            <strong id="orderNumber">FC-2026-0000</strong>
            <button class="btn btn-secondary btn-sm mt-16" id="copyOrderBtn">📋 Copy</button>
          </div>

          <div class="action-grid">
            <a href="orders.php" class="btn btn-primary btn-full">📦 Track My Order</a>
            <a href="menu.php" class="btn btn-accent btn-full">🍽 Order Again</a>
          </div>
        </section>

        <section class="details-card">
          <span class="eyebrow">Order Summary</span>
          <h2 class="form-title">Details</h2>

          <div class="order-items" id="orderItems"></div>

          <div class="info-row">
            <span class="section-text">Delivery fee</span>
            <strong id="deliveryFee">₱0.00</strong>
          </div>

          <div class="info-row total-row">
            <span>Total</span>
            <strong id="orderTotal">₱0.00</strong>
          </div>

          <div class="detail-list">
            <div><strong>📍 Address:</strong> <span id="deliveryAddress"></span></div>
            <div><strong>💵 Payment:</strong> <span id="paymentMethod"></span></div>
            <div><strong>🕒 Estimated time:</strong> <span id="estimatedTime"></span></div>
            <div><strong>☎ Contact number:</strong> <span id="contactNumber"></span></div>
          </div>
        </section>
      </div>
    </div>
  </main>

  <script src="script.js"></script>
  <script>
    document.addEventListener("DOMContentLoaded", () => {
      const order = JSON.parse(localStorage.getItem("furrfectcafe_last_order") || "null");

      const orderNumber = document.getElementById("orderNumber");
      const orderItems = document.getElementById("orderItems");
      const deliveryFee = document.getElementById("deliveryFee");
      const orderTotal = document.getElementById("orderTotal");
      const deliveryAddress = document.getElementById("deliveryAddress");
      const paymentMethod = document.getElementById("paymentMethod");
      const estimatedTime = document.getElementById("estimatedTime");
      const contactNumber = document.getElementById("contactNumber");
      const copyOrderBtn = document.getElementById("copyOrderBtn");

      function formatPeso(value) {
        return `₱${Number(value).toFixed(2)}`;
      }

      if (!order) {
        orderNumber.textContent = "No order found";

        orderItems.innerHTML = `
          <p class="section-text">
            No recent order was found. Please place an order first.
          </p>
        `;

        deliveryFee.textContent = formatPeso(0);
        orderTotal.textContent = formatPeso(0);
        deliveryAddress.textContent = "N/A";
        paymentMethod.textContent = "N/A";
        estimatedTime.textContent = "N/A";
        contactNumber.textContent = "N/A";

        return;
      }

      orderNumber.textContent = order.id;

      orderItems.innerHTML = order.items.map(item => `
        <div class="order-item-row">
          <span>${item.name} × ${item.quantity}</span>
          <strong>${formatPeso(item.price * item.quantity)}</strong>
        </div>
      `).join("");

      deliveryFee.textContent = formatPeso(order.deliveryFee || 0);
      orderTotal.textContent = formatPeso(order.total || 0);
      deliveryAddress.textContent = order.address || "N/A";
      paymentMethod.textContent = order.paymentMethod || "Cash on Delivery";
      contactNumber.textContent = order.contactNumber || "N/A";

      estimatedTime.textContent = order.type === "Pick-up"
        ? "Ready for pick-up in 15 to 25 minutes"
        : "Estimated delivery in 30 to 45 minutes";

      copyOrderBtn.addEventListener("click", async () => {
        try {
          await navigator.clipboard.writeText(order.id);
          alert("Order number copied.");
        } catch (error) {
          alert(`Order number: ${order.id}`);
        }
      });
    });
  </script>
</body>
</html>