FurrfectCafe PHP + MySQL Setup

This version keeps the original front-end layout/design and adds PHP/MySQL files for database connection and real login/register sessions.

How to run:
1. Copy this folder to C:\xampp\htdocs\ecomm-project
2. Start Apache and MySQL in XAMPP.
3. Open http://localhost/phpmyadmin
4. Create a database named furrfectcafe_db
5. Import database/furrfectcafe_db_final.sql
6. Test connection: http://localhost/ecomm-project/test-db.php
7. Open: http://localhost/ecomm-project/login.php

Demo accounts:
Customer: customer@furrfectcafe.ph / customer123
Admin: admin@furrfectcafe.ph / admin123

Important:
Use localhost through XAMPP, not VS Code Live Server. PHP will not run in Live Server.

Current backend connection included:
- Database connection
- PHP sessions
- Customer login
- Admin login
- Customer registration
- Logout
- Protected customer/admin pages

The menu/cart/order visuals are preserved from the original front-end prototype. They can be connected to full database CRUD next without changing the design.
