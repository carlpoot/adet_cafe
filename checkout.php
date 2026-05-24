<?php
require_once "includes/auth.php";
require_once "includes/db.php";

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["action"]) && $_POST["action"] === "create_order") {
    header("Content-Type: application/json");

    try {
        $userId = (int) $_SESSION["user_id"];
        $customerName = trim($_POST["full_name"] ?? "");
        $contactNumber = trim($_POST["contact_number"] ?? "");
        $deliveryAddress = trim($_POST["delivery_address"] ?? "");
        $orderNotes = trim($_POST["order_notes"] ?? "");
        $deliveryType = ($_POST["delivery_type"] ?? "delivery") === "pickup" ? "pickup" : "delivery";
        $scheduleType = ($_POST["schedule_type"] ?? "now") === "preorder" ? "preorder" : "now";
        $paymentMethod = "cod";
        $cartJson = $_POST["cart_json"] ?? "[]";
        $cartItems = json_decode($cartJson, true);

        if (!$customerName || !$contactNumber || !$deliveryAddress) {
            throw new Exception("Please complete your name, contact number, and address.");
        }

        if (!is_array($cartItems) || count($cartItems) === 0) {
            throw new Exception("Your cart is empty. Please add an item first.");
        }

        $validItems = [];
        $subtotal = 0;

        $productStmt = $conn->prepare("
            SELECT product_id, product_name, price 
            FROM products 
            WHERE product_id = :product_id 
            AND is_available = 1
            LIMIT 1
        ");

        foreach ($cartItems as $item) {
            $productId = (int) ($item["productId"] ?? $item["id"] ?? 0);
            $quantity = max(1, (int) ($item["quantity"] ?? 1));

            if ($productId <= 0) {
                continue;
            }

            $productStmt->execute([":product_id" => $productId]);
            $product = $productStmt->fetch();

            if (!$product) {
                continue;
            }

            $unitPrice = (float) $product["price"];
            $lineSubtotal = $unitPrice * $quantity;
            $subtotal += $lineSubtotal;

            $validItems[] = [
                "product_id" => (int) $product["product_id"],
                "product_name" => $product["product_name"],
                "quantity" => $quantity,
                "unit_price" => $unitPrice,
                "subtotal" => $lineSubtotal
            ];
        }

        if (count($validItems) === 0) {
            throw new Exception("The items in your cart could not be found in the database.");
        }

        $deliveryFee = $deliveryType === "delivery" ? 50.00 : 0.00;
        $totalAmount = $subtotal + $deliveryFee;

        $conn->beginTransaction();

        do {
            $orderNumber = "FC-" . date("Y") . "-" . random_int(1000, 9999);
            $checkStmt = $conn->prepare("SELECT order_id FROM orders WHERE order_number = :order_number LIMIT 1");
            $checkStmt->execute([":order_number" => $orderNumber]);
        } while ($checkStmt->fetch());

        $orderStmt = $conn->prepare("
            INSERT INTO orders
            (user_id, order_number, customer_name, contact_number, delivery_type, delivery_address, order_notes, schedule_type, subtotal, discount_amount, delivery_fee, total_amount, order_status, payment_status)
            VALUES
            (:user_id, :order_number, :customer_name, :contact_number, :delivery_type, :delivery_address, :order_notes, :schedule_type, :subtotal, 0.00, :delivery_fee, :total_amount, 'pending', 'unpaid')
        ");

        $orderStmt->execute([
            ":user_id" => $userId,
            ":order_number" => $orderNumber,
            ":customer_name" => $customerName,
            ":contact_number" => $contactNumber,
            ":delivery_type" => $deliveryType,
            ":delivery_address" => $deliveryAddress,
            ":order_notes" => $orderNotes ?: null,
            ":schedule_type" => $scheduleType,
            ":subtotal" => $subtotal,
            ":delivery_fee" => $deliveryFee,
            ":total_amount" => $totalAmount
        ]);

        $orderId = (int) $conn->lastInsertId();

        $itemStmt = $conn->prepare("
            INSERT INTO order_items
            (order_id, product_id, product_name, size_name, selected_addons, quantity, unit_price, addon_total, subtotal)
            VALUES
            (:order_id, :product_id, :product_name, NULL, NULL, :quantity, :unit_price, 0.00, :subtotal)
        ");

        foreach ($validItems as $item) {
            $itemStmt->execute([
                ":order_id" => $orderId,
                ":product_id" => $item["product_id"],
                ":product_name" => $item["product_name"],
                ":quantity" => $item["quantity"],
                ":unit_price" => $item["unit_price"],
                ":subtotal" => $item["subtotal"]
            ]);
        }

        $paymentStmt = $conn->prepare("
            INSERT INTO payments
            (order_id, payment_method, amount, payment_status)
            VALUES
            (:order_id, :payment_method, :amount, 'pending')
        ");

        $paymentStmt->execute([
            ":order_id" => $orderId,
            ":payment_method" => $paymentMethod,
            ":amount" => $totalAmount
        ]);

        $historyStmt = $conn->prepare("
            INSERT INTO order_status_history
            (order_id, updated_by, old_status, new_status, remarks)
            VALUES
            (:order_id, :updated_by, NULL, 'pending', 'Order placed by customer')
        ");

        $historyStmt->execute([
            ":order_id" => $orderId,
            ":updated_by" => $userId
        ]);

        $conn->commit();

        echo json_encode([
            "success" => true,
            "order_id" => $orderId,
            "order_number" => $orderNumber
        ]);
        exit;

    } catch (Throwable $e) {
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }

        http_response_code(400);
        echo json_encode([
            "success" => false,
            "message" => $e->getMessage()
        ]);
        exit;
    }
}
?>

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
            <p class="payment-note">Cash on Delivery is currently available. Your order will be saved in the database after checkout.</p>
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

          <div class="form-help mt-16" id="checkoutMessage" aria-live="polite"></div>
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
      const deliveryOptions = document.getElementById("deliveryOptions");
      const scheduleOptions = document.getElementById("scheduleOptions");
      const placeOrderBtn = document.getElementById("placeOrderBtn");
      const checkoutMessage = document.getElementById("checkoutMessage");

      let orderType = "Delivery";
      let scheduleType = "Order Now";

      function formatPeso(value) {
        return `₱${Number(value).toFixed(2)}`;
      }

      function getCart() {
        if (window.FurrfectCafe && typeof window.FurrfectCafe.getCart === "function") {
          return window.FurrfectCafe.getCart();
        }

        return JSON.parse(localStorage.getItem("furrfectcafe_cart") || "[]");
      }

      function clearCart() {
        if (window.FurrfectCafe && typeof window.FurrfectCafe.clearCart === "function") {
          window.FurrfectCafe.clearCart();
          return;
        }

        localStorage.removeItem("furrfectcafe_cart");
      }

      function getUser() {
        return JSON.parse(localStorage.getItem("furrfectcafe_user") || "{}");
      }

      function getOrderTotals() {
        const cart = getCart();
        const subtotal = cart.reduce((sum, item) => sum + (Number(item.price) * Number(item.quantity)), 0);
        const deliveryFee = orderType === "Delivery" ? 50 : 0;
        const total = subtotal + deliveryFee;

        return { subtotal, deliveryFee, total };
      }

      function renderSummary() {
        const cart = getCart();

        if (!cart.length) {
          summaryItems.innerHTML = `
            <p class="section-text">Your cart is empty. Please add an item first.</p>
            <a href="menu.php" class="btn btn-secondary btn-full mt-16">Go to Menu</a>
          `;

          subtotalEl.textContent = formatPeso(0);
          deliveryEl.textContent = formatPeso(0);
          totalEl.textContent = formatPeso(0);
          placeOrderBtn.disabled = true;
          placeOrderBtn.style.opacity = "0.6";
          return;
        }

        placeOrderBtn.disabled = false;
        placeOrderBtn.style.opacity = "1";

        const totals = getOrderTotals();

        summaryItems.innerHTML = cart.map(item => `
          <div class="summary-item">
            <span>${item.name} × ${item.quantity}</span>
            <strong>${formatPeso(Number(item.price) * Number(item.quantity))}</strong>
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

        document.getElementById("fullName").value = user.name || "";
        document.getElementById("contactNumber").value = user.contactNumber || "";
        document.getElementById("streetAddress").value = user.fullAddress || "";
      }

      function showCheckoutMessage(message, type = "info") {
        if (!checkoutMessage) return;
        checkoutMessage.textContent = message;
        checkoutMessage.style.color = type === "error" ? "var(--danger)" : "var(--text-muted)";
      }

      async function placeDatabaseOrder() {
        const cart = getCart();
        showCheckoutMessage("");

        if (!cart.length) {
          window.location.href = "menu.php";
          return;
        }

        const fullName = document.getElementById("fullName").value.trim();
        const contactNumber = document.getElementById("contactNumber").value.trim();
        const streetAddress = document.getElementById("streetAddress").value.trim();
        const orderNotes = document.getElementById("orderNotes").value.trim();

        if (!fullName) {
          showCheckoutMessage("Please enter your full name.", "error");
          document.getElementById("fullName").focus();
          return;
        }

        if (!contactNumber) {
          showCheckoutMessage("Please enter your contact number.", "error");
          document.getElementById("contactNumber").focus();
          return;
        }

        if (!streetAddress) {
          showCheckoutMessage("Please enter your address.", "error");
          document.getElementById("streetAddress").focus();
          return;
        }

        const formData = new FormData();
        formData.append("action", "create_order");
        formData.append("full_name", fullName);
        formData.append("contact_number", contactNumber);
        formData.append("delivery_address", streetAddress);
        formData.append("order_notes", orderNotes);
        formData.append("delivery_type", orderType === "Pick-up" ? "pickup" : "delivery");
        formData.append("schedule_type", scheduleType === "Pre-order" ? "preorder" : "now");
        formData.append("cart_json", JSON.stringify(cart));

        placeOrderBtn.disabled = true;
        placeOrderBtn.textContent = "Saving order...";
        showCheckoutMessage("Saving your order...");

        try {
          const response = await fetch("checkout.php", {
            method: "POST",
            body: formData
          });

          const result = await response.json();

          if (!result.success) {
            throw new Error(result.message || "Order could not be saved.");
          }

          clearCart();
          window.location.href = `order-confirmation.php?id=${result.order_id}`;
        } catch (error) {
          showCheckoutMessage(error.message || "Something went wrong while saving the order.", "error");
          placeOrderBtn.disabled = false;
          placeOrderBtn.textContent = "✔ Place Order";
        }
      }

      placeOrderBtn.addEventListener("click", placeDatabaseOrder);

      prefillUser();
      renderSummary();
    });
  </script>
</body>
</html>
