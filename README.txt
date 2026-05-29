MYBUDGET TRACKER APP DEV FINAL EXAM - Register/Login + Users Database + Reports/Categories

CHANGES:
- Added register.html and register.php.
- Login page has no default text values.
- Removed the text: "Soft, professional, and complete CRUD expense tracker using PHP API and MySQL."
- Successful registration saves the user in the users table.
- Login checks the users table. If email is new, it is also saved in users table after login.
- Expenses are linked to the logged-in user.
- No default users and no default expenses.
- Categories menu now displays:
  Foods, Drinks, School, Personal, Needs, Wants, Groceries, Electronics, Bills, Other
- Reports menu now displays all reports and expense report list.

HOW TO RUN:
1. Copy this folder to C:/xampp/htdocs/
2. Start Apache and MySQL.
3. Open phpMyAdmin.
4. Import database.sql.
5. Open:
   http://localhost/mybudget_with_register_users_reports/login.html

MAIN FILES:
- login.html
- register.html
- login.php
- register.php
- logout.php
- index.php
- database.sql
- api/
- assets/
