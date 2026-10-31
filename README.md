# MyBudget Tracker — HTML frontend + MySQL backend

The page URLs stay `.html`. PHP is REQUIRED only for the API endpoints and sessions. Pure HTML cannot directly or securely connect to MySQL. This app is designed for local XAMPP, not static-only GitHub Pages.

## Set up on Windows with XAMPP
1. Install XAMPP with Apache, PHP and MySQL.
2. Copy the folder `MyBudget-Tracker-Database` into `C:\xampp\htdocs\`.
3. Start **Apache** and **MySQL** in the XAMPP Control Panel.
4. Open `http://localhost/phpmyadmin`, select **Import**, choose `database.sql`, and click **Import**. (The SQL creates `mybudget_db`, `users`, and `expenses`.)
5. Open **http://localhost/MyBudget-Tracker-Database/login.html**. Register an account and add expenses.
6. Check tables in phpMyAdmin to confirm your saved data.

## Database configuration
- Edit `api/db.php` or set environment variables `BUDGET_DB_HOST`, `BUDGET_DB_USER`, `BUDGET_DB_PASS`, `BUDGET_DB_NAME`.
- Defaults: `localhost`, `root`, blank password, `mybudget_db` (common XAMPP dev defaults). Never use these on a public server.
- Login now requires a previously registered email (the original code auto-registered unknown emails during login; removed for security).
- Passwords are hashed using `password_hash`; sessions use HTTP-only cookies; API writes require a session CSRF token; expense queries scope records to the logged-in user.
- Files with `.php` under `api/` must stay because MySQL requires server-side access. There is no `index.php` page.
- GitHub Pages, VS Code Live Server, and double-clicking `index.html` CANNOT execute PHP or connect to MySQL. Use localhost via Apache (or a PHP-capable host).
- No real database connection was tested in this environment; validate with your running XAMPP instance.
