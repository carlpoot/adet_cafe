<?php require_once "includes/admin-auth.php"; ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>FurrfectCafe Admin | Dashboard</title>
  <link rel="stylesheet" href="style.css">
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
        <a href="admin-dashboard.php" class="active">Dashboard</a>
      </nav>

      <div class="eyebrow" style="color: rgba(255,255,255,0.45); margin-top: 20px;">Orders</div>
      <nav class="admin-nav">
        <a href="admin-orders.php">Orders <span style="float:right; opacity:0.8;">5</span></a>
      </nav>

      <div class="eyebrow" style="color: rgba(255,255,255,0.45); margin-top: 20px;">Catalog</div>
      <nav class="admin-nav">
        <a href="admin-products.php">Products</a>
        <a href="admin-product-form.php">+ Add / Edit Product</a>
      </nav>

      <div class="admin-sidebar-footer">
        <div class="admin-user-mini">
          <div class="admin-user-avatar">A</div>
          <div>
            <strong style="display:block;">Admin</strong>
            <span style="font-family:Arial, Helvetica, sans-serif; color:rgba(255,255,255,0.65); font-size:0.9rem;">Administrator</span>
          </div>
        </div>

        <a href="logout.php" class="admin-logout-btn">Logout</a>
      </div>
    </aside>

    <main class="admin-content">
      <div class="admin-topbar">
        <h1 class="admin-page-title">☰ Dashboard</h1>

        <div class="admin-toolbar-right">
          <span>Today: Sun, Mar 22 2026</span>
          <a href="index.php" class="btn btn-secondary btn-sm">View Site</a>
          <a href="logout.php" class="btn btn-secondary btn-sm">Logout</a>
        </div>
      </div>

      <section class="dashboard-stats-grid">
        <article class="dashboard-stat-card">
          <div class="dashboard-stat-icon orders">🧾</div>
          <div>
            <div class="dashboard-stat-value">24</div>
            <div class="dashboard-stat-label">Orders Today</div>
            <div class="dashboard-stat-subtext">5 more than yesterday</div>
          </div>
        </article>

        <article class="dashboard-stat-card">
          <div class="dashboard-stat-icon pending">🕒</div>
          <div>
            <div class="dashboard-stat-value">5</div>
            <div class="dashboard-stat-label">Pending Orders</div>
            <div class="dashboard-stat-subtext">Needs attention</div>
          </div>
        </article>

        <article class="dashboard-stat-card">
          <div class="dashboard-stat-icon customers">👥</div>
          <div>
            <div class="dashboard-stat-value">87</div>
            <div class="dashboard-stat-label">Total Customers</div>
            <div class="dashboard-stat-subtext">4 new this week</div>
          </div>
        </article>
      </section>

      <section class="dashboard-main-grid">
        <article class="admin-panel-card">
          <div class="admin-panel-header">
            <h2 class="form-title" style="margin:0;">Recent Orders</h2>
            <a href="admin-orders.php" class="btn btn-secondary btn-sm">View All</a>
          </div>

          <div class="table-wrap">
            <table>
              <thead>
                <tr>
                  <th>Order #</th>
                  <th>Customer</th>
                  <th>Total</th>
                  <th>Status</th>
                </tr>
              </thead>

              <tbody>
                <tr>
                  <td>FC-2026-0043</td>
                  <td>Jenny D.</td>
                  <td>₱387.50</td>
                  <td><span class="badge badge-warning">Pending</span></td>
                </tr>

                <tr>
                  <td>FC-2026-0042</td>
                  <td>Carl P.</td>
                  <td>₱215.00</td>
                  <td><span class="badge badge-accent">Preparing</span></td>
                </tr>

                <tr>
                  <td>FC-2026-0041</td>
                  <td>Neil L.</td>
                  <td>₱450.00</td>
                  <td><span class="badge badge-soft">Ready</span></td>
                </tr>

                <tr>
                  <td>FC-2026-0040</td>
                  <td>Gab C.</td>
                  <td>₱175.00</td>
                  <td><span class="badge badge-success">Delivered</span></td>
                </tr>

                <tr>
                  <td>FC-2026-0039</td>
                  <td>Vice G.</td>
                  <td>₱310.00</td>
                  <td><span class="badge badge-success">Delivered</span></td>
                </tr>
              </tbody>
            </table>
          </div>
        </article>

        <div class="admin-side-stack">
          <article class="admin-panel-card">
            <div class="admin-panel-header">
              <h2 class="form-title" style="margin:0;">Top Products Today</h2>
            </div>

            <div class="dashboard-products-list">
              <div class="dashboard-product-row">
                <div>
                  <strong>Signature Cat Latte</strong>
                  <small>Hot Drinks</small>
                </div>
                <div style="text-align:right;">
                  <strong>₱1,190</strong>
                  <small>14 orders</small>
                </div>
              </div>

              <div class="dashboard-product-row">
                <div>
                  <strong>Matcha Cloud</strong>
                  <small>Cold Drinks</small>
                </div>
                <div style="text-align:right;">
                  <strong>₱1,210</strong>
                  <small>11 orders</small>
                </div>
              </div>

              <div class="dashboard-product-row">
                <div>
                  <strong>Matcha Cheesecake</strong>
                  <small>Pastries</small>
                </div>
                <div style="text-align:right;">
                  <strong>₱1,080</strong>
                  <small>9 orders</small>
                </div>
              </div>

              <div class="dashboard-product-row">
                <div>
                  <strong>Paw Print Cupcake</strong>
                  <small>Pastries</small>
                </div>
                <div style="text-align:right;">
                  <strong>₱455</strong>
                  <small>7 orders</small>
                </div>
              </div>
            </div>
          </article>

          <article class="admin-panel-card">
            <h2 class="form-title" style="margin-top:0;">Quick Actions</h2>

            <div class="admin-quick-actions">
              <a href="admin-product-form.php" class="btn btn-primary btn-full">+ Add New Product</a>
              <a href="admin-orders.php" class="btn btn-secondary btn-full">🕒 View Pending Orders</a>
            </div>
          </article>
        </div>
      </section>
    </main>
  </div>

  <script src="script.js"></script>
</body>
</html>