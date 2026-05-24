<?php
require_once "includes/admin-auth.php";
require_once "includes/db.php";


function autoStatusConfig() {
    return [
        "pending_seconds" => 20,
        "preparing_seconds" => 60,
        "ready_seconds" => 100
    ];
}

function getAutomaticOrderStatus($createdAt, $currentStatus) {
    if ($currentStatus === "cancelled") {
        return "cancelled";
    }

    $config = autoStatusConfig();
    $createdTimestamp = strtotime($createdAt);

    if (!$createdTimestamp) {
        return $currentStatus ?: "pending";
    }

    $elapsedSeconds = time() - $createdTimestamp;

    if ($elapsedSeconds < $config["pending_seconds"]) {
        return "pending";
    }

    if ($elapsedSeconds < $config["preparing_seconds"]) {
        return "preparing";
    }

    if ($elapsedSeconds < $config["ready_seconds"]) {
        return "ready";
    }

    return "delivered";
}

function syncAutomaticOrderStatuses($conn, $userId = null) {
    $query = "SELECT order_id, order_status, created_at FROM orders WHERE order_status != 'cancelled'";
    $params = [];

    if ($userId !== null) {
        $query .= " AND user_id = :user_id";
        $params[":user_id"] = (int) $userId;
    }

    $stmt = $conn->prepare($query);
    $stmt->execute($params);
    $ordersToCheck = $stmt->fetchAll();

    $updateStmt = $conn->prepare("UPDATE orders SET order_status = :new_status WHERE order_id = :order_id");
    $historyStmt = $conn->prepare("\n        INSERT INTO order_status_history\n        (order_id, updated_by, old_status, new_status, remarks)\n        VALUES\n        (:order_id, NULL, :old_status, :new_status, 'Automatically updated by system timer')\n    ");

    foreach ($ordersToCheck as $orderToCheck) {
        $oldStatus = $orderToCheck["order_status"];
        $newStatus = getAutomaticOrderStatus($orderToCheck["created_at"], $oldStatus);

        if ($newStatus !== $oldStatus) {
            $updateStmt->execute([
                ":new_status" => $newStatus,
                ":order_id" => (int) $orderToCheck["order_id"]
            ]);

            $historyStmt->execute([
                ":order_id" => (int) $orderToCheck["order_id"],
                ":old_status" => $oldStatus,
                ":new_status" => $newStatus
            ]);
        }
    }
}

function peso($value) {
    return "₱" . number_format((float) $value, 2);
}

function labelStatus($status) {
    return ucfirst((string) $status);
}

function statusBadgeClass($status) {
    return [
        "pending" => "badge-warning",
        "preparing" => "badge-accent",
        "ready" => "badge-soft",
        "delivered" => "badge-success",
        "cancelled" => "badge-danger"
    ][$status] ?? "badge-soft";
}

function paymentBadgeClass($status) {
    return $status === "paid" ? "badge-success" : "badge-danger";
}

function stepClass($orderStatus, $step) {
    $steps = ["pending", "preparing", "ready", "delivered"];
    $current = array_search($orderStatus, $steps, true);
    $index = array_search($step, $steps, true);

    if ($current === false) {
        $current = 0;
    }

    if ($index < $current) {
        return "done";
    }

    if ($index === $current) {
        return "active";
    }

    return "";
}


syncAutomaticOrderStatuses($conn);

