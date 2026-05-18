<?php require_once "includes/auth.php"; ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>FurrfectCafe | My Profile</title>
  <link rel="stylesheet" href="style.css">
  <style>
    .profile-page {
      padding: 28px 0 56px;
    }

    .profile-layout {
      display: grid;
      grid-template-columns: 320px 1fr;
      gap: 24px;
      align-items: start;
    }

    .profile-sidebar,
    .profile-card {
      background: var(--surface);
      border: 1px solid var(--border);
      border-radius: 28px;
      box-shadow: var(--shadow-md);
      padding: 22px;
    }

    .profile-avatar-wrap {
      text-align: center;
      padding: 12px 0 6px;
    }

    .profile-avatar {
      width: 88px;
      height: 88px;
      border-radius: 50%;
      margin: 0 auto 14px;
      display: grid;
      place-items: center;
      background: linear-gradient(135deg, var(--accent), #a75f24);
      color: white;
      font-size: 2rem;
    }

    .profile-menu {
      display: grid;
      gap: 10px;
      margin-top: 18px;
    }

    .profile-menu button {
      min-height: 50px;
      padding: 12px 16px;
      border-radius: 16px;
      text-align: left;
      background: var(--bg-soft);
      border: 1px solid var(--border);
      font-family: Arial, Helvetica, sans-serif;
      color: var(--text-muted);
      font-weight: 700;
    }

    .profile-menu button.active {
      background: var(--primary);
      color: white;
      border-color: var(--primary);
    }

    .profile-content {
      display: grid;
      gap: 18px;
    }

    .history-list {
      display: grid;
      gap: 12px;
      font-family: Arial, Helvetica, sans-serif;
    }

    .history-item {
      display: flex;
      justify-content: space-between;
      gap: 14px;
      padding: 14px 16px;
      border: 1px solid var(--border);
      border-radius: 18px;
      background: var(--bg-soft);
      flex-wrap: wrap;
    }

    @media (max-width: 920px) {
      .profile-layout {
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
        <a href="profile.php" class="btn btn-secondary btn-sm active">👤 Profile</a>
      </div>

      <button class="menu-toggle" aria-label="Open menu" aria-expanded="false">☰</button>
    </div>
  </header>

  <main class="profile-page">
    <div class="container">
      <div class="profile-layout">
        <aside class="profile-sidebar">
          <div class="profile-avatar-wrap">
            <div class="profile-avatar" id="profileAvatar">A</div>
            <h2 id="sidebarName">Fuji Denzo</h2>
            <p class="section-text" id="sidebarEmail">fuji@example.com</p>
            <span class="badge badge-success">Member</span>
          </div>

          <div class="profile-menu">
            <button class="active" data-tab="personal">👤 Personal Info</button>
            <button data-tab="address">📍 Delivery Address</button>
            <button data-tab="password">🔒 Change Password</button>
            <button data-tab="history">🧾 Order History</button>
          </div>
        </aside>

        <section class="profile-content">
          <div class="profile-card tab-panel" id="tab-personal">
            <h2 class="form-title">👤 Personal Information</h2>
            <form id="personalForm" class="form-grid form-grid-2">
              <div class="field">
                <label for="fullName">Full Name</label>
                <input class="input" type="text" id="fullName">
              </div>

              <div class="field">
                <label for="contactNumber">Contact Number</label>
                <input class="input" type="text" id="contactNumber">
              </div>

              <div class="field" style="grid-column: 1 / -1;">
                <label for="emailAddress">Email Address</label>
                <input class="input" type="email" id="emailAddress">
              </div>
            </form>
            <button class="btn btn-primary mt-24" id="savePersonalBtn">💾 Save Changes</button>
          </div>

          <div class="profile-card tab-panel hidden" id="tab-address">
            <h2 class="form-title">📍 Delivery Address</h2>
            <form id="addressForm" class="form-grid">
              <div class="field">
                <label for="addressField">Street / House Number</label>
                <input class="input" type="text" id="addressField">
              </div>

              <div class="field">
                <label for="cityField">City</label>
                <input class="input" type="text" id="cityField" value="Legazpi City">
              </div>
            </form>
            <button class="btn btn-primary mt-24" id="saveAddressBtn">📍 Update Address</button>
          </div>

          <div class="profile-card tab-panel hidden" id="tab-password">
            <h2 class="form-title">🔒 Change Password</h2>
            <form id="passwordForm" class="form-grid">
              <div class="field">
                <label for="currentPassword">Current Password</label>
                <input class="input" type="password" id="currentPassword">
              </div>

              <div class="field">
                <label for="newPassword">New Password</label>
                <input class="input" type="password" id="newPassword">
              </div>

              <div class="field">
                <label for="confirmNewPassword">Confirm New Password</label>
                <input class="input" type="password" id="confirmNewPassword">
              </div>
            </form>
            <button class="btn btn-primary mt-24" id="changePasswordBtn">🔐 Update Password</button>
          </div>

          <div class="profile-card tab-panel hidden" id="tab-history">
            <h2 class="form-title">🧾 Order History</h2>
            <div class="history-list" id="historyList"></div>
          </div>
        </section>
      </div>
    </div>
  </main>

  <script src="script.js"></script>
  <script>
    document.addEventListener("DOMContentLoaded", () => {
      const menuButtons = document.querySelectorAll(".profile-menu button");
      const panels = document.querySelectorAll(".tab-panel");

      function getUser() {
        return JSON.parse(localStorage.getItem("furrfectcafe_user") || "{}");
      }

      function saveUser(user) {
        localStorage.setItem("furrfectcafe_user", JSON.stringify(user));
      }

      function loadProfile() {
        const user = getUser();
        const fullName = user.name || "Fuji Denzo";
        const email = user.email || "fuji@example.com";
        const contact = user.contactNumber || "09123456789";
        const address = user.fullAddress || "123 Rizal Street, Barangay 1, Legazpi City";

        document.getElementById("sidebarName").textContent = fullName;
        document.getElementById("sidebarEmail").textContent = email;
        document.getElementById("profileAvatar").textContent = fullName.charAt(0).toUpperCase();

        document.getElementById("fullName").value = fullName;
        document.getElementById("contactNumber").value = contact;
        document.getElementById("emailAddress").value = email;
        document.getElementById("addressField").value = address;
      }

      function loadHistory() {
        const orders = JSON.parse(localStorage.getItem("furrfectcafe_orders") || "[]");
        const historyList = document.getElementById("historyList");

        if (!orders.length) {
          historyList.innerHTML = `<p class="section-text">No order history yet.</p>`;
          return;
        }

        historyList.innerHTML = orders.slice(0, 5).map(order => `
          <div class="history-item">
            <div>
              <strong>${order.id}</strong><br>
              <span class="section-text">${order.date} • ${order.type}</span>
            </div>
            <div>
              <strong>₱${Number(order.total).toFixed(2)}</strong><br>
              <span class="section-text">${order.status}</span>
            </div>
          </div>
        `).join("");
      }

      menuButtons.forEach(button => {
        button.addEventListener("click", () => {
          const tab = button.dataset.tab;
          menuButtons.forEach(btn => btn.classList.remove("active"));
          panels.forEach(panel => panel.classList.add("hidden"));

          button.classList.add("active");
          document.getElementById(`tab-${tab}`).classList.remove("hidden");
        });
      });

      document.getElementById("savePersonalBtn").addEventListener("click", () => {
        const current = getUser();
        const updated = {
          ...current,
          name: document.getElementById("fullName").value.trim(),
          contactNumber: document.getElementById("contactNumber").value.trim(),
          email: document.getElementById("emailAddress").value.trim()
        };
        saveUser(updated);
        loadProfile();
        alert("Personal information updated.");
      });

      document.getElementById("saveAddressBtn").addEventListener("click", () => {
        const current = getUser();
        const address = document.getElementById("addressField").value.trim();
        const city = document.getElementById("cityField").value.trim();
        const updated = {
          ...current,
          fullAddress: city ? `${address}` : address
        };
        saveUser(updated);
        loadProfile();
        alert("Delivery address updated.");
      });

      document.getElementById("changePasswordBtn").addEventListener("click", () => {
        const newPassword = document.getElementById("newPassword").value.trim();
        const confirmNewPassword = document.getElementById("confirmNewPassword").value.trim();

        if (newPassword.length < 8) {
          alert("New password must be at least 8 characters.");
          return;
        }

        if (newPassword !== confirmNewPassword) {
          alert("New passwords do not match.");
          return;
        }

        alert("Password updated. Demo only, no real authentication is connected.");
        document.getElementById("passwordForm").reset();
      });

      loadProfile();
      loadHistory();
    });
  </script>
</body>
</html>