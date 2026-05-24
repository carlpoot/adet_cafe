<?php
require_once "includes/admin-auth.php";
require_once "includes/db.php";

$message = "";
$error = "";

$uploadDir = __DIR__ . "/assets/uploads/products/";
$uploadWebPath = "assets/uploads/products/";

if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

function makeSlug($text) {
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    $text = trim($text, '-');
    return $text ?: 'product';
}

function uniqueProductSlug($conn, $name, $currentId = null) {
    $base = makeSlug($name);
    $slug = $base;
    $counter = 1;

    while (true) {
        if ($currentId) {
            $stmt = $conn->prepare("SELECT product_id FROM products WHERE product_slug = ? AND product_id != ? LIMIT 1");
            $stmt->execute([$slug, $currentId]);
        } else {
            $stmt = $conn->prepare("SELECT product_id FROM products WHERE product_slug = ? LIMIT 1");
            $stmt->execute([$slug]);
        }

        if (!$stmt->fetch()) {
            return $slug;
        }

        $slug = $base . "-" . $counter;
        $counter++;
    }
}

function parseSizeOptions($text) {
    $items = preg_split('/[\r\n,]+/', $text);
    $sizes = [];

    foreach ($items as $item) {
        $item = trim($item);
        if ($item !== "") {
            $sizes[] = $item;
        }
    }

    return $sizes;
}

function parseAddOns($text) {
    $items = preg_split('/[\r\n,]+/', $text);
    $addons = [];

    foreach ($items as $item) {
        $item = trim($item);
        if ($item === "") continue;

        $name = $item;
        $price = 0;

        if (preg_match('/^(.*?)\s*\+?\s*(\d+(?:\.\d{1,2})?)$/', $item, $matches)) {
            $name = trim($matches[1]);
            $price = (float) $matches[2];
        }

        if ($name !== "") {
            $addons[] = [
                "name" => $name,
                "price" => $price
            ];
        }
    }

    return $addons;
}

$editId = isset($_GET["id"]) ? (int) $_GET["id"] : 0;
$product = null;

$categoriesStmt = $conn->query("SELECT category_id, category_name FROM categories WHERE is_active = 1 ORDER BY display_order, category_name");
$categories = $categoriesStmt->fetchAll();