$stmt = $conn->prepare("
    SELECT
        o.*,
        u.email,
        p.payment_method,
        COUNT(oi.order_item_id) AS item_count
    FROM orders o
    LEFT JOIN users u ON o.user_id = u.user_id
    LEFT JOIN payments p ON o.order_id = p.order_id
    LEFT JOIN order_items oi ON o.order_id = oi.order_id
    GROUP BY o.order_id
    ORDER BY o.created_at DESC
");
$stmt->execute();
$orders = $stmt->fetchAll();

$statusCounts = [
    "all" => count($orders),
    "pending" => 0,
    "preparing" => 0,
    "ready" => 0,
    "delivered" => 0
];

foreach ($orders as $order) {
    if (isset($statusCounts[$order["order_status"]])) {
        $statusCounts[$order["order_status"]]++;
    }
}

function displayPaymentMethod($method) {
    if ($method === "gcash") return "GCash";
    if ($method === "card") return "Card";
    return "Cash";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>FurrfectCafe Admin | Orders</title>
  <link rel="stylesheet" href="style.css">
  <style>
    .orders-shell { background: var(--surface); border: 1px solid var(--border); border-radius: 26px; box-shadow: var(--shadow-sm); padding: 18px; }
    .toolbar-grid { display: grid; grid-template-columns: 1.3fr 0.8fr 0.8fr 0.7fr; gap: 12px; margin-bottom: 14px; }
    .status-pills { display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 16px; }
    .status-pill { min-height: 40px; padding: 10px 14px; border-radius: 999px; border: 1px solid var(--border); background: #fff; font-family: Arial, Helvetica, sans-serif; color: var(--text-muted); font-weight: 700; }
    .status-pill.active { background: var(--primary); color: white; border-color: var(--primary); }
    .admin-search, .admin-select { width: 100%; min-height: 52px; border: 1px solid var(--border); border-radius: 16px; background: var(--surface); padding: 14px 16px; outline: none; font-family: Arial, Helvetica, sans-serif; }
    .customer-cell strong { display: block; }
    .customer-cell small, .type-cell { font-family: Arial, Helvetica, sans-serif; color: var(--text-muted); }
    .view-btn { width: 38px; height: 38px; border-radius: 12px; background: var(--bg-soft); border: 1px solid var(--border); display: inline-grid; place-items: center; }
    .auto-status { display: inline-flex; align-items: center; gap: 8px; font-family: Arial, Helvetica, sans-serif; color: var(--success); background: rgba(75,155,99,0.12); border: 1px solid rgba(75,155,99,0.22); padding: 9px 12px; border-radius: 999px; font-size: 0.88rem; }
    .status-now { display: inline-flex; align-items: center; gap: 8px; }
    @media (max-width: 1100px) { .toolbar-grid { grid-template-columns: 1fr 1fr; } }
    @media (max-width: 700px) { .toolbar-grid { grid-template-columns: 1fr; } }
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
      <nav class="admin-nav"><a href="admin-dashboard.php">Dashboard</a></nav>

      <div class="eyebrow" style="color: rgba(255,255,255,0.45); margin-top: 20px;">Orders</div>
      <nav class="admin-nav"><a href="admin-orders.php" class="active">Orders <span style="float:right; opacity:0.8;"><?php echo count($orders); ?></span></a></nav>

      <div class="eyebrow" style="color: rgba(255,255,255,0.45); margin-top: 20px;">Catalog</div>
      <nav class="admin-nav">
        <a href="admin-products.php">Products</a>
        <a href="admin-product-form.php">+ Add / Edit Product</a>
      </nav>

      <div class="admin-sidebar-footer">
        <div class="admin-user-mini">
          <div class="admin-user-avatar">A</div>
          <div><strong style="display:block;">Admin</strong><span style="font-family:Arial, Helvetica, sans-serif; color:rgba(255,255,255,0.65); font-size:0.9rem;">Administrator</span></div>
        </div>
        <a href="logout.php" class="admin-logout-btn" style="display:grid;place-items:center;">Logout</a>
      </div>
    </aside>

    <main class="admin-content">
      <div class="admin-topbar">
        <div>
          <span class="eyebrow">Orders</span>
          <h1 class="admin-page-title">☰ Order Status Monitor</h1>
          <p class="section-text">Statuses update automatically based on order time. This page refreshes every 10 seconds.</p>
        </div>
        <div class="admin-toolbar-right">
          <span class="auto-status">● Auto status active</span>
          <a href="index.php" class="btn btn-secondary btn-sm">View Site</a>
          <a href="logout.php" class="btn btn-secondary btn-sm">Logout</a>
        </div>
      </div>

      <section class="orders-shell">
        <div class="status-pills" id="statusPills">
          <button type="button" class="status-pill active" data-status="all">All (<?php echo $statusCounts["all"]; ?>)</button>
          <button type="button" class="status-pill" data-status="pending">Pending (<?php echo $statusCounts["pending"]; ?>)</button>
          <button type="button" class="status-pill" data-status="preparing">Preparing (<?php echo $statusCounts["preparing"]; ?>)</button>
          <button type="button" class="status-pill" data-status="ready">Ready (<?php echo $statusCounts["ready"]; ?>)</button>
          <button type="button" class="status-pill" data-status="delivered">Delivered (<?php echo $statusCounts["delivered"]; ?>)</button>
        </div>

        <div class="toolbar-grid">
          <input type="search" class="admin-search" id="orderSearch" placeholder="Search order or customer">
          <select class="admin-select" id="statusFilter">
            <option value="all">All Statuses</option>
            <option value="pending">Pending</option>
            <option value="preparing">Preparing</option>
            <option value="ready">Ready</option>
            <option value="delivered">Delivered</option>
            <option value="cancelled">Cancelled</option>
          </select>
          <select class="admin-select" id="paymentFilter">
            <option value="all">All Payments</option>
            <option value="paid">Paid</option>
            <option value="unpaid">Unpaid</option>
          </select>
          <button type="button" class="btn btn-secondary btn-full" id="refreshBtn">Refresh</button>
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
                <th>Payment Status</th>
                <th>Order Status</th>
                <th>Time</th>
                <th>View</th>
              </tr>
            </thead>
            <tbody id="ordersTableBody">
              <?php foreach ($orders as $order): ?>
                <?php
                  $paymentStatus = $order["payment_status"] ?: "unpaid";
                  $searchText = strtolower($order["order_number"] . " " . $order["customer_name"] . " " . ($order["email"] ?? ""));
                ?>
                <tr data-status="<?php echo htmlspecialchars($order["order_status"]); ?>" data-payment="<?php echo htmlspecialchars($paymentStatus); ?>" data-search="<?php echo htmlspecialchars($searchText); ?>">
                  <td><strong><?php echo htmlspecialchars($order["order_number"]); ?></strong></td>
                  <td class="customer-cell"><strong><?php echo htmlspecialchars($order["customer_name"]); ?></strong><small><?php echo htmlspecialchars($order["email"] ?? "No email"); ?></small></td>
                  <td class="type-cell"><?php echo $order["delivery_type"] === "pickup" ? "☕ Pick-up" : "🚚 Delivery"; ?></td>
                  <td><?php echo (int) $order["item_count"]; ?></td>
                  <td><?php echo peso($order["total_amount"]); ?></td>
                  <td><?php echo displayPaymentMethod($order["payment_method"] ?? "cod"); ?></td>
                  <td><span class="badge <?php echo paymentBadgeClass($paymentStatus); ?>"><?php echo labelStatus($paymentStatus); ?></span></td>
                  <td><span class="badge <?php echo statusBadgeClass($order["order_status"]); ?> status-now">● <?php echo labelStatus($order["order_status"]); ?></span></td>
                  <td><?php echo date("h:i A", strtotime($order["created_at"])); ?></td>
                  <td><a class="view-btn" href="order-confirmation.php?id=<?php echo (int) $order["order_id"]; ?>">👁</a></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </section>
    </main>
  </div>

  <script src="script.js"></script>
  <script>
    const rows = Array.from(document.querySelectorAll("#ordersTableBody tr"));
    const orderSearch = document.getElementById("orderSearch");
    const statusFilter = document.getElementById("statusFilter");
    const paymentFilter = document.getElementById("paymentFilter");
    const statusPills = document.getElementById("statusPills");
    const refreshBtn = document.getElementById("refreshBtn");

    function filterRows() {
      const searchValue = orderSearch.value.trim().toLowerCase();
      const statusValue = statusFilter.value;
      const paymentValue = paymentFilter.value;

      rows.forEach(row => {
        const matchesSearch = row.dataset.search.includes(searchValue);
        const matchesStatus = statusValue === "all" || row.dataset.status === statusValue;
        const matchesPayment = paymentValue === "all" || row.dataset.payment === paymentValue;
        row.style.display = matchesSearch && matchesStatus && matchesPayment ? "" : "none";
      });
    }

    orderSearch.addEventListener("input", filterRows);
    statusFilter.addEventListener("change", filterRows);
    paymentFilter.addEventListener("change", filterRows);

    statusPills.addEventListener("click", event => {
      const pill = event.target.closest("[data-status]");
      if (!pill) return;
      document.querySelectorAll(".status-pill").forEach(item => item.classList.remove("active"));
      pill.classList.add("active");
      statusFilter.value = pill.dataset.status;
      filterRows();
    });

    refreshBtn.addEventListener("click", () => window.location.reload());
    setTimeout(() => window.location.reload(), 10000);
  </script>
</body>
</html>
