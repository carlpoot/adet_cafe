<?php require_once "includes/auth.php"; ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>FurrfectCafe | My Orders</title>
  <link rel="stylesheet" href="style.css">
  <style>
    .orders-page {
      padding: 28px 0 56px;
    }

    .orders-stack {
      display: grid;
      gap: 18px;
    }

    .order-card {
      background: var(--surface);
      border: 1px solid var(--border);
      border-radius: 28px;
      box-shadow: var(--shadow-md);
      padding: 22px;
    }

    .order-top {
      display: flex;
      justify-content: space-between;
      gap: 16px;
      flex-wrap: wrap;
      margin-bottom: 16px;
    }

    .order-id {
      font-size: 1.2rem;
      margin-bottom: 4px;
    }

    .order-meta {
      font-family: Arial, Helvetica, sans-serif;
      color: var(--text-muted);
      font-size: 0.92rem;
    }

    .badge-row {
      display: flex;
      gap: 8px;
      flex-wrap: wrap;
    }

    .tracker {
      display: grid;
      grid-template-columns: repeat(4, minmax(0, 1fr));
      gap: 12px;
      margin: 18px 0;
    }

    .track-step {
      text-align: center;
      position: relative;
    }

    .track-step::after {
      content: "";
      position: absolute;
      top: 17px;
      left: calc(50% + 20px);
      width: calc(100% - 40px);
      height: 2px;
      background: var(--border);
      z-index: 0;
    }

    .track-step:last-child::after {
      display: none;
    }

    .track-dot {
      position: relative;
      z-index: 1;
      width: 34px;
      height: 34px;
      margin: 0 auto 8px;
      border-radius: 50%;
      display: grid;
      place-items: center;
      background: #f3eadf;
      border: 2px solid var(--border);
      font-family: Arial, Helvetica, sans-serif;
      font-size: 0.8rem;
      font-weight: 700;
      color: var(--text-muted);
    }

    .track-step.active .track-dot,
    .track-step.done .track-dot {
      background: var(--accent);
      color: white;
      border-color: var(--accent);
    }

    .track-step span {
      display: block;
      font-family: Arial, Helvetica, sans-serif;
      font-size: 0.86rem;
      color: var(--text-muted);
    }

    .items-summary {
      display: grid;
      gap: 10px;
      padding: 16px;
      border-radius: 20px;
      background: var(--bg-soft);
      font-family: Arial, Helvetica, sans-serif;
    }

    .item-line,
    .order-total-line {
      display: flex;
      justify-content: space-between;
      gap: 12px;
    }

    .order-total-line {
      border-top: 1px solid var(--border);
      padding-top: 12px;
      margin-top: 6px;
      font-weight: 700;
      color: var(--text);
    }

    .order-actions {
      display: flex;
      gap: 12px;
      flex-wrap: wrap;
      margin-top: 18px;
    }

    @media (max-width: 700px) {
      .tracker {
        grid-template-columns: repeat(2, minmax(0, 1fr));
      }

      .track-step::after {
        display: none;
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

  <main class="orders-page">
    <div class="container">
      <span class="eyebrow">Account</span>
      <h1 class="section-title">My Orders</h1>
      <p class="section-text">Track your current and past orders all in one place.</p>

      <div class="orders-stack mt-32" id="ordersContainer"></div>
    </div>
  </main>

  <script src="script.js"></script>
  <script>
    document.addEventListener("DOMContentLoaded", () => {
      const container = document.getElementById("ordersContainer");

      function formatPeso(value) {
        return `₱${Number(value).toFixed(2)}`;
      }

      function getOrders() {
        let orders = JSON.parse(localStorage.getItem("furrfectcafe_orders") || "[]");

        if (!orders.length) {
          orders = [
            {
              id: "FC-2026-2043",
              date: "Mar 27, 2026 2:14 PM",
              type: "Delivery",
              paymentMethod: "Cash on Delivery",
              paymentStatus: "Unpaid",
              status: "Preparing",
              total: 387.5,
              deliveryFee: 50,
              address: "Legazpi City, Albay",
              contactNumber: "09123456789",
              items: [
                { name: "Signature Cat Latte", quantity: 2, price: 85 },
                { name: "Matcha Cheesecake", quantity: 1, price: 120 },
                { name: "Paw Print Cupcake", quantity: 1, price: 65 }
              ]
            },
            {
              id: "FC-2026-2038",
              date: "Mar 20, 2026 4:50 PM",
              type: "Pick-up",
              paymentMethod: "Cash",
              paymentStatus: "Paid",
              status: "Delivered",
              total: 245,
              deliveryFee: 0,
              address: "FurrfectCafe Branch",
              contactNumber: "09123456789",
              items: [
                { name: "Brown Sugar Milk Tea", quantity: 2, price: 95 },
                { name: "Croissant", quantity: 1, price: 55 }
              ]
            }
          ];

          localStorage.setItem("furrfectcafe_orders", JSON.stringify(orders));
        }

        return orders;
      }

      function statusIndex(status) {
        const steps = ["Pending", "Preparing", "Ready", "Delivered"];
        const index = steps.indexOf(status);
        return index === -1 ? 0 : index;
      }

      function renderTracker(status) {
        const steps = ["Pending", "Preparing", "Ready", "Delivered"];
        const current = statusIndex(status);

        return `
          <div class="tracker">
            ${steps.map((step, index) => `
              <div class="track-step ${index < current ? "done" : ""} ${index === current ? "active" : ""}">
                <div class="track-dot">${index + 1}</div>
                <span>${step}</span>
              </div>
            `).join("")}
          </div>
        `;
      }

      function paymentBadge(status) {
        return status === "Paid"
          ? `<span class="badge badge-success">Paid</span>`
          : `<span class="badge badge-danger">Unpaid</span>`;
      }

      function statusBadge(status) {
        const map = {
          Pending: "badge-warning",
          Preparing: "badge-accent",
          Ready: "badge-soft",
          Delivered: "badge-success"
        };

        return `<span class="badge ${map[status] || "badge-soft"}">${status}</span>`;
      }

      function renderOrders() {
        const orders = getOrders();

        if (!orders.length) {
          container.innerHTML = `
            <div class="order-card">
              <h3>No orders yet</h3>
              <p class="section-text">Place your first order from the menu.</p>
              <a href="menu.php" class="btn btn-primary mt-16">Go to Menu</a>
            </div>
          `;
          return;
        }

        container.innerHTML = orders.map(order => `
          <article class="order-card">
            <div class="order-top">
              <div>
                <h2 class="order-id">${order.id}</h2>
                <div class="order-meta">${order.date || "Today"} • ${order.type || "Delivery"}</div>
              </div>

              <div class="badge-row">
                ${statusBadge(order.status || "Pending")}
                ${paymentBadge(order.paymentStatus || "Unpaid")}
              </div>
            </div>

            ${renderTracker(order.status || "Pending")}

            <div class="items-summary">
              ${order.items.map(item => `
                <div class="item-line">
                  <span>${item.name} × ${item.quantity}</span>
                  <strong>${formatPeso(item.price * item.quantity)}</strong>
                </div>
              `).join("")}

              <div class="order-total-line">
                <span>Total</span>
                <strong>${formatPeso(order.total)}</strong>
              </div>
            </div>

            <div class="order-actions">
              <button class="btn btn-secondary btn-sm receipt-btn" data-order-id="${order.id}">🧾 View Receipt</button>
              <button class="btn btn-primary btn-sm reorder-btn" data-order-id="${order.id}">↻ Reorder</button>
            </div>
          </article>
        `).join("");
      }

      container.addEventListener("click", event => {
        const receiptBtn = event.target.closest(".receipt-btn");
        const reorderBtn = event.target.closest(".reorder-btn");

        if (receiptBtn) {
          const orders = getOrders();
          const order = orders.find(item => item.id === receiptBtn.dataset.orderId);

          if (!order) {
            alert("Receipt not found.");
            return;
          }

          localStorage.setItem("furrfectcafe_last_order", JSON.stringify(order));
          window.location.href = "order-confirmation.php";
        }

        if (reorderBtn) {
          alert("Reorder demo: please select the items again from the menu.");
          window.location.href = "menu.php";
        }
      });

      renderOrders();
    });
  </script>
</body>
</html>