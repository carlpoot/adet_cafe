<?php require_once "includes/admin-auth.php"; ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>FurrfectCafe Admin | Orders</title>
  <link rel="stylesheet" href="style.css">
  <style>
    .orders-shell {
      background: var(--surface);
      border: 1px solid var(--border);
      border-radius: 26px;
      box-shadow: var(--shadow-sm);
      padding: 18px;
    }

    .toolbar-grid {
      display: grid;
      grid-template-columns: 1.3fr 0.8fr 0.8fr 0.7fr;
      gap: 12px;
      margin-bottom: 14px;
    }

    .status-pills {
      display: flex;
      gap: 10px;
      flex-wrap: wrap;
      margin-bottom: 16px;
    }

    .status-pill {
      min-height: 40px;
      padding: 10px 14px;
      border-radius: 999px;
      border: 1px solid var(--border);
      background: #fff;
      font-family: Arial, Helvetica, sans-serif;
      color: var(--text-muted);
      font-weight: 700;
    }

    .status-pill.active {
      background: var(--bg-soft);
      color: var(--text);
      border-color: var(--border);
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

    .customer-cell strong {
      display: block;
    }

    .customer-cell small,
    .type-cell small {
      display: block;
      color: var(--text-muted);
      font-family: Arial, Helvetica, sans-serif;
      font-size: 0.84rem;
      margin-top: 3px;
    }

    .type-cell {
      font-family: Arial, Helvetica, sans-serif;
    }

    .status-select {
      min-height: 36px;
      border-radius: 12px;
      border: 1px solid var(--border);
      padding: 6px 10px;
      font-family: Arial, Helvetica, sans-serif;
      background: white;
    }

    .view-btn {
      min-height: 36px;
      min-width: 36px;
      border-radius: 999px;
      border: 1px solid var(--border);
      background: #fff;
    }

    @media (max-width: 1100px) {
      .toolbar-grid {
        grid-template-columns: 1fr 1fr;
      }
    }

    @media (max-width: 640px) {
      .toolbar-grid {
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
        <a href="admin-orders.php" class="active">Orders <span style="float:right; opacity:0.8;">5</span></a>
      </nav>

      <div class="eyebrow" style="color: rgba(255,255,255,0.45); margin-top: 20px;">Catalog</div>
      <nav class="admin-nav">
        <a href="admin-products.php">Products</a>
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
        <h1 class="admin-page-title">☰ Order Management</h1>
        <div class="admin-toolbar-right">
          <span>24 orders today</span>
        </div>
      </div>

      <section class="orders-shell">
        <div class="toolbar-grid">
          <input type="text" class="admin-search" id="orderSearch" placeholder="Search order # or customer...">

          <select class="admin-select" id="statusFilter">
            <option value="all">All Statuses</option>
            <option value="Pending">Pending</option>
            <option value="Preparing">Preparing</option>
            <option value="Ready">Ready</option>
            <option value="Delivered">Delivered</option>
          </select>

          <select class="admin-select" id="paymentFilter">
            <option value="all">All Payments</option>
            <option value="Paid">Paid</option>
            <option value="Unpaid">Unpaid</option>
          </select>

          <input type="date" class="admin-search" value="2026-03-22">
        </div>

        <div class="status-pills" id="statusTabs">
          <button class="status-pill active" data-status="all">24 All</button>
          <button class="status-pill" data-status="Pending">5 Pending</button>
          <button class="status-pill" data-status="Preparing">7 Preparing</button>
          <button class="status-pill" data-status="Ready">4 Ready</button>
          <button class="status-pill" data-status="Delivered">8 Delivered</button>
        </div>

        <div class="table-wrap">
          <table>
            <thead>
              <tr>
                <th>Order #</th>
                <th>Customer</th>
                <th>Type</th>
                <th>Items</th>
                <th>Total</th>
                <th>Payment</th>
                <th>Pay Status</th>
                <th>Status</th>
                <th>Time</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody id="ordersTableBody"></tbody>
          </table>
        </div>
      </section>
    </main>
  </div>

  <script src="script.js"></script>
  <script>
        document.addEventListener("DOMContentLoaded", () => {
          const mockOrders = [
        {
          id: "FC-2026-0043",
          customer: "Jenny D.",
          email: "jennyd@example.com",
          type: "Delivery",
          items: 3,
          total: 387.50,
          payment: "COD",
          paymentStatus: "Unpaid",
          status: "Pending",
          time: "2:14 PM"
        },
        {
          id: "FC-2026-0041",
          customer: "Carl P.",
          email: "carlp@gmail.com",
          type: "Delivery",
          items: 4,
          total: 450.00,
          payment: "COD",
          paymentStatus: "Unpaid",
          status: "Ready",
          time: "1:20 PM"
        },
        {
          id: "FC-2026-0039",
          customer: "Neil L.",
          email: "neill@email.com",
          type: "Pick-up",
          items: 3,
          total: 310.00,
          payment: "Cash",
          paymentStatus: "Unpaid",
          status: "Delivered",
          time: "12:30 PM"
        },
        {
          id: "FC-2026-0038",
          customer: "Fuji D.",
          email: "fuji@yahoo.com",
          type: "Delivery",
          items: 2,
          total: 245.00,
          payment: "COD",
          paymentStatus: "Paid",
          status: "Preparing",
          time: "11:55 AM"
        },
        {
          id: "FC-2026-0037",
          customer: "Marc G.",
          email: "marcg@mail.com",
          type: "Pick-up",
          items: 5,
          total: 515.00,
          payment: "Cash",
          paymentStatus: "Paid",
          status: "Preparing",
          time: "10:40 AM"
        }
      ];

      const tableBody = document.getElementById("ordersTableBody");
      const orderSearch = document.getElementById("orderSearch");
      const statusFilter = document.getElementById("statusFilter");
      const paymentFilter = document.getElementById("paymentFilter");
      const statusTabs = document.getElementById("statusTabs");

      let activeStatusTab = "all";

      function formatPeso(value) {
        return `₱${Number(value).toFixed(2)}`;
      }

      function renderTable() {
        const searchValue = orderSearch.value.trim().toLowerCase();
        const statusValue = statusFilter.value;
        const paymentValue = paymentFilter.value;

        const filtered = mockOrders.filter(order => {
          const matchesSearch =
            order.id.toLowerCase().includes(searchValue) ||
            order.customer.toLowerCase().includes(searchValue);

          const matchesStatusSelect = statusValue === "all" || order.status === statusValue;
          const matchesPayment = paymentValue === "all" || order.paymentStatus === paymentValue;
          const matchesTab = activeStatusTab === "all" || order.status === activeStatusTab;

          return matchesSearch && matchesStatusSelect && matchesPayment && matchesTab;
        });

        tableBody.innerHTML = filtered.map(order => `
          <tr>
            <td><strong>${order.id}</strong></td>
            <td class="customer-cell">
              <strong>${order.customer}</strong>
              <small>${order.email}</small>
            </td>
            <td class="type-cell">
              ${order.type === "Delivery" ? "🚚 Delivery" : "🛍 Pick-up"}
            </td>
            <td>${order.items}</td>
            <td>${formatPeso(order.total)}</td>
            <td>${order.payment}</td>
            <td>
              <span class="badge ${order.paymentStatus === "Paid" ? "badge-success" : "badge-danger"}">${order.paymentStatus}</span>
            </td>
            <td>
              <select class="status-select" data-order-id="${order.id}">
                <option ${order.status === "Pending" ? "selected" : ""}>Pending</option>
                <option ${order.status === "Preparing" ? "selected" : ""}>Preparing</option>
                <option ${order.status === "Ready" ? "selected" : ""}>Ready</option>
                <option ${order.status === "Delivered" ? "selected" : ""}>Delivered</option>
              </select>
            </td>
            <td>${order.time}</td>
            <td><button class="view-btn" data-view-id="${order.id}">👁</button></td>
          </tr>
        `).join("");
      }

      orderSearch.addEventListener("input", renderTable);
      statusFilter.addEventListener("change", renderTable);
      paymentFilter.addEventListener("change", renderTable);

      statusTabs.addEventListener("click", (event) => {
        const btn = event.target.closest(".status-pill");
        if (!btn) return;

        statusTabs.querySelectorAll(".status-pill").forEach(item => item.classList.remove("active"));
        btn.classList.add("active");
        activeStatusTab = btn.dataset.status;
        renderTable();
      });

      tableBody.addEventListener("change", (event) => {
        const select = event.target.closest(".status-select");
        if (!select) return;
        const order = mockOrders.find(item => item.id === select.dataset.orderId);
        if (order) {
          order.status = select.value;
          alert(`Order ${order.id} status updated to ${order.status}.`);
          renderTable();
        }
      });

      tableBody.addEventListener("click", (event) => {
        const viewBtn = event.target.closest("[data-view-id]");
        if (!viewBtn) return;
        alert(`Viewing order ${viewBtn.dataset.viewId}. Demo only.`);
      });

      renderTable();
    });
  </script>
</body>
</html>