<?php require_once "includes/auth.php"; ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>FurrfectCafe | Home</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>

  <header class="site-header">
    <div class="container navbar">
      <a href="index.php" class="brand" aria-label="FurrfectCafe home">
        <span class="brand-mark">CAFE</span>
        <span class="brand-name">FurrfectCafe</span>
      </a>

      <nav class="nav-links" aria-label="Primary navigation">
        <a href="index.php" class="active">Home</a>
        <a href="menu.php">Menu</a>
        <a href="orders.php">My Orders</a>
        <a href="#how-it-works">How it Works</a>
      </nav>

      <div class="nav-actions">
        <a href="cart.php" class="cart-pill" aria-label="Cart">
          <span>🛒 Cart</span>
          <span class="cart-pill-count" data-cart-count>0</span>
        </a>

        <a href="profile.php" class="btn btn-secondary btn-sm">👤 Profile</a>

        <button type="button" class="btn btn-secondary btn-sm" data-customer-logout>
          Logout
        </button>
      </div>

      <button class="menu-toggle" aria-label="Open menu" aria-expanded="false">☰</button>
    </div>
  </header>

  <section class="hero">
    <div class="container">
      <div class="hero-shell">
        <div class="hero-grid">
          <div>
            <div class="hero-badge">📍 Now delivering in Legazpi City</div>

            <h1 class="hero-title">Good food, great <em>vibes</em>, happy cats.</h1>

            <p class="hero-text">
              Order your favorite drinks and bites from FurrfectCafe. Pick up or get it delivered,
              fast, fresh, and furrfect.
            </p>

            <div class="hero-actions">
              <a href="menu.php" class="btn btn-primary">🍽️ Order Now</a>
              <a href="#how-it-works" class="btn btn-secondary">How it works →</a>
            </div>
          </div>

          <div>
            <div class="hero-card">
              <h3>🔥 Today's Picks</h3>

              <div class="pick-list" id="todaysPicks"></div>

              <div class="order-mini">
                <strong>Order FC-2026-0042</strong><br>
                <span style="font-family: Arial, Helvetica, sans-serif; color: var(--success);">● Out for delivery</span>
              </div>
            </div>
          </div>
        </div>

        <div class="info-strip">
          <ul>
            <li>🚚 Free delivery on orders ₱300+</li>
            <li>🕘 Open daily 8AM - 9PM</li>
            <li>📦 Pre-order your faves now</li>
          </ul>
        </div>
      </div>
    </div>
  </section>

  <section class="section">
    <div class="container split-layout">
      <div>
        <span class="eyebrow">Browse by Category</span>

        <div class="space-between mt-16">
          <div>
            <h2 class="section-title">What are you <em>craving?</em></h2>
            <p class="section-text">
              Jump straight to your favorite drinks, pastries, or all-day café bites.
            </p>
          </div>

          <a href="menu.php" class="btn btn-accent btn-sm">View Full Menu →</a>
        </div>

        <div class="grid grid-2 mt-32" id="homeCategories"></div>
      </div>

      <aside class="home-featured-clean">
        <span class="eyebrow">Featured Menu</span>
        <h2 class="section-title" style="color:white;">Today’s cozy picks</h2>
        <p class="section-text">
          A smaller preview of bestsellers so the homepage feels cleaner and easier to scan.
        </p>

        <div class="featured-mini-list">
          <a href="product.php?id=1" class="featured-mini-item">
            <img src="https://images.unsplash.com/photo-1509042239860-f550ce710b93?auto=format&fit=crop&w=300&q=80" alt="Signature Cat Latte">
            <div>
              <strong>Signature Cat Latte</strong>
              <span>Rich espresso with cat latte art</span>
            </div>
            <div class="mini-price">₱85.00</div>
          </a>

          <a href="product.php?id=2" class="featured-mini-item">
            <img src="https://images.unsplash.com/photo-1517705008128-361805f42e86?auto=format&fit=crop&w=300&q=80" alt="Matcha Cloud">
            <div>
              <strong>Matcha Cloud</strong>
              <span>Premium matcha with cream cloud</span>
            </div>
            <div class="mini-price">₱110.00</div>
          </a>

          <a href="product.php?id=3" class="featured-mini-item">
            <img src="https://images.unsplash.com/photo-1563729784474-d77dbb933a9e?auto=format&fit=crop&w=300&q=80" alt="Matcha Cheesecake">
            <div>
              <strong>Matcha Cheesecake</strong>
              <span>Creamy cheesecake with matcha finish</span>
            </div>
            <div class="mini-price">₱120.00</div>
          </a>
        </div>

        <div class="featured-mini-actions">
          <a href="menu.php" class="btn btn-accent btn-sm">Browse Menu</a>
          <a href="menu.php?category=pastries" class="btn btn-secondary btn-sm">View Pastries</a>
        </div>
      </aside>
    </div>
  </section>

  <section class="section-sm">
    <div class="container">
      <div class="space-between">
        <div>
          <span class="eyebrow">Fan Favorites</span>
          <h2 class="section-title">Bestsellers worth the hype</h2>
          <p class="section-text">Crafted for comfort, made for cravings, and perfect for café dates.</p>
        </div>

        <a href="menu.php" class="btn btn-secondary">See full menu →</a>
      </div>

      <div class="grid grid-3 mt-32" id="bestsellerGrid"></div>
    </div>
  </section>

  <section class="section" id="how-it-works">
    <div class="container">
      <div class="text-center">
        <span class="eyebrow">How it Works</span>
        <h2 class="section-title">Order in three easy steps</h2>
        <p class="section-text" style="max-width: 700px; margin: 0 auto 34px;">
          Browse the menu, add your favorites, and enjoy a cozy café experience from home or on the go.
        </p>
      </div>

      <div class="grid grid-3">
        <article class="card card-pad">
          <div class="category-icon">1</div>
          <h3>Pick your favorites</h3>
          <p class="section-text">Choose from hot drinks, cold drinks, pastries, and all-day bites.</p>
        </article>

        <article class="card card-pad">
          <div class="category-icon">2</div>
          <h3>Add to cart</h3>
          <p class="section-text">Build your order with realistic front-end cart behavior for your demo flow.</p>
        </article>

        <article class="card card-pad">
          <div class="category-icon">3</div>
          <h3>Checkout smoothly</h3>
          <p class="section-text">Place a mock order for delivery or pick-up in a clean, student-project-ready UI.</p>
        </article>
      </div>
    </div>
  </section>

  <footer class="site-footer" id="contact">
    <div class="container footer-inner">
      <div>
        <div class="footer-brand">
          <strong class="brand-name">FurrfectCafe</strong>
        </div>

        <p class="footer-text">
          A cozy cat café in Legazpi City, Albay. Order online and enjoy your favorites at home or pick them up fresh.
        </p>

        <div class="socials">
          <a href="index.php" aria-label="Facebook">f</a>
          <a href="index.php" aria-label="Instagram">◎</a>
          <a href="index.php" aria-label="TikTok">♪</a>
          <a href="index.php" aria-label="Twitter">x</a>
        </div>
      </div>

      <div>
        <h3 class="footer-heading">Quick Links</h3>

        <div class="footer-links">
          <a href="index.php">Home</a>
          <a href="menu.php">Menu</a>
          <a href="index.php#how-it-works">How It Works</a>
          <a href="orders.php">My Orders</a>
        </div>
      </div>

      <div>
        <h3 class="footer-heading">Account</h3>

        <div class="footer-links">
          <a href="profile.php">Profile</a>
          <a href="orders.php">Order History</a>
          <a href="cart.php">Cart</a>
          <a href="login.php">Login Portal</a>
        </div>
      </div>

      <div>
        <h3 class="footer-heading">Contact</h3>

        <div class="footer-contact">
          <span>📍 Legazpi City, Albay</span>
          <a href="tel:+639123456789">📞 +63 912 345 6789</a>
          <a href="mailto:hello@furrfectcafe.ph">✉️ hello@furrfectcafe.ph</a>
          <span>🕘 8AM - 9PM daily</span>
        </div>
      </div>
    </div>

    <div class="container footer-bottom">
      © 2026 FurrfectCafe. All rights reserved.
    </div>
  </footer>

  <script src="script.js"></script>
</body>
</html>