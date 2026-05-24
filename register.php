<?php
session_start();
require_once "includes/db.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $firstName = trim($_POST["firstName"] ?? "");
    $lastName = trim($_POST["lastName"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $contactNumber = trim($_POST["contactNumber"] ?? "");
    $fullAddress = trim($_POST["fullAddress"] ?? "");
    $password = $_POST["password"] ?? "";
    $confirmPassword = $_POST["confirmPassword"] ?? "";

    if ($firstName === "" || $lastName === "" || $email === "" || $contactNumber === "" || $fullAddress === "") {
        $message = "Please complete all required fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Please enter a valid email address.";
    } elseif (strlen($password) < 8) {
        $message = "Password must be at least 8 characters.";
    } elseif ($password !== $confirmPassword) {
        $message = "Passwords do not match.";
    } else {
        try {
            $stmt = $conn->prepare("INSERT INTO users (first_name, last_name, email, password_hash, contact_number, delivery_address, role, status) VALUES (?, ?, ?, ?, ?, ?, 'customer', 'active')");
            $stmt->execute([$firstName, $lastName, $email, password_hash($password, PASSWORD_DEFAULT), $contactNumber, $fullAddress]);
            header("Location: login.php?registered=1");
            exit;
        } catch (PDOException $e) {
            $message = "Registration failed. This email may already be registered.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>FurrfectCafe | Create Account</title>
  <link rel="stylesheet" href="style.css">
  <style>
    .register-page {
      min-height: 100vh;
      display: grid;
      grid-template-columns: 0.78fr 1.22fr;
      background: var(--bg);
    }

    .register-panel {
      position: relative;
      overflow: hidden;
      background: linear-gradient(180deg, #4b2617 0%, #2f180e 100%);
      color: #fff7ef;
      padding: 28px;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
    }

    .register-panel::before {
      content: "";
      position: absolute;
      right: -70px;
      bottom: -70px;
      width: 250px;
      height: 250px;
      border-radius: 50%;
      background: rgba(201, 122, 53, 0.18);
    }

    .register-panel-top {
      position: relative;
      z-index: 1;
    }

    .register-panel-copy {
      max-width: 320px;
      margin-top: 28px;
    }

    .register-panel-copy h1 {
      font-size: clamp(2rem, 4vw, 3.3rem);
      line-height: 0.98;
      margin-bottom: 14px;
    }

    .register-panel-copy p,
    .register-hint {
      font-family: Arial, Helvetica, sans-serif;
      color: rgba(255,255,255,0.82);
    }

    .register-steps-side {
      display: grid;
      gap: 16px;
      margin-top: 34px;
      font-family: Arial, Helvetica, sans-serif;
      color: rgba(255,255,255,0.82);
      font-size: 0.95rem;
    }

    .register-content {
      display: grid;
      place-items: center;
      padding: 36px 20px;
    }

    .register-card {
      width: min(100%, 760px);
    }

    .register-title {
      font-size: clamp(2rem, 4vw, 3rem);
      margin-bottom: 8px;
    }

    .register-subtext {
      font-family: Arial, Helvetica, sans-serif;
      color: var(--text-muted);
      margin-bottom: 22px;
    }

    .register-subtext a {
      color: var(--accent);
      font-weight: 700;
    }

    .stepper {
      display: flex;
      align-items: center;
      gap: 12px;
      flex-wrap: wrap;
      margin-bottom: 22px;
    }

    .step-item {
      display: flex;
      align-items: center;
      gap: 10px;
      font-family: Arial, Helvetica, sans-serif;
      color: var(--text-muted);
      font-size: 0.92rem;
    }

    .step-bubble {
      width: 36px;
      height: 36px;
      border-radius: 50%;
      display: grid;
      place-items: center;
      background: var(--surface-2);
      color: var(--text-muted);
      border: 1px solid var(--border);
      font-weight: 700;
    }

    .step-item.active .step-bubble {
      background: var(--accent);
      color: white;
      border-color: var(--accent);
    }

    .step-line {
      width: 34px;
      height: 2px;
      background: var(--border);
    }

    .register-section {
      background: var(--surface);
      border: 1px solid var(--border);
      border-radius: 24px;
      padding: 20px;
      box-shadow: var(--shadow-sm);
    }

    .register-section + .register-section {
      margin-top: 18px;
    }

    .register-section-title {
      display: flex;
      align-items: center;
      gap: 10px;
      font-size: 1.2rem;
      margin-bottom: 18px;
    }

    .field-icon-wrap {
      position: relative;
    }

    .field-icon {
      position: absolute;
      left: 16px;
      top: 50%;
      transform: translateY(-50%);
      color: var(--text-muted);
      font-family: Arial, Helvetica, sans-serif;
      pointer-events: none;
    }

    .field-icon-wrap .input,
    .field-icon-wrap .textarea {
      padding-left: 44px;
    }

    .password-toggle {
      position: absolute;
      right: 14px;
      top: 50%;
      transform: translateY(-50%);
      color: var(--text-muted);
    }

    .checkbox-row {
      display: flex;
      align-items: flex-start;
      gap: 12px;
      font-family: Arial, Helvetica, sans-serif;
      color: var(--text-muted);
      font-size: 0.92rem;
    }

    .checkbox-row input {
      accent-color: var(--accent);
      width: 16px;
      height: 16px;
      margin-top: 3px;
      flex-shrink: 0;
    }

    .checkbox-row a {
      color: var(--accent);
      font-weight: 700;
    }

    .register-actions {
      margin-top: 20px;
      display: grid;
      gap: 12px;
    }

    .register-note {
      margin-top: 14px;
      background: #fff7ef;
      border: 1px solid rgba(201, 122, 53, 0.25);
      border-radius: 16px;
      padding: 14px 16px;
      font-family: Arial, Helvetica, sans-serif;
      color: var(--text-muted);
      font-size: 0.9rem;
    }

    @media (max-width: 980px) {
      .register-page {
        grid-template-columns: 1fr;
      }

      .register-panel {
        min-height: 300px;
      }
    }

    @media (max-width: 620px) {
      .stepper {
        align-items: flex-start;
      }

      .step-line {
        display: none;
      }
    }
  </style>
</head>
<body>
  <main class="register-page">
    <section class="register-panel">
      <div class="register-panel-top">
        <a href="index.php" class="back-link" style="display:inline-flex;align-items:center;gap:8px;font-family:Arial, Helvetica, sans-serif;color:rgba(255,255,255,0.82);">← Back to Home</a>

        <div class="auth-brand" style="display:flex;align-items:center;gap:12px;margin-top:22px;">
          <span class="brand-mark">CAFE</span>
          <span class="brand-name">FurrfectCafe</span>
        </div>

        <div class="register-panel-copy">
          <span class="eyebrow" style="color:#f4bf88;">Join the Family</span>
          <h1>Order with every little craving in mind.</h1>
          <p>
            Create your account and enjoy the full FurrfectCafe experience, from menu browsing to order tracking.
          </p>
        </div>

        <div class="register-steps-side">
          <span>✓ Browse the menu</span>
          <span>✓ Create your account</span>
          <span>✓ Enjoy delivery or pick-up</span>
        </div>
      </div>

      <p class="register-hint" style="position:relative; z-index:1; margin:0;">
        Good food, great vibes, cozy moments.
      </p>
    </section>

    <section class="register-content">
      <div class="register-card">
        <h1 class="register-title">Create Account</h1>
        <p class="register-subtext">
          Already have one? <a href="login.php">Log in here</a>
        </p>

        <div class="stepper" aria-label="Registration steps">
          <div class="step-item active">
            <span class="step-bubble">1</span>
            <span>Personal Information</span>
          </div>
          <span class="step-line"></span>
          <div class="step-item">
            <span class="step-bubble">2</span>
            <span>Security</span>
          </div>
          <span class="step-line"></span>
          <div class="step-item">
            <span class="step-bubble">3</span>
            <span>Done</span>
          </div>
        </div>

        <?php if ($message): ?>
          <div class="admin-note" style="margin-bottom:16px;"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <form id="registerForm" method="POST" action="register.php">
          <section class="register-section">
            <h2 class="register-section-title">👤 Personal Information</h2>

            <div class="form-grid form-grid-2">
              <div class="field">
                <label for="firstName">First Name *</label>
                <div class="field-icon-wrap">
                  <span class="field-icon">👤</span>
                  <input class="input" type="text" id="firstName" name="firstName" placeholder="Enter first name" required>
                </div>
              </div>

              <div class="field">
                <label for="lastName">Last Name *</label>
                <div class="field-icon-wrap">
                  <span class="field-icon">👤</span>
                  <input class="input" type="text" id="lastName" name="lastName" placeholder="Enter last name" required>
                </div>
              </div>

              <div class="field">
                <label for="email">Email Address *</label>
                <div class="field-icon-wrap">
                  <span class="field-icon">✉</span>
                  <input class="input" type="email" id="email" name="email" placeholder="Enter email address" required>
                </div>
              </div>

              <div class="field">
                <label for="contactNumber">Contact Number *</label>
                <div class="field-icon-wrap">
                  <span class="field-icon">📞</span>
                  <input class="input" type="tel" id="contactNumber" name="contactNumber" placeholder="+63 9XX XXX XXXX" required>
                </div>
              </div>

              <div class="field" style="grid-column: 1 / -1;">
                <label for="fullAddress">Full Address *</label>
                <div class="field-icon-wrap">
                  <span class="field-icon" style="top:22px; transform:none;">📍</span>
                  <textarea class="textarea" id="fullAddress" name="fullAddress" placeholder="House no., street, barangay, city, province" required></textarea>
                </div>
              </div>
            </div>
          </section>

          <section class="register-section">
            <h2 class="register-section-title">🔒 Security</h2>

            <div class="form-grid form-grid-2">
              <div class="field">
                <label for="password">Password *</label>
                <div class="field-icon-wrap">
                  <span class="field-icon">🔒</span>
                  <input class="input" type="password" id="password" name="password" placeholder="Min. 8 characters" required minlength="8">
                  <button class="password-toggle" type="button" data-toggle="password">👁</button>
                </div>
                <div class="form-help">Use 8+ characters with letters and numbers.</div>
              </div>

              <div class="field">
                <label for="confirmPassword">Confirm Password *</label>
                <div class="field-icon-wrap">
                  <span class="field-icon">🔐</span>
                  <input class="input" type="password" id="confirmPassword" name="confirmPassword" placeholder="Repeat your password" required minlength="8">
                  <button class="password-toggle" type="button" data-toggle="confirmPassword">👁</button>
                </div>
              </div>
            </div>
          </section>

          <section class="register-section">
            <h2 class="register-section-title">✅ Confirmation</h2>

            <label class="checkbox-row">
              <input type="checkbox" id="agreeTerms" required>
              <span>
                I agree to FurrfectCafe’s <a href="#">Terms of Service</a> and <a href="#">Privacy Policy</a>.
              </span>
            </label>

            <div class="register-actions">
              <button type="submit" class="btn btn-primary btn-full">📝 Create My Account</button>
              <a href="login.php" class="btn btn-secondary btn-full">Already have an account? Log In</a>
            </div>
          </section>
        </form>

        <div class="register-note">
          Front-end demo only. Submitted data will be stored locally in your browser for presentation purposes.
        </div>
      </div>
    </section>
  </main>
  <script src="script.js"></script>
  <script>
    document.addEventListener("DOMContentLoaded", () => {
      document.querySelectorAll(".password-toggle").forEach(button => {
        button.addEventListener("click", () => {
          const targetId = button.dataset.toggle;
          const targetInput = document.getElementById(targetId);
          const isPassword = targetInput.type === "password";
          targetInput.type = isPassword ? "text" : "password";
          button.textContent = isPassword ? "🙈" : "👁";
        });
      });
    });
  </script>
</body>
</html>
