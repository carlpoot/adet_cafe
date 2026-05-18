<?php
session_start();
require_once "includes/db.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";
    $loginType = $_POST["login_type"] ?? "customer";

    if ($email === "" || $password === "") {
        $message = "Please enter your email and password.";
    } else {
        $allowedRoles = $loginType === "admin" ? ["admin", "system_admin"] : ["customer"];
        $placeholders = implode(",", array_fill(0, count($allowedRoles), "?"));

        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? AND role IN ($placeholders) AND status = 'active' LIMIT 1");
        $stmt->execute(array_merge([$email], $allowedRoles));
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user["password_hash"])) {
            $_SESSION["user_id"] = $user["user_id"];
            $_SESSION["user_name"] = $user["first_name"];
            $_SESSION["role"] = $user["role"];

            if (in_array($user["role"], ["admin", "system_admin"], true)) {
                header("Location: admin-dashboard.php");
            } else {
                header("Location: index.php");
            }
            exit;
        }

        $message = "Invalid email or password.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>FurrfectCafe | Login Portal</title>
  <link rel="stylesheet" href="style.css">
  <style>
    .portal-page {
      min-height: 100vh;
      display: grid;
      grid-template-columns: 0.95fr 1.05fr;
      background: var(--bg);
    }

    .portal-panel {
      position: relative;
      overflow: hidden;
      background: linear-gradient(180deg, #4b2617 0%, #2f180e 100%);
      color: #fff7ef;
      padding: 34px;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
    }

    .portal-panel::before,
    .portal-panel::after {
      content: "";
      position: absolute;
      border-radius: 50%;
      background: rgba(201, 122, 53, 0.18);
      pointer-events: none;
    }

    .portal-panel::before {
      width: 250px;
      height: 250px;
      right: -70px;
      bottom: -70px;
    }

    .portal-panel::after {
      width: 170px;
      height: 170px;
      left: -50px;
      top: 90px;
    }

    .portal-panel-content {
      position: relative;
      z-index: 1;
      max-width: 460px;
    }

    .portal-back-link {
      position: relative;
      z-index: 1;
      display: inline-flex;
      align-items: center;
      gap: 8px;
      font-family: Arial, Helvetica, sans-serif;
      color: rgba(255,255,255,0.82);
      margin-bottom: 28px;
    }

    .portal-back-link:hover {
      color: #fff;
    }

    .portal-brand {
      position: relative;
      z-index: 1;
      display: flex;
      align-items: center;
      gap: 12px;
      margin-bottom: 42px;
    }

    .portal-panel h1 {
      font-size: clamp(2.6rem, 5vw, 4.5rem);
      line-height: 0.96;
      margin-bottom: 16px;
    }

    .portal-panel p {
      font-family: Arial, Helvetica, sans-serif;
      color: rgba(255,255,255,0.82);
      margin: 0;
    }

    .portal-form-wrap {
      display: grid;
      place-items: center;
      padding: 40px 20px;
    }

    .portal-card {
      width: min(100%, 620px);
    }

    .portal-title {
      font-size: clamp(2rem, 4vw, 3rem);
      margin-bottom: 10px;
    }

    .portal-subtext {
      font-family: Arial, Helvetica, sans-serif;
      color: var(--text-muted);
      margin-bottom: 24px;
    }

    .portal-subtext a {
      color: var(--accent);
      font-weight: 700;
    }

    .portal-tabs {
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: 10px;
      margin-bottom: 22px;
      background: #efe5d8;
      border-radius: 18px;
      padding: 6px;
    }

    .portal-tab {
      min-height: 48px;
      border-radius: 14px;
      font-family: Arial, Helvetica, sans-serif;
      font-weight: 700;
      color: var(--text-muted);
      transition: var(--transition);
    }

    .portal-tab.active {
      background: var(--surface);
      color: var(--primary);
      box-shadow: var(--shadow-sm);
    }

    .login-panel {
      display: none;
    }

    .login-panel.active {
      display: block;
    }

    .field-icon-wrap {
      position: relative;
    }

    .field-icon {
      position: absolute;
      left: 16px;
      top: 50%;
      transform: translateY(-50%);
      font-size: 0.95rem;
      color: var(--text-muted);
      pointer-events: none;
      font-family: Arial, Helvetica, sans-serif;
    }

    .field-icon-wrap .input {
      padding-left: 44px;
      padding-right: 44px;
    }

    .toggle-password {
      position: absolute;
      right: 14px;
      top: 50%;
      transform: translateY(-50%);
      font-size: 0.95rem;
      color: var(--text-muted);
      background: none;
      border: none;
      padding: 0;
      cursor: pointer;
    }

    .form-row-between {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 16px;
      flex-wrap: wrap;
      margin: 8px 0 6px;
      font-family: Arial, Helvetica, sans-serif;
      font-size: 0.92rem;
    }

    .remember-wrap {
      display: inline-flex;
      align-items: center;
      gap: 10px;
      color: var(--text-muted);
    }

    .remember-wrap input {
      accent-color: var(--accent);
      width: 16px;
      height: 16px;
      margin: 0;
    }

    .text-link {
      color: var(--accent);
      font-weight: 700;
      background: none;
      border: none;
      padding: 0;
      cursor: pointer;
      font-family: Arial, Helvetica, sans-serif;
    }

    .divider {
      display: flex;
      align-items: center;
      gap: 12px;
      margin: 18px 0;
      font-family: Arial, Helvetica, sans-serif;
      color: var(--text-muted);
      font-size: 0.9rem;
    }

    .divider::before,
    .divider::after {
      content: "";
      flex: 1;
      height: 1px;
      background: var(--border);
    }

    .google-btn {
      background: #1f86da;
      color: white;
    }

    .demo-note,
    .admin-note {
      margin-top: 16px;
      border-radius: 16px;
      padding: 14px 16px;
      font-family: Arial, Helvetica, sans-serif;
      color: var(--text-muted);
      font-size: 0.9rem;
    }

    .demo-note {
      background: #fff7ef;
      border: 1px solid rgba(201, 122, 53, 0.28);
    }

    .admin-note {
      background: #f3ede7;
      border: 1px solid var(--border);
    }

    @media (max-width: 920px) {
      .portal-page {
        grid-template-columns: 1fr;
      }

      .portal-panel {
        min-height: 320px;
      }

      .portal-form-wrap {
        padding-top: 30px;
      }
    }
  </style>
</head>
<body>
  <main class="portal-page">
    <section class="portal-panel">
      <div class="portal-panel-content">
        <a href="index.php" class="portal-back-link">← Back to Home</a>

        <div class="portal-brand">
          <span class="brand-mark">CAFE</span>
          <span class="brand-name">FurrfectCafe</span>
        </div>

        <h1>Welcome to FurrfectCafe</h1>
        <p>
          Use this single login portal to access both the customer side and the admin side during your demo.
        </p>
      </div>

      <p style="position:relative; z-index:1; font-family:Arial, Helvetica, sans-serif; color:rgba(255,255,255,0.72); margin:0;">
        Good food, great vibes, happy cats.
      </p>
    </section>

    <section class="portal-form-wrap">
      <div class="portal-card">
        <h1 class="portal-title">Login Portal</h1>
        <p class="portal-subtext">
          New customer? <a href="register.php">Create an account here</a>
        </p>

        <?php if ($message): ?>
          <div class="admin-note" style="margin-bottom:16px;"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <div class="portal-tabs">
          <button type="button" class="portal-tab active" data-target="customerPanel">Customer Login</button>
          <button type="button" class="portal-tab" data-target="adminPanel">Admin Login</button>
        </div>

        <section class="login-panel active" id="customerPanel">
          <form id="customerLoginForm" class="form-grid" method="POST" action="login.php">
            <input type="hidden" name="login_type" value="customer">
            <div class="field">
              <label for="customerEmail">Email address</label>
              <div class="field-icon-wrap">
                <span class="field-icon">✉</span>
                <input class="input" type="email" id="customerEmail" name="email" placeholder="customer@furrfectcafe.ph" required>
              </div>
            </div>

            <div class="field">
              <label for="customerPassword">Password</label>
              <div class="field-icon-wrap">
                <span class="field-icon">🔒</span>
                <input class="input" type="password" id="customerPassword" name="password" placeholder="Enter password" required minlength="6">
                <button class="toggle-password" type="button" data-toggle="customerPassword">👁</button>
              </div>
            </div>

            <div class="form-row-between">
              <label class="remember-wrap">
                <input type="checkbox" id="rememberMe">
                <span>Remember me</span>
              </label>
              <button type="button" class="text-link" id="forgotPasswordBtn">Forgot password?</button>
            </div>

            <button type="submit" class="btn btn-primary btn-full">🔐 Log In as Customer</button>

            <div class="divider">or continue with</div>

            <button type="button" class="btn google-btn btn-full" id="googleLoginBtn">Continue with Google</button>
          </form>

          <div class="demo-note">
            Customer account: customer@furrfectcafe.ph / customer123
          </div>
        </section>

        <section class="login-panel" id="adminPanel">
          <form id="adminLoginForm" class="form-grid" method="POST" action="login.php">
            <input type="hidden" name="login_type" value="admin">
            <div class="field">
              <label for="adminEmail">Admin email</label>
              <div class="field-icon-wrap">
                <span class="field-icon">🛠</span>
                <input class="input" type="email" id="adminEmail" name="email" placeholder="admin@furrfectcafe.ph" required>
              </div>
            </div>

            <div class="field">
              <label for="adminPassword">Password</label>
              <div class="field-icon-wrap">
                <span class="field-icon">🔒</span>
                <input class="input" type="password" id="adminPassword" name="password" placeholder="Enter admin password" required minlength="6">
                <button class="toggle-password" type="button" data-toggle="adminPassword">👁</button>
              </div>
            </div>

            <button type="submit" class="btn btn-primary btn-full">🔐 Log In as Admin</button>
          </form>

          <div class="admin-note">
            Admin account: admin@furrfectcafe.ph / admin123
          </div>
        </section>
      </div>
    </section>
  </main>
  <script src="script.js"></script>
  <script>
    document.addEventListener("DOMContentLoaded", () => {
      const tabs = document.querySelectorAll(".portal-tab");
      const panels = document.querySelectorAll(".login-panel");

      tabs.forEach(tab => {
        tab.addEventListener("click", () => {
          const target = tab.dataset.target;
          tabs.forEach(item => item.classList.remove("active"));
          panels.forEach(panel => panel.classList.remove("active"));
          tab.classList.add("active");
          document.getElementById(target).classList.add("active");
        });
      });

      document.querySelectorAll(".toggle-password").forEach(button => {
        button.addEventListener("click", () => {
          const input = document.getElementById(button.dataset.toggle);
          const isPassword = input.type === "password";
          input.type = isPassword ? "text" : "password";
          button.textContent = isPassword ? "🙈" : "👁";
        });
      });

      const forgotPasswordBtn = document.getElementById("forgotPasswordBtn");
      const googleLoginBtn = document.getElementById("googleLoginBtn");

      if (forgotPasswordBtn) {
        forgotPasswordBtn.addEventListener("click", () => {
          alert("Forgot password is a front-end demo only.");
        });
      }

      if (googleLoginBtn) {
        googleLoginBtn.addEventListener("click", () => {
          alert("Google login is a front-end demo only.");
        });
      }
    });
  </script>
</body>
</html>