if ($editId > 0) {
    $stmt = $conn->prepare("
        SELECT 
            p.*,
            c.category_name
        FROM products p
        JOIN categories c ON p.category_id = c.category_id
        WHERE p.product_id = ?
        LIMIT 1
    ");
    $stmt->execute([$editId]);
    $product = $stmt->fetch();

    if (!$product) {
        $error = "Product not found.";
        $editId = 0;
    }
}

$sizeText = "";
$addonText = "";

if ($editId > 0 && $product) {
    $sizeStmt = $conn->prepare("SELECT size_name FROM product_sizes WHERE product_id = ? ORDER BY display_order, size_id");
    $sizeStmt->execute([$editId]);
    $sizeText = implode(", ", array_column($sizeStmt->fetchAll(), "size_name"));

    $addonStmt = $conn->prepare("SELECT addon_name, addon_price FROM product_addons WHERE product_id = ? ORDER BY display_order, addon_id");
    $addonStmt->execute([$editId]);
    $addonRows = $addonStmt->fetchAll();

    $addonParts = [];
    foreach ($addonRows as $addon) {
        $addonParts[] = $addon["addon_name"] . " +" . rtrim(rtrim(number_format((float)$addon["addon_price"], 2, ".", ""), "0"), ".");
    }
    $addonText = implode(", ", $addonParts);
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $postedId = isset($_POST["product_id"]) ? (int) $_POST["product_id"] : 0;
    $name = trim($_POST["product_name"] ?? "");
    $description = trim($_POST["description"] ?? "");
    $price = (float) ($_POST["price"] ?? 0);
    $categoryId = (int) ($_POST["category_id"] ?? 0);
    $promoBadge = trim($_POST["promo_badge"] ?? "");
    $availability = $_POST["availability"] ?? "Available";
    $isAvailable = $availability === "Available" ? 1 : 0;
    $sizesInput = trim($_POST["size_options"] ?? "");
    $addonsInput = trim($_POST["add_ons"] ?? "");
    $existingImage = trim($_POST["existing_image"] ?? "");
    $imagePath = $existingImage;

    if ($name === "") {
        $error = "Please enter a product name.";
    } elseif ($price <= 0) {
        $error = "Please enter a valid product price.";
    } elseif ($categoryId <= 0) {
        $error = "Please select a category.";
    } else {
        if (isset($_FILES["product_image"]) && $_FILES["product_image"]["error"] !== UPLOAD_ERR_NO_FILE) {
            if ($_FILES["product_image"]["error"] !== UPLOAD_ERR_OK) {
                $error = "Image upload failed. Please try again.";
            } elseif ($_FILES["product_image"]["size"] > 5 * 1024 * 1024) {
                $error = "Image must be 5MB or below.";
            } else {
                $allowedTypes = [
                    "image/jpeg" => "jpg",
                    "image/png" => "png",
                    "image/webp" => "webp"
                ];

                $mime = mime_content_type($_FILES["product_image"]["tmp_name"]);

                if (!isset($allowedTypes[$mime])) {
                    $error = "Only JPG, PNG, and WEBP images are allowed.";
                } else {
                    $extension = $allowedTypes[$mime];
                    $filename = makeSlug($name) . "-" . time() . "." . $extension;
                    $targetPath = $uploadDir . $filename;

                    if (move_uploaded_file($_FILES["product_image"]["tmp_name"], $targetPath)) {
                        $imagePath = $uploadWebPath . $filename;
                    } else {
                        $error = "Could not save the uploaded image.";
                    }
                }
            }
        }
    }

    if ($error === "") {
        try {
            $conn->beginTransaction();

            if ($postedId > 0) {
                $slug = uniqueProductSlug($conn, $name, $postedId);

                $stmt = $conn->prepare("
                    UPDATE products
                    SET category_id = ?, product_name = ?, product_slug = ?, description = ?, price = ?, image_path = ?, promo_badge = ?, is_available = ?
                    WHERE product_id = ?
                ");
                $stmt->execute([
                    $categoryId,
                    $name,
                    $slug,
                    $description,
                    $price,
                    $imagePath ?: null,
                    $promoBadge ?: null,
                    $isAvailable,
                    $postedId
                ]);

                $productId = $postedId;
            } else {
                $slug = uniqueProductSlug($conn, $name);

                $stmt = $conn->prepare("
                    INSERT INTO products
                    (category_id, product_name, product_slug, description, price, image_path, promo_badge, is_available)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $categoryId,
                    $name,
                    $slug,
                    $description,
                    $price,
                    $imagePath ?: null,
                    $promoBadge ?: null,
                    $isAvailable
                ]);

                $productId = (int) $conn->lastInsertId();
            }

            $conn->prepare("DELETE FROM product_sizes WHERE product_id = ?")->execute([$productId]);
            $conn->prepare("DELETE FROM product_addons WHERE product_id = ?")->execute([$productId]);

            $sizes = parseSizeOptions($sizesInput);
            $displayOrder = 1;
            foreach ($sizes as $size) {
                $stmt = $conn->prepare("
                    INSERT INTO product_sizes (product_id, size_name, price_modifier, is_default, is_available, display_order)
                    VALUES (?, ?, 0.00, ?, 1, ?)
                ");
                $stmt->execute([$productId, $size, $displayOrder === 1 ? 1 : 0, $displayOrder]);
                $displayOrder++;
            }

            $addons = parseAddOns($addonsInput);
            $displayOrder = 1;
            foreach ($addons as $addon) {
                $stmt = $conn->prepare("
                    INSERT INTO product_addons (product_id, addon_name, addon_price, is_available, display_order)
                    VALUES (?, ?, ?, 1, ?)
                ");
                $stmt->execute([$productId, $addon["name"], $addon["price"], $displayOrder]);
                $displayOrder++;
            }

            $conn->commit();

            header("Location: admin-products.php?saved=1");
            exit;
        } catch (Exception $e) {
            $conn->rollBack();
            $error = "Could not save product: " . $e->getMessage();
        }
    }

    $product = [
        "product_id" => $postedId,
        "product_name" => $name,
        "description" => $description,
        "price" => $price,
        "category_id" => $categoryId,
        "image_path" => $imagePath,
        "promo_badge" => $promoBadge,
        "is_available" => $isAvailable
    ];
    $editId = $postedId;
    $sizeText = $sizesInput;
    $addonText = $addonsInput;
}

$pageTitle = $editId > 0 ? "Edit Product" : "Add Product";
$currentImage = $product["image_path"] ?? "";
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>FurrfectCafe Admin | <?php echo htmlspecialchars($pageTitle); ?></title>
  <link rel="stylesheet" href="style.css">
  <style>
    .form-shell {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 18px;
      align-items: start;
    }

    .stack {
      display: grid;
      gap: 18px;
    }

    .upload-box {
      min-height: 230px;
      border: 2px dashed #dcc7af;
      border-radius: 22px;
      background: #faf4ec;
      display: grid;
      place-items: center;
      text-align: center;
      padding: 20px;
      font-family: Arial, Helvetica, sans-serif;
      color: var(--text-muted);
      cursor: pointer;
      transition: var(--transition);
      overflow: hidden;
    }

    .upload-box:hover,
    .upload-box.drag-over {
      border-color: var(--accent);
      background: #fff8ef;
    }

    .upload-preview {
      width: 100%;
      max-height: 240px;
      object-fit: contain;
      border-radius: 18px;
      margin-top: 14px;
      background: white;
      border: 1px solid var(--border);
    }

    .hint {
      display: block;
      margin-top: 6px;
      font-size: 0.88rem;
    }

    .action-footer {
      display: grid;
      gap: 12px;
      margin-top: 8px;
    }

    .alert {
      padding: 14px 16px;
      border-radius: 16px;
      margin-bottom: 16px;
      font-family: Arial, Helvetica, sans-serif;
      font-weight: 700;
    }

    .alert-danger {
      background: rgba(210, 84, 84, 0.12);
      color: var(--danger);
      border: 1px solid rgba(210, 84, 84, 0.25);
    }

    @media (max-width: 980px) {
      .form-shell {
        grid-template-columns: 1fr;
      }
    }
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
        <a href="admin-orders.php">Orders</a>
      </nav>

      <div class="eyebrow" style="color: rgba(255,255,255,0.45); margin-top: 20px;">Catalog</div>
      <nav class="admin-nav">
        <a href="admin-products.php">Products</a>
        <a href="admin-product-form.php" class="active">+ Add / Edit Product</a>
      </nav>

      <div class="admin-sidebar-footer">
        <div class="admin-user-mini">
          <div class="admin-user-avatar">A</div>
          <div>
            <strong style="display:block;">Admin</strong>
            <span style="font-family:Arial, Helvetica, sans-serif; color:rgba(255,255,255,0.65); font-size:0.9rem;">Administrator</span>
          </div>
        </div>
        <a href="logout.php" class="admin-logout-btn" style="display:flex; align-items:center; justify-content:center;">Logout</a>
      </div>
    </aside>

    <main class="admin-content">
      <div class="admin-topbar">
        <div>
          <div class="eyebrow" style="margin-bottom:6px;">Products</div>
          <h1 class="admin-page-title">☰ <?php echo htmlspecialchars($pageTitle); ?></h1>
        </div>
        <a href="admin-products.php" class="btn btn-secondary btn-sm">← Back to Products</a>
      </div>

      <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
      <?php endif; ?>

      <form id="productForm" class="form-shell" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="product_id" value="<?php echo (int)($product["product_id"] ?? 0); ?>">
        <input type="hidden" name="existing_image" value="<?php echo htmlspecialchars($currentImage); ?>">

        <div class="stack">
          <section class="form-card">
            <h2 class="form-title">📦 Product Information</h2>

            <div class="form-grid">
              <div class="field">
                <label for="productName">Product Name *</label>
                <input class="input" type="text" id="productName" name="product_name" value="<?php echo htmlspecialchars($product["product_name"] ?? ""); ?>" required>
              </div>

              <div class="field">
                <label for="productDescription">Description</label>
                <textarea class="textarea" id="productDescription" name="description" placeholder="Short description shown on the menu card."><?php echo htmlspecialchars($product["description"] ?? ""); ?></textarea>
              </div>

              <div class="form-grid form-grid-2">
                <div class="field">
                  <label for="productPrice">Price (₱) *</label>
                  <input class="input" type="number" id="productPrice" name="price" min="1" step="0.01" value="<?php echo htmlspecialchars($product["price"] ?? ""); ?>" required>
                </div>

                <div class="field">
                  <label for="productCategory">Category *</label>
                  <select class="select" id="productCategory" name="category_id" required>
                    <option value="">Select category</option>
                    <?php foreach ($categories as $category): ?>
                      <option value="<?php echo (int)$category["category_id"]; ?>" <?php echo ((int)($product["category_id"] ?? 0) === (int)$category["category_id"]) ? "selected" : ""; ?>>
                        <?php echo htmlspecialchars($category["category_name"]); ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>
              </div>
            </div>
          </section>

          <section class="form-card">
            <h2 class="form-title">⚙ Customization Options <span style="font-weight:400; color:var(--text-muted); font-size:0.95rem;">(optional)</span></h2>

            <div class="form-grid">
              <div class="field">
                <label for="sizeOptions">Size Options</label>
                <input class="input" type="text" id="sizeOptions" name="size_options" value="<?php echo htmlspecialchars($sizeText); ?>">
                <div class="form-help">Comma-separated. Example: Small, Regular, Large</div>
              </div>

              <div class="field">
                <label for="addOns">Add-ons</label>
                <textarea class="textarea" id="addOns" name="add_ons"><?php echo htmlspecialchars($addonText); ?></textarea>
                <div class="form-help">One per line or comma-separated. Example: Extra Shot +20</div>
              </div>
            </div>
          </section>
        </div>

        <div class="stack">
          <section class="form-card">
            <h2 class="form-title">🖼 Product Image</h2>

            <label for="productImage" class="upload-box" id="uploadBox">
              <input
                type="file"
                id="productImage"
                name="product_image"
                accept="image/png, image/jpeg, image/webp"
                hidden
              >

              <div>
                <strong>Drop image here or click to browse</strong>
                <span class="hint">PNG, JPG, WEBP up to 5MB • Recommended 800×600</span>

                <img
                  id="imagePreview"
                  class="upload-preview"
                  src="<?php echo htmlspecialchars($currentImage); ?>"
                  alt="Product preview"
                  style="<?php echo $currentImage ? "" : "display:none;"; ?>"
                >
              </div>
            </label>

            <button type="button" class="btn btn-secondary btn-full mt-16" id="clearImageBtn">
              Clear Selected Image
            </button>
          </section>

          <section class="form-card">
            <h2 class="form-title">🏷 Visibility & Badge</h2>

            <div class="form-grid">
              <div class="field">
                <label for="productBadge">Promo Badge</label>
                <input class="input" type="text" id="productBadge" name="promo_badge" value="<?php echo htmlspecialchars($product["promo_badge"] ?? ""); ?>" placeholder="e.g. New, 10% OFF, Buy 2 Get 1">
              </div>

              <div class="field">
                <label for="productAvailability">Availability</label>
                <select class="select" id="productAvailability" name="availability">
                  <option value="Available" <?php echo ((int)($product["is_available"] ?? 1) === 1) ? "selected" : ""; ?>>Available</option>
                  <option value="Hidden" <?php echo ((int)($product["is_available"] ?? 1) === 0) ? "selected" : ""; ?>>Hidden</option>
                </select>
              </div>
            </div>

            <div class="action-footer">
              <button type="submit" class="btn btn-primary btn-full">💾 Save Product</button>
              <a href="admin-products.php" class="btn btn-secondary btn-full">Cancel</a>
            </div>
          </section>
        </div>
      </form>
    </main>
  </div>

  <script src="script.js"></script>
  <script>
    document.addEventListener("DOMContentLoaded", () => {
      const uploadBox = document.getElementById("uploadBox");
      const productImage = document.getElementById("productImage");
      const imagePreview = document.getElementById("imagePreview");
      const uploadTitle = document.getElementById("uploadTitle");
      const uploadHelp = document.getElementById("uploadHelp");
      const clearImageBtn = document.getElementById("clearImageBtn");

      function previewImage(file) {
        if (!file) return;

        if (!["image/png", "image/jpeg", "image/webp"].includes(file.type)) {
          uploadHelp.textContent = "Please upload a PNG, JPG, or WEBP image only.";
          uploadHelp.style.color = "var(--danger)";
          productImage.value = "";
          return;
        }

        if (file.size > 5 * 1024 * 1024) {
          uploadHelp.textContent = "Image must be 5MB or below.";
          uploadHelp.style.color = "var(--danger)";
          productImage.value = "";
          return;
        }

        const reader = new FileReader();

        reader.onload = event => {
          imagePreview.src = event.target.result;
          imagePreview.style.display = "block";
          uploadTitle.textContent = file.name;
          uploadHelp.textContent = "Image selected. Click Save Product to upload.";
          uploadHelp.style.color = "var(--text-muted)";
        };

        reader.readAsDataURL(file);
      }

      if (productImage) {
        productImage.addEventListener("change", () => {
          previewImage(productImage.files[0]);
        });
      }

      if (uploadBox) {
        uploadBox.addEventListener("dragover", event => {
          event.preventDefault();
          uploadBox.classList.add("drag-over");
        });

        uploadBox.addEventListener("dragleave", () => {
          uploadBox.classList.remove("drag-over");
        });

        uploadBox.addEventListener("drop", event => {
          event.preventDefault();
          uploadBox.classList.remove("drag-over");

          const file = event.dataTransfer.files[0];

          if (!file) return;

          const dataTransfer = new DataTransfer();
          dataTransfer.items.add(file);
          productImage.files = dataTransfer.files;

          previewImage(file);
        });
      }

      if (clearImageBtn) {
        clearImageBtn.addEventListener("click", () => {
          productImage.value = "";
          imagePreview.src = "";
          imagePreview.style.display = "none";

          uploadTitle.textContent = "Drop image here or click to browse";
          uploadHelp.textContent = "PNG, JPG, WEBP up to 5MB • Recommended 800×600";
          uploadHelp.style.color = "var(--text-muted)";
        });
      }
    });
  </script>
</body>
</html>
