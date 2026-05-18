<?php
require_once "includes/auth.php";
require_once "includes/db.php";

$userId = (int) $_SESSION["user_id"];
$orderId = isset($_GET["id"]) ? (int) $_GET["id"] : 0;

if ($orderId > 0) {
    $orderStmt = $conn->prepare("
        SELECT o.*, p.payment_method
        FROM orders o
        LEFT JOIN payments p ON o.order_id = p.order_id
        WHERE o.order_id = :order_id
        AND o.user_id = :user_id
        LIMIT 1
    ");
    $orderStmt->execute([
        ":order_id" => $orderId,
        ":user_id" => $userId
    ]);
} else {
    $orderStmt = $conn->prepare("
        SELECT o.*, p.payment_method
        FROM orders o
        LEFT JOIN payments p ON o.order_id = p.order_id
        WHERE o.user_id = :user_id
        ORDER BY o.created_at DESC
        LIMIT 1
    ");
    $orderStmt->execute([":user_id" => $userId]);
}

$order = $orderStmt->fetch();

$items = [];

if ($order) {
    $itemStmt = $conn->prepare("
        SELECT *
        FROM order_items
        WHERE order_id = :order_id
        ORDER BY order_item_id ASC
    ");
    $itemStmt->execute([":order_id" => $order["order_id"]]);
    $items = $itemStmt->fetchAll();
}

function peso($value) {
    return "₱" . number_format((float) $value, 2);
}

function displayPaymentMethod($method) {
    if ($method === "gcash") return "GCash";
    if ($method === "card") return "Card";
    return "Cash on Delivery";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>FurrfectCafe | Order Confirmation</title>
  <link rel="stylesheet" href="style.css">
  <style>
    .confirmation-page { padding: 32px 0 56px; }
    .confirmation-layout { display: grid; grid-template-columns: 0.8fr 1.2fr; gap: 28px; align-items: start; }
    .confirm-card, .details-card { background: var(--surface); border: 1px solid var(--border); border-radius: 28px; box-shadow: var(--shadow-md); padding: 24px; }
    .order-number-box { background: #fbf4ea; border: 1px solid var(--border); border-radius: 22px; padding: 22px; text-align: center; margin: 18px 0; }
    .order-number-box strong { display: block; font-size: clamp(2rem, 4vw, 3rem); line-height: 1; margin-top: 10px; color: var(--primary); }
    .detail-list { display: grid; gap: 14px; font-family: Arial, Helvetica, sans-serif; color: var(--text-muted); margin-top: 18px; }
    .detail-list strong { color: var(--text); }
    .order-items { display: grid; gap: 12px; margin: 16px 0; font-family: Arial, Helvetica, sans-serif; }
    .order-item-row, .info-row { display: flex; justify-content: space-between; gap: 16px; padding: 12px 0; border-bottom: 1px solid var(--border); }
    .total-row { font-size: 1.25rem; color: var(--primary); border-bottom: none; }
    .action-grid { display: grid; gap: 12px; margin-top: 18px; }
    @media (max-width: 820px) { .confirmation-layout { grid-template-columns: 1fr; } }
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
        <a href="orders.php" class="active">Orders</a>
        <a href="profile.php">Profile</a>
      </nav>
      <div class="nav-actions">
        <a href="cart.php" class="cart-pill">🛒 Cart <span class="cart-pill-count" data-cart-count>0</span></a>
        <a href="logout.php" class="btn btn-secondary btn-sm">Logout</a>
      </div>
    </div>
  </header>

  <main class="confirmation-page">
    <div class="container">
      <div class="confirmation-layout">
        <section class="confirm-card">
          <span class="eyebrow">Order Confirmed</span>
          <h1 class="section-title">Thank you!</h1>

          <?php if (!$order): ?>
            <p class="section-text">No recent order was found. Please place an order first.</p>
            <div class="action-grid">
              <a href="menu.php" class="btn btn-primary btn-full">🍽 Go to Menu</a>
            </div>
          <?php else: ?>
            <p class="section-text">Your order has been saved in the database and is now pending preparation.</p>

            <div class="order-number-box">
              <div class="section-text">Your Order Number</div>
              <strong id="orderNumber"><?php echo htmlspecialchars($order["order_number"]); ?></strong>
              <button class="btn btn-secondary btn-sm mt-16" id="copyOrderBtn">📋 Copy</button>
            </div>

            <div class="action-grid">
              <a href="orders.php" class="btn btn-primary btn-full">📦 Track My Order</a>
              <a href="menu.php" class="btn btn-accent btn-full">🍽 Order Again</a>
            </div>
          <?php endif; ?>
        </section>

        <section class="details-card">
          <span class="eyebrow">Order Summary</span>
          <h2 class="form-title">Details</h2>

          <?php if ($order): ?>
            <div class="order-items">
              <?php foreach ($items as $item): ?>
                <div class="order-item-row">
                  <span><?php echo htmlspecialchars($item["product_name"]); ?> × <?php echo (int) $item["quantity"]; ?></span>
                  <strong><?php echo peso($item["subtotal"]); ?></strong>
                </div>
              <?php endforeach; ?>
            </div>

            <div class="info-row">
              <span class="section-text">Delivery fee</span>
              <strong><?php echo peso($order["delivery_fee"]); ?></strong>
            </div>

            <div class="info-row total-row">
              <span>Total</span>
              <strong><?php echo peso($order["total_amount"]); ?></strong>
            </div>

            <div class="detail-list">
              <div><strong>📍 Address:</strong> <?php echo htmlspecialchars($order["delivery_address"] ?: "Pick-up at store"); ?></div>
              <div><strong>💵 Payment:</strong> <?php echo displayPaymentMethod($order["payment_method"] ?? "cod"); ?></div>
              <div><strong>🕒 Estimated time:</strong> <?php echo $order["delivery_type"] === "pickup" ? "Ready for pick-up in 15 to 25 minutes" : "Estimated delivery in 30 to 45 minutes"; ?></div>
              <div><strong>☎ Contact number:</strong> <?php echo htmlspecialchars($order["contact_number"]); ?></div>
            </div>
          <?php else: ?>
            <p class="section-text">No order details available.</p>
          <?php endif; ?>
        </section>
      </div>
    </div>
  </main>

  <script src="script.js"></script>
  <?php if ($order): ?>
  <script>
    document.getElementById("copyOrderBtn").addEventListener("click", async () => {
      const orderNumber = document.getElementById("orderNumber").textContent.trim();

      try {
        await navigator.clipboard.writeText(orderNumber);
        alert("Order number copied.");
      } catch (error) {
        alert(`Order number: ${orderNumber}`);
      }
    });
  </script>
  <?php endif; ?>
</body>
</html>
