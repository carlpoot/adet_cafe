<?php
require_once "includes/admin-auth.php";
require_once "includes/db.php";

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["action"]) && $_POST["action"] === "update_status") {
    header("Content-Type: application/json");

    try {
        $orderId = (int) ($_POST["order_id"] ?? 0);
        $newStatus = strtolower(trim($_POST["status"] ?? ""));
        $allowed = ["pending", "preparing", "ready", "delivered", "cancelled"];

        if ($orderId <= 0 || !in_array($newStatus, $allowed, true)) {
            throw new Exception("Invalid order status update.");
        }

        $stmt = $conn->prepare("SELECT order_status FROM orders WHERE order_id = :order_id LIMIT 1");
        $stmt->execute([":order_id" => $orderId]);
        $order = $stmt->fetch();

        if (!$order) {
            throw new Exception("Order not found.");
        }

        $oldStatus = $order["order_status"];

        $updateStmt = $conn->prepare("
            UPDATE orders
            SET order_status = :order_status
            WHERE order_id = :order_id
        ");
        $updateStmt->execute([
            ":order_status" => $newStatus,
            ":order_id" => $orderId
        ]);

        $historyStmt = $conn->prepare("
            INSERT INTO order_status_history
            (order_id, updated_by, old_status, new_status, remarks)
            VALUES
            (:order_id, :updated_by, :old_status, :new_status, 'Updated by admin')
        ");
        $historyStmt->execute([
            ":order_id" => $orderId,
            ":updated_by" => (int) $_SESSION["user_id"],
            ":old_status" => $oldStatus,
            ":new_status" => $newStatus
        ]);

        echo json_encode(["success" => true]);
        exit;

    } catch (Throwable $e) {
        http_response_code(400);
        echo json_encode([
            "success" => false,
            "message" => $e->getMessage()
        ]);
        exit;
    }
}

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

function peso($value) {
    return "₱" . number_format((float) $value, 2);
}

function displayStatus($status) {
    return ucfirst($status);
}

function displayPaymentStatus($status) {
    return ucfirst($status ?: "unpaid");
}

function displayPaymentMethod($method) {
    if ($method === "gcash") return "GCash";
    if ($method === "card") return "Card";
    return "Cash";
}

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
    .status-pill.active { background: var(--bg-soft); color: var(--text); border-color: var(--border); }
    .admin-search, .admin-select { width: 100%; min-height: 52px; border: 1px solid var(--border); border-radius: 16px; background: var(--surface); padding: 14px 16px; outline: none; font-family: Arial, Helvetica, sans-serif; }
    .customer-cell strong { display: block; }
    .customer-cell small, .type-cell { font-family: Arial, Helvetica, sans-serif; color: var(--text-muted); }
    .status-select { min-height: 40px; border: 1px solid var(--border); border-radius: 12px; padding: 8px 12px; background: #fff; font-family: Arial, Helvetica, sans-serif; }
    .view-btn { width: 38px; height: 38px; border-radius: 12px; background: var(--bg-soft); border: 1px solid var(--border); }
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
      <nav class="admin-nav">
        <a href="admin-dashboard.php">Dashboard</a>
      </nav>

      <div class="eyebrow" style="color: rgba(255,255,255,0.45); margin-top: 20px;">Orders</div>
      <nav class="admin-nav">
        <a href="admin-orders.php" class="active">Orders <span style="float:right; opacity:0.8;"><?php echo count($orders); ?></span></a>
      </nav>

      <div class="eyebrow" style="color: rgba(255,255,255,0.45); margin-top: 20px;">Catalog</div>
      <nav class="admin-nav">
        <a href="admin-products.php">Products</a>
        <a href="admin-product-form.php">+ Add / Edit Product</a>
      </nav>

      <div class="admin-sidebar-footer">
        <a href="logout.php" class="admin-logout-btn" style="display:grid;place-items:center;">Logout</a>
      </div>
    </aside>

    <main class="admin-content">
      <div class="admin-topbar">
        <div>
          <span class="eyebrow">Admin</span>
          <h1 class="admin-page-title">Orders</h1>
        </div>

        <div class="admin-toolbar-right">
          <a href="index.php" class="btn btn-secondary btn-sm">View Site</a>
          <a href="logout.php" class="btn btn-secondary btn-sm">Logout</a>
        </div>
      </div>

      <section class="orders-shell">
        <div class="status-pills" id="statusTabs">
          <button class="status-pill active" data-status="all">All (<?php echo $statusCounts["all"]; ?>)</button>
          <button class="status-pill" data-status="pending">Pending (<?php echo $statusCounts["pending"]; ?>)</button>
          <button class="status-pill" data-status="preparing">Preparing (<?php echo $statusCounts["preparing"]; ?>)</button>
          <button class="status-pill" data-status="ready">Ready (<?php echo $statusCounts["ready"]; ?>)</button>
          <button class="status-pill" data-status="delivered">Delivered (<?php echo $statusCounts["delivered"]; ?>)</button>
        </div>

        <div class="toolbar-grid">
          <input class="admin-search" type="search" id="orderSearch" placeholder="Search order or customer">
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
            <option value="unpaid">Unpaid</option>
            <option value="paid">Paid</option>
          </select>
          <button class="btn btn-secondary btn-full" type="button" onclick="window.location.reload()">Refresh</button>
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
              <?php if (!$orders): ?>
                <tr>
                  <td colspan="10" style="text-align:center; padding:30px;">No database orders found yet.</td>
                </tr>
              <?php endif; ?>

              <?php foreach ($orders as $order): ?>
                <tr
                  data-search="<?php echo strtolower(htmlspecialchars($order["order_number"] . " " . $order["customer_name"] . " " . ($order["email"] ?? ""))); ?>"
                  data-status="<?php echo htmlspecialchars($order["order_status"]); ?>"
                  data-payment="<?php echo htmlspecialchars($order["payment_status"]); ?>"
                >
                  <td><strong><?php echo htmlspecialchars($order["order_number"]); ?></strong></td>
                  <td class="customer-cell">
                    <strong><?php echo htmlspecialchars($order["customer_name"]); ?></strong>
                    <small><?php echo htmlspecialchars($order["email"] ?? ""); ?></small>
                  </td>
                  <td class="type-cell"><?php echo $order["delivery_type"] === "pickup" ? "🛍 Pick-up" : "🚚 Delivery"; ?></td>
                  <td><?php echo (int) $order["item_count"]; ?></td>
                  <td><?php echo peso($order["total_amount"]); ?></td>
                  <td><?php echo displayPaymentMethod($order["payment_method"] ?? "cod"); ?></td>
                  <td>
                    <span class="badge <?php echo $order["payment_status"] === "paid" ? "badge-success" : "badge-danger"; ?>">
                      <?php echo displayPaymentStatus($order["payment_status"]); ?>
                    </span>
                  </td>
                  <td>
                    <select class="status-select" data-order-id="<?php echo (int) $order["order_id"]; ?>">
                      <?php foreach (["pending", "preparing", "ready", "delivered", "cancelled"] as $status): ?>
                        <option value="<?php echo $status; ?>" <?php echo $order["order_status"] === $status ? "selected" : ""; ?>>
                          <?php echo displayStatus($status); ?>
                        </option>
                      <?php endforeach; ?>
                    </select>
                  </td>
                  <td><?php echo date("h:i A", strtotime($order["created_at"])); ?></td>
                  <td><a class="view-btn" href="order-confirmation.php?id=<?php echo (int) $order["order_id"]; ?>" style="display:grid;place-items:center;">👁</a></td>
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
    document.addEventListener("DOMContentLoaded", () => {
      const orderSearch = document.getElementById("orderSearch");
      const statusFilter = document.getElementById("statusFilter");
      const paymentFilter = document.getElementById("paymentFilter");
      const statusTabs = document.getElementById("statusTabs");
      const tableBody = document.getElementById("ordersTableBody");
      let activeStatusTab = "all";

      function filterRows() {
        const searchValue = orderSearch.value.trim().toLowerCase();
        const statusValue = statusFilter.value;
        const paymentValue = paymentFilter.value;

        tableBody.querySelectorAll("tr[data-status]").forEach(row => {
          const matchesSearch = row.dataset.search.includes(searchValue);
          const matchesStatus = statusValue === "all" || row.dataset.status === statusValue;
          const matchesPayment = paymentValue === "all" || row.dataset.payment === paymentValue;
          const matchesTab = activeStatusTab === "all" || row.dataset.status === activeStatusTab;

          row.style.display = matchesSearch && matchesStatus && matchesPayment && matchesTab ? "" : "none";
        });
      }

      orderSearch.addEventListener("input", filterRows);
      statusFilter.addEventListener("change", filterRows);
      paymentFilter.addEventListener("change", filterRows);

      statusTabs.addEventListener("click", event => {
        const btn = event.target.closest(".status-pill");
        if (!btn) return;

        statusTabs.querySelectorAll(".status-pill").forEach(item => item.classList.remove("active"));
        btn.classList.add("active");
        activeStatusTab = btn.dataset.status;
        filterRows();
      });

      tableBody.addEventListener("change", async event => {
        const select = event.target.closest(".status-select");
        if (!select) return;

        const formData = new FormData();
        formData.append("action", "update_status");
        formData.append("order_id", select.dataset.orderId);
        formData.append("status", select.value);

        try {
          const response = await fetch("admin-orders.php", {
            method: "POST",
            body: formData
          });

          const result = await response.json();

          if (!result.success) {
            throw new Error(result.message || "Status update failed.");
          }

          alert("Order status updated successfully.");
          window.location.reload();
        } catch (error) {
          alert(error.message || "Status update failed.");
          window.location.reload();
        }
      });
    });
  </script>
</body>
</html>
