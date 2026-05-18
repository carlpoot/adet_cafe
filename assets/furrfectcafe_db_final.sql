-- FurrfectCafe Database
-- Frontend-aligned MySQL/MariaDB schema for customer ordering + admin management

CREATE DATABASE IF NOT EXISTS furrfectcafe_db
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE furrfectcafe_db;

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS order_status_history;
DROP TABLE IF EXISTS payments;
DROP TABLE IF EXISTS order_items;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS cart_items;
DROP TABLE IF EXISTS cart;
DROP TABLE IF EXISTS product_addons;
DROP TABLE IF EXISTS product_sizes;
DROP TABLE IF EXISTS products;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS users;

SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE users (
  user_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  first_name VARCHAR(100) NOT NULL,
  last_name VARCHAR(100) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  contact_number VARCHAR(20) DEFAULT NULL,
  delivery_address TEXT DEFAULT NULL,
  role ENUM('customer','admin','system_admin') NOT NULL DEFAULT 'customer',
  status ENUM('active','inactive') NOT NULL DEFAULT 'active',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_users_role (role),
  INDEX idx_users_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE categories (
  category_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  category_name VARCHAR(100) NOT NULL UNIQUE,
  category_slug VARCHAR(100) NOT NULL UNIQUE,
  description VARCHAR(255) DEFAULT NULL,
  display_order INT UNSIGNED NOT NULL DEFAULT 0,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_categories_active (is_active),
  INDEX idx_categories_order (display_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE products (
  product_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  category_id INT UNSIGNED NOT NULL,
  product_name VARCHAR(150) NOT NULL,
  product_slug VARCHAR(180) NOT NULL UNIQUE,
  description TEXT DEFAULT NULL,
  price DECIMAL(10,2) NOT NULL,
  image_path VARCHAR(255) DEFAULT NULL,
  promo_badge VARCHAR(50) DEFAULT NULL,
  is_available TINYINT(1) NOT NULL DEFAULT 1,
  is_featured TINYINT(1) NOT NULL DEFAULT 0,
  is_bestseller TINYINT(1) NOT NULL DEFAULT 0,
  display_order INT UNSIGNED NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_products_category FOREIGN KEY (category_id) REFERENCES categories(category_id) ON UPDATE CASCADE,
  INDEX idx_products_category (category_id),
  INDEX idx_products_available (is_available),
  INDEX idx_products_featured (is_featured),
  INDEX idx_products_bestseller (is_bestseller)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE product_sizes (
  size_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  product_id INT UNSIGNED NOT NULL,
  size_name VARCHAR(50) NOT NULL,
  price_modifier DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  is_default TINYINT(1) NOT NULL DEFAULT 0,
  is_available TINYINT(1) NOT NULL DEFAULT 1,
  display_order INT UNSIGNED NOT NULL DEFAULT 0,
  CONSTRAINT fk_product_sizes_product FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE CASCADE ON UPDATE CASCADE,
  INDEX idx_product_sizes_product (product_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE product_addons (
  addon_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  product_id INT UNSIGNED NOT NULL,
  addon_name VARCHAR(100) NOT NULL,
  addon_price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  is_available TINYINT(1) NOT NULL DEFAULT 1,
  display_order INT UNSIGNED NOT NULL DEFAULT 0,
  CONSTRAINT fk_product_addons_product FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE CASCADE ON UPDATE CASCADE,
  INDEX idx_product_addons_product (product_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE cart (
  cart_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_cart_user FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE ON UPDATE CASCADE,
  UNIQUE KEY uq_cart_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE cart_items (
  cart_item_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  cart_id INT UNSIGNED NOT NULL,
  product_id INT UNSIGNED NOT NULL,
  size_id INT UNSIGNED DEFAULT NULL,
  quantity INT UNSIGNED NOT NULL DEFAULT 1,
  unit_price DECIMAL(10,2) NOT NULL,
  addon_total DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  subtotal DECIMAL(10,2) NOT NULL,
  selected_addons JSON DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_cart_items_cart FOREIGN KEY (cart_id) REFERENCES cart(cart_id) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_cart_items_product FOREIGN KEY (product_id) REFERENCES products(product_id) ON UPDATE CASCADE,
  CONSTRAINT fk_cart_items_size FOREIGN KEY (size_id) REFERENCES product_sizes(size_id) ON DELETE SET NULL ON UPDATE CASCADE,
  INDEX idx_cart_items_cart (cart_id),
  INDEX idx_cart_items_product (product_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE orders (
  order_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  order_number VARCHAR(50) NOT NULL UNIQUE,
  customer_name VARCHAR(200) NOT NULL,
  contact_number VARCHAR(20) NOT NULL,
  delivery_type ENUM('delivery','pickup') NOT NULL DEFAULT 'delivery',
  delivery_address TEXT DEFAULT NULL,
  order_notes TEXT DEFAULT NULL,
  schedule_type ENUM('now','preorder') NOT NULL DEFAULT 'now',
  schedule_datetime DATETIME DEFAULT NULL,
  subtotal DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  discount_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  delivery_fee DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  total_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  order_status ENUM('pending','preparing','ready','delivered','cancelled') NOT NULL DEFAULT 'pending',
  payment_status ENUM('unpaid','paid','failed','refunded') NOT NULL DEFAULT 'unpaid',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_orders_user FOREIGN KEY (user_id) REFERENCES users(user_id) ON UPDATE CASCADE,
  INDEX idx_orders_user (user_id),
  INDEX idx_orders_status (order_status),
  INDEX idx_orders_payment_status (payment_status),
  INDEX idx_orders_schedule (schedule_datetime),
  INDEX idx_orders_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE order_items (
  order_item_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  order_id INT UNSIGNED NOT NULL,
  product_id INT UNSIGNED NOT NULL,
  product_name VARCHAR(150) NOT NULL,
  size_name VARCHAR(50) DEFAULT NULL,
  selected_addons JSON DEFAULT NULL,
  quantity INT UNSIGNED NOT NULL DEFAULT 1,
  unit_price DECIMAL(10,2) NOT NULL,
  addon_total DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  subtotal DECIMAL(10,2) NOT NULL,
  CONSTRAINT fk_order_items_order FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_order_items_product FOREIGN KEY (product_id) REFERENCES products(product_id) ON UPDATE CASCADE,
  INDEX idx_order_items_order (order_id),
  INDEX idx_order_items_product (product_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE payments (
  payment_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  order_id INT UNSIGNED NOT NULL,
  payment_method ENUM('cod','gcash','card') NOT NULL DEFAULT 'cod',
  amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  payment_status ENUM('pending','verified','rejected','refunded') NOT NULL DEFAULT 'pending',
  reference_number VARCHAR(100) DEFAULT NULL,
  proof_of_payment VARCHAR(255) DEFAULT NULL,
  verified_by INT UNSIGNED DEFAULT NULL,
  verified_at DATETIME DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_payments_order FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_payments_verified_by FOREIGN KEY (verified_by) REFERENCES users(user_id) ON DELETE SET NULL ON UPDATE CASCADE,
  INDEX idx_payments_order (order_id),
  INDEX idx_payments_status (payment_status),
  INDEX idx_payments_verified_by (verified_by)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE order_status_history (
  status_history_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  order_id INT UNSIGNED NOT NULL,
  updated_by INT UNSIGNED DEFAULT NULL,
  old_status ENUM('pending','preparing','ready','delivered','cancelled') DEFAULT NULL,
  new_status ENUM('pending','preparing','ready','delivered','cancelled') NOT NULL,
  remarks VARCHAR(255) DEFAULT NULL,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_status_history_order FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_status_history_updated_by FOREIGN KEY (updated_by) REFERENCES users(user_id) ON DELETE SET NULL ON UPDATE CASCADE,
  INDEX idx_status_history_order (order_id),
  INDEX idx_status_history_updated_by (updated_by),
  INDEX idx_status_history_status (new_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO users
(first_name, last_name, email, password_hash, contact_number, delivery_address, role, status)
VALUES
('Demo', 'Customer', 'customer@furrfectcafe.ph', '$2y$12$V9mDCGvnHjd7Y9J7wGwIiOxCgNSRsMNgpAntYohfHDS0Ex.Evf/mq', '09123456789', 'Legazpi City, Albay', 'customer', 'active'),
('Cafe', 'Admin', 'admin@furrfectcafe.ph', '$2y$12$0GpLzr4fhUd8J48IBQwliOhZfhZf/JcnZ8OBwb06QmLLkeWXVIiCq', '09987654321', 'FurrfectCafe Branch', 'admin', 'active');

INSERT INTO categories
(category_id, category_name, category_slug, description, display_order, is_active)
VALUES
(1, 'Hot Drinks', 'hot-drinks', 'Warm coffee and espresso-based drinks', 1, 1),
(2, 'Cold Drinks', 'cold-drinks', 'Iced coffee, milk tea, and chilled café drinks', 2, 1),
(3, 'Pastries', 'pastries', 'Cupcakes, cakes, croissants, and sweet treats', 3, 1),
(4, 'All-Day Bites', 'all-day-bites', 'Sandwiches, waffles, and savory café meals', 4, 1),
(5, 'Fruit Blends', 'fruit-blends', 'Smoothies and fruit coolers', 5, 1);

INSERT INTO products
(product_id, category_id, product_name, product_slug, description, price, image_path, promo_badge, is_available, is_featured, is_bestseller, display_order)
VALUES
(1, 1, 'Signature Cat Latte', 'signature-cat-latte', 'Rich espresso with velvety steamed milk and adorable cat latte art.', 85.00, 'assets/images/signature-cat-latte.jpg', '10% OFF', 1, 1, 1, 1),
(2, 2, 'Matcha Cloud', 'matcha-cloud', 'Premium matcha blended with oat milk and soft cream cloud topping.', 110.00, 'assets/images/matcha-cloud.jpg', NULL, 1, 1, 1, 2),
(3, 3, 'Matcha Cheesecake', 'matcha-cheesecake', 'Creamy New York style cheesecake with a dreamy matcha finish.', 120.00, 'assets/images/matcha-cheesecake.jpg', 'Buy 2 Get 1', 1, 1, 1, 3),
(4, 3, 'Paw Print Cupcake', 'paw-print-cupcake', 'Vanilla sponge with caramel buttercream and a cute paw-print topping.', 65.00, 'assets/images/paw-print-cupcake.jpg', NULL, 1, 1, 0, 4),
(5, 2, 'Brown Sugar Milk Tea', 'brown-sugar-milk-tea', 'Tiger-striped brown sugar milk tea with fresh milk and tapioca pearls.', 95.00, 'assets/images/brown-sugar-milk-tea.jpg', NULL, 1, 0, 1, 5),
(6, 4, 'Cafe Club Sandwich', 'cafe-club-sandwich', 'Triple stack sandwich with chicken, egg, lettuce, and house mustard mayo.', 155.00, 'assets/images/cafe-club-sandwich.jpg', 'New', 1, 0, 1, 6),
(7, 5, 'Mango Passion Cooler', 'mango-passion-cooler', 'Fresh blended mango and passion fruit with a citrusy tropical lift.', 90.00, 'assets/images/mango-passion-cooler.jpg', NULL, 1, 0, 1, 7),
(8, 1, 'Caramel Macchiato', 'caramel-macchiato', 'Layered espresso with silky milk, vanilla sweetness, and caramel drizzle.', 105.00, 'assets/images/caramel-macchiato.jpg', NULL, 1, 0, 1, 8),
(9, 2, 'Iced Americano', 'iced-americano', 'Bold double-shot espresso over ice for a clean and refreshing kick.', 75.00, 'assets/images/iced-americano.jpg', NULL, 1, 0, 0, 9),
(10, 3, 'Croissant', 'croissant', 'Buttery flaky French croissant baked fresh every morning.', 60.00, 'assets/images/croissant.jpg', NULL, 1, 0, 0, 10),
(11, 5, 'Strawberry Smoothie', 'strawberry-smoothie', 'Creamy strawberry smoothie with yogurt and a hint of honey.', 95.00, 'assets/images/strawberry-smoothie.jpg', NULL, 1, 0, 0, 11),
(12, 4, 'Chicken Waffle', 'chicken-waffle', 'Crispy fried chicken on a golden waffle with maple spice glaze.', 175.00, 'assets/images/chicken-waffle.jpg', 'Best Seller', 1, 0, 1, 12);

INSERT INTO product_sizes
(product_id, size_name, price_modifier, is_default, is_available, display_order)
VALUES
(1, 'Small', 0.00, 1, 1, 1),
(1, 'Regular', 10.00, 0, 1, 2),
(1, 'Large', 25.00, 0, 1, 3),
(2, 'Small', 0.00, 1, 1, 1),
(2, 'Regular', 10.00, 0, 1, 2),
(2, 'Large', 25.00, 0, 1, 3),
(5, 'Regular', 0.00, 1, 1, 1),
(5, 'Large', 20.00, 0, 1, 2),
(7, 'Regular', 0.00, 1, 1, 1),
(7, 'Large', 20.00, 0, 1, 2),
(8, 'Small', 0.00, 1, 1, 1),
(8, 'Regular', 10.00, 0, 1, 2),
(8, 'Large', 25.00, 0, 1, 3),
(9, 'Regular', 0.00, 1, 1, 1),
(9, 'Large', 20.00, 0, 1, 2),
(11, 'Regular', 0.00, 1, 1, 1),
(11, 'Large', 20.00, 0, 1, 2);

INSERT INTO product_addons
(product_id, addon_name, addon_price, is_available, display_order)
VALUES
(1, 'Extra Shot', 20.00, 1, 1),
(1, 'Oat Milk', 15.00, 1, 2),
(1, 'Vanilla Syrup', 10.00, 1, 3),
(1, 'Caramel Drizzle', 10.00, 1, 4),
(2, 'Oat Milk', 15.00, 1, 1),
(2, 'Cream Cloud', 20.00, 1, 2),
(5, 'Extra Pearls', 15.00, 1, 1),
(5, 'Cream Cheese', 20.00, 1, 2),
(8, 'Extra Shot', 20.00, 1, 1),
(8, 'Oat Milk', 15.00, 1, 2),
(8, 'Caramel Drizzle', 10.00, 1, 3),
(11, 'Whipped Cream', 15.00, 1, 1),
(11, 'Extra Strawberry', 20.00, 1, 2);

INSERT INTO cart (user_id) VALUES (1);

INSERT INTO orders
(order_id, user_id, order_number, customer_name, contact_number, delivery_type, delivery_address, order_notes, schedule_type, schedule_datetime, subtotal, discount_amount, delivery_fee, total_amount, order_status, payment_status, created_at)
VALUES
(1, 1, 'FC-2026-0043', 'Demo Customer', '09123456789', 'delivery', 'Legazpi City, Albay', 'Please call when rider arrives.', 'now', NULL, 337.50, 0.00, 50.00, 387.50, 'preparing', 'unpaid', '2026-03-27 14:14:00'),
(2, 1, 'FC-2026-0038', 'Demo Customer', '09123456789', 'pickup', 'FurrfectCafe Branch', NULL, 'now', NULL, 245.00, 0.00, 0.00, 245.00, 'delivered', 'paid', '2026-03-20 16:50:00');

INSERT INTO order_items
(order_id, product_id, product_name, size_name, selected_addons, quantity, unit_price, addon_total, subtotal)
VALUES
(1, 1, 'Signature Cat Latte', 'Small', NULL, 2, 85.00, 0.00, 170.00),
(1, 3, 'Matcha Cheesecake', NULL, NULL, 1, 120.00, 0.00, 120.00),
(1, 4, 'Paw Print Cupcake', NULL, NULL, 1, 47.50, 0.00, 47.50),
(2, 5, 'Brown Sugar Milk Tea', 'Regular', NULL, 2, 95.00, 0.00, 190.00),
(2, 10, 'Croissant', NULL, NULL, 1, 55.00, 0.00, 55.00);

INSERT INTO payments
(order_id, payment_method, amount, payment_status, reference_number, verified_by, verified_at)
VALUES
(1, 'cod', 387.50, 'pending', NULL, NULL, NULL),
(2, 'cod', 245.00, 'verified', 'CASH-20260320-2038', 2, '2026-03-20 17:05:00');

INSERT INTO order_status_history
(order_id, updated_by, old_status, new_status, remarks, updated_at)
VALUES
(1, 2, NULL, 'pending', 'Order placed by customer.', '2026-03-27 14:14:00'),
(1, 2, 'pending', 'preparing', 'Order is now being prepared.', '2026-03-27 14:18:00'),
(2, 2, NULL, 'pending', 'Order placed by customer.', '2026-03-20 16:50:00'),
(2, 2, 'pending', 'preparing', 'Order is now being prepared.', '2026-03-20 16:55:00'),
(2, 2, 'preparing', 'ready', 'Order is ready for pick-up.', '2026-03-20 17:02:00'),
(2, 2, 'ready', 'delivered', 'Order completed.', '2026-03-20 17:05:00');
