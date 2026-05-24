<?php
require_once "includes/auth.php";
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


$userId = (int) $_SESSION["user_id"];
syncAutomaticOrderStatuses($conn, $userId);

$stmt = $conn->prepare("
    SELECT 
        o.*,
        p.payment_method,
        COUNT(oi.order_item_id) AS item_count
    FROM orders o
    LEFT JOIN payments p ON o.order_id = p.order_id
    LEFT JOIN order_items oi ON o.order_id = oi.order_id
    WHERE o.user_id = :user_id
    GROUP BY o.order_id
    ORDER BY o.created_at DESC
");
$stmt->execute([":user_id" => $userId]);
$orders = $stmt->fetchAll();

$itemStmt = $conn->prepare("
    SELECT *
    FROM order_items
    WHERE order_id = :order_id
    ORDER BY order_item_id ASC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>FurrfectCafe | My Orders</title>
  <link rel="stylesheet" href="style.css">
  <style>
    .orders-page { padding: 28px 0 56px; }
    .orders-stack { display: grid; gap: 18px; }
    .order-card { background: var(--surface); border: 1px solid var(--border); border-radius: 28px; box-shadow: var(--shadow-md); padding: 22px; }
    .order-top { display: flex; justify-content: space-between; gap: 16px; flex-wrap: wrap; margin-bottom: 16px; }
    .order-id { font-size: 1.2rem; margin-bottom: 4px; }
    .order-meta { font-family: Arial, Helvetica, sans-serif; color: var(--text-muted); font-size: 0.92rem; }
    .badge-row { display: flex; gap: 8px; flex-wrap: wrap; }
    .tracker { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 12px; margin: 18px 0; }
    .track-step { text-align: center; position: relative; }
    .track-step::after { content: ""; position: absolute; top: 17px; left: calc(50% + 20px); width: calc(100% - 40px); height: 2px; background: var(--border); z-index: 0; }
    .track-step:last-child::after { display: none; }
    .track-dot { width: 36px; height: 36px; margin: 0 auto 8px; border-radius: 50%; background: var(--surface-2); border: 1px solid var(--border); display: grid; place-items: center; position: relative; z-index: 1; font-family: Arial, Helvetica, sans-serif; font-weight: 700; }
    .track-step.done .track-dot, .track-step.active .track-dot { background: var(--primary); color: white; border-color: var(--primary); }
    .track-step span { font-family: Arial, Helvetica, sans-serif; font-size: 0.85rem; color: var(--text-muted); }
    .items-summary { background: var(--bg-soft); border: 1px solid var(--border); border-radius: 20px; padding: 16px; }
    .item-line, .order-total-line { display: flex; justify-content: space-between; gap: 14px; font-family: Arial, Helvetica, sans-serif; padding: 8px 0; }
    .order-total-line { border-top: 1px solid var(--border); margin-top: 8px; padding-top: 14px; font-weight: 700; color: var(--primary); }
    .order-actions { display: flex; gap: 10px; flex-wrap: wrap; margin-top: 16px; }
    .live-status-note { display: inline-flex; gap: 8px; align-items: center; padding: 10px 14px; border-radius: 999px; background: rgba(75,155,99,0.12); color: var(--success); font-family: Arial, Helvetica, sans-serif; font-weight: 700; margin-top: 10px; }
    @media (max-width: 700px) { .tracker { grid-template-columns: repeat(2, minmax(0, 1fr)); } .track-step::after { display: none; } }
  </style>
</head>
<body>
  <header class="site-header">
    <div class="container navbar">
      <a href="index.php" class="brand"><span class="brand-mark">CAFE</span><span class="brand-name">FurrfectCafe</span></a>
      <nav class="nav-links"><a href="index.php">Home</a><a href="menu.php">Menu</a><a href="orders.php" class="active">Orders</a><a href="profile.php">Profile</a></nav>
      <div class="nav-actions"><a href="cart.php" class="cart-pill">🛒 Cart <span class="cart-pill-count" data-cart-count>0</span></a><a href="logout.php" class="btn btn-secondary btn-sm">Logout</a></div>
    </div>
  </header>

  <main class="orders-page">
    <div class="container">
      <span class="eyebrow">Order History</span>
      <h1 class="section-title">My Orders</h1>
      <p class="section-text">Track your orders saved in the FurrfectCafe database.</p>
      <div class="live-status-note">● Live status updates every 10 seconds</div>

      <section class="orders-stack mt-24">
        <?php if (!$orders): ?>
          <article class="order-card">
            <h3>No orders yet</h3>
            <p class="section-text">Place your first order from the menu.</p>
            <a href="menu.php" class="btn btn-primary mt-16">Go to Menu</a>
          </article>
        <?php endif; ?>

        <?php foreach ($orders as $order): ?>
          <?php
            $itemStmt->execute([":order_id" => $order["order_id"]]);
            $items = $itemStmt->fetchAll();
            $paymentStatus = $order["payment_status"] ?? "unpaid";
          ?>
          <article class="order-card">
            <div class="order-top">
              <div>
                <h2 class="order-id"><?php echo htmlspecialchars($order["order_number"]); ?></h2>
                <div class="order-meta"><?php echo date("M d, Y h:i A", strtotime($order["created_at"])); ?> • <?php echo $order["delivery_type"] === "pickup" ? "Pick-up" : "Delivery"; ?></div>
              </div>
              <div class="badge-row">
                <span class="badge <?php echo statusBadgeClass($order["order_status"]); ?>">● <?php echo labelStatus($order["order_status"]); ?></span>
                <span class="badge <?php echo paymentBadgeClass($paymentStatus); ?>"><?php echo labelStatus($paymentStatus); ?></span>
              </div>
            </div>

            <div class="tracker">
              <?php foreach (["pending", "preparing", "ready", "delivered"] as $i => $step): ?>
                <div class="track-step <?php echo stepClass($order["order_status"], $step); ?>">
                  <div class="track-dot"><?php echo $i + 1; ?></div>
                  <span><?php echo labelStatus($step); ?></span>
                </div>
              <?php endforeach; ?>
            </div>

            <div class="items-summary">
              <?php foreach ($items as $item): ?>
                <div class="item-line"><span><?php echo htmlspecialchars($item["product_name"]); ?> × <?php echo (int) $item["quantity"]; ?></span><strong><?php echo peso($item["subtotal"]); ?></strong></div>
              <?php endforeach; ?>
              <div class="order-total-line"><span>Total</span><strong><?php echo peso($order["total_amount"]); ?></strong></div>
            </div>

            <div class="order-actions">
              <a class="btn btn-secondary btn-sm" href="order-confirmation.php?id=<?php echo (int) $order["order_id"]; ?>">🧾 View Receipt</a>
              <a class="btn btn-primary btn-sm" href="menu.php">↻ Reorder</a>
            </div>
          </article>
        <?php endforeach; ?>
      </section>
    </div>
  </main>

  <script src="script.js"></script>
  <script>setTimeout(() => window.location.reload(), 10000);</script>
</body>
</html>
