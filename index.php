<?php
session_start();
if (!isset($_SESSION['user_id'])) {
  header("Location: login.html");
  exit;
}
$userEmail = $_SESSION['user_email'];
$userName = $_SESSION['user_name'];

date_default_timezone_set('Asia/Manila');
$currentHour = (int) date('H');

if ($currentHour >= 5 && $currentHour < 12) {
  $greeting = "Good morning";
} elseif ($currentHour >= 12 && $currentHour < 18) {
  $greeting = "Good afternoon";
} else {
  $greeting = "Good evening";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>MyBudget | Expense Tracker</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Pacifico&family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="assets/style.css" />

  <style>
    .notification-wrap {
      position: relative;
    }

    .notification-wrap .bell {
      width: 54px;
      height: 54px;
      border: 1px solid var(--line);
      background: white;
      border-radius: 14px;
      color: #b01862;
      box-shadow: var(--shadow);
      cursor: pointer;
      position: relative;
    }

    .notification-wrap .bell span {
      position: absolute;
      top: -7px;
      right: -7px;
      background: #ff3f91;
      color: white;
      border-radius: 50%;
      font-size: 12px;
      padding: 3px 7px;
      font-weight: 700;
    }

    .notification-dropdown {
      display: none;
      position: absolute;
      right: 0;
      top: 68px;
      width: 340px;
      background: rgba(255, 255, 255, 0.96);
      border: 1px solid var(--line);
      border-radius: 20px;
      box-shadow: 0 20px 50px rgba(255, 63, 145, 0.18);
      z-index: 50;
      overflow: hidden;
    }

    .notification-dropdown.show {
      display: block;
    }

    .notification-head {
      padding: 18px 20px;
      background: linear-gradient(135deg, #fff0f8, #ffe1ef);
      border-bottom: 1px solid var(--line);
    }

    .notification-head h3 {
      margin: 0;
      color: #c21865;
      font-size: 18px;
    }

    .notification-head small {
      color: #7d6a8e;
    }

    .notification-list {
      max-height: 310px;
      overflow-y: auto;
    }

    .notification-item {
      padding: 15px 20px;
      border-bottom: 1px solid #ffe0ee;
    }

    .notification-item:last-child {
      border-bottom: none;
    }

    .notification-item strong {
      display: block;
      color: #241d3f;
      font-size: 14px;
      margin-bottom: 4px;
    }

    .notification-item small {
      color: #766b92;
      line-height: 1.4;
    }
  </style>

</head>
<body>
  <aside class="sidebar">
    <div class="brand"><div class="bow">🎀</div><div><h1>MyBudget</h1><p>Expense Tracker 💗</p></div></div>
    <nav>
      <a class="nav-link active" href="#" data-page="dashboard"><i class="fa-solid fa-house"></i>Dashboard</a>
      <a class="nav-link" href="#expenses-section" data-page="expenses"><i class="fa-solid fa-wallet"></i>Expenses</a>
      <a href="#" id="openExpenseSide"><i class="fa-solid fa-circle-plus"></i>Add Expense</a>
      <a class="nav-link" href="#" data-page="categories"><i class="fa-solid fa-table-cells-large"></i>Categories</a>
      <a class="nav-link" href="#" data-page="reports"><i class="fa-solid fa-chart-column"></i>Reports</a>
      <a href="logout.php"><i class="fa-solid fa-right-from-bracket"></i>Logout</a>
    </nav>
    <div class="decor"><span class="flower">🌸</span><span class="butterfly">🦋</span><span class="sparkle">✨</span></div>
    <div class="premium"><div class="crown">👑</div><h3>Go Premium 💎</h3><p>Unlock advanced reports, custom categories and more amazing features.</p><button>Upgrade Now ✨</button></div>
    <div class="profile"><div class="avatar">👩🏻</div><div><strong>Hello, <?php echo htmlspecialchars($userName); ?>! 👑</strong><small><?php echo htmlspecialchars($userEmail); ?></small></div><i class="fa-solid fa-chevron-down"></i></div>
  </aside>

  <main class="main">
    <header class="topbar">
      <button class="mobile-menu"><i class="fa-solid fa-bars"></i></button>
      <div></div>
      <div class="date-pill"><i class="fa-regular fa-calendar-days"></i> MyBudget Tracker 💗</div>
      <div class="notification-wrap">
        <button class="bell" id="notificationBell" type="button">
          <i class="fa-regular fa-bell"></i>
          <span id="notificationCount">0</span>
        </button>

        <div class="notification-dropdown" id="notificationDropdown">
          <div class="notification-head">
            <h3>System Activity 💗</h3>
            <small>Latest updates</small>
          </div>
          <div class="notification-list" id="notificationList">
            <div class="notification-item">
              <strong>Loading...</strong>
              <small>Please wait while we check your activities.</small>
            </div>
          </div>
        </div>
      </div>
    </header>

    <section class="page-section" id="dashboardPage">
      <section class="welcome">
        <div><h2><?php echo $greeting; ?>, <?php echo htmlspecialchars($userName); ?>! 👑</h2><p>Here’s what's happening with your expenses today. 💗</p></div>
        <button class="add-btn" id="openModal"><i class="fa-solid fa-plus"></i> Add Expense ✨</button>
      </section>

      <section class="stats">
        <div class="stat-card"><div class="icon pink"><i class="fa-solid fa-wallet"></i></div><div><p>Total Expenses</p><h3 id="totalExpenses">₱0.00</h3><small class="green">Saved in database</small></div><span>✨</span></div>
        <div class="stat-card"><div class="icon lavender"><i class="fa-solid fa-arrow-trend-up"></i></div><div><p>This Week</p><h3 id="weekTotal">₱0.00</h3><small class="green">Current week</small></div></div>
        <div class="stat-card"><div class="icon rose"><i class="fa-solid fa-bag-shopping"></i></div><div><p>This Month</p><h3 id="monthTotal">₱0.00</h3><small class="hot">Current month</small></div></div>
        <div class="stat-card"><div class="icon purple"><i class="fa-solid fa-credit-card"></i></div><div><p>Total Records</p><h3 id="recordCount">0</h3><small>Saved expenses</small></div><span>💖</span></div>
      </section>

      <section class="charts">
        <div class="panel overview"><div class="panel-head"><h3>Expenses Overview <span>💗</span></h3><button>This Week <i class="fa-solid fa-chevron-down"></i></button></div><canvas id="lineChart"></canvas></div>
        <div class="panel category"><div class="panel-head"><h3>Expenses by Category <span>💗</span></h3><button>This Month <i class="fa-solid fa-chevron-down"></i></button></div><div class="category-flex"><div class="donut-wrap"><canvas id="donutChart"></canvas><div class="donut-center"><strong id="donutTotal">₱0.00</strong><span>Total</span><em>🎀</em></div></div><div class="legend" id="legend"></div></div></div>
      </section>
    </section>

    <section class="page-section" id="categoriesPage" style="display:none">
      <div class="page-title"><h2>Categories 💗</h2><p>Choose where your expenses belong.</p></div>
      <div class="category-grid" id="categoryGrid"></div>
    </section>

    <section class="page-section" id="reportsPage" style="display:none">
      <div class="page-title"><h2>All Reports 📊</h2><p>Summary report of your saved expenses.</p></div>
      <section class="report-grid">
        <div class="report-card"><h3>Total Expenses</h3><strong id="reportTotal">₱0.00</strong></div>
        <div class="report-card"><h3>Total Records</h3><strong id="reportRecords">0</strong></div>
        <div class="report-card"><h3>Highest Expense</h3><strong id="reportHighest">₱0.00</strong></div>
        <div class="report-card"><h3>Most Used Category</h3><strong id="reportTopCategory">None</strong></div>
      </section>
      <section class="panel table-panel">
        <div class="table-head"><div><h3>Expense Report List <span>💗</span></h3></div></div>
        <div class="table-responsive"><table><thead><tr><th>#</th><th>Title</th><th>Category</th><th>Amount</th><th>Date</th></tr></thead><tbody id="reportTable"></tbody></table></div>
      </section>
    </section>

    <section class="panel table-panel page-section" id="expenses-section">
      <div class="table-head"><div><h3>Recent Expenses <span>💗</span></h3></div><div><input type="search" id="searchInput" placeholder="Search expense or category..."><a href="#">View All</a></div></div>
      <div class="table-responsive"><table><thead><tr><th>#</th><th>Title</th><th>Category</th><th>Amount</th><th>Date</th><th>Notes</th><th>Action</th></tr></thead><tbody id="expenseTable"><tr><td colspan="7" class="empty">No expenses yet. Click Add Expense ✨</td></tr></tbody></table></div>
    </section>
  </main>

  <div class="modal" id="expenseModal">
    <form class="modal-card" id="expenseForm">
      <button type="button" class="close" id="closeModal">×</button>
      <h2 id="modalTitle">Add New Expense 💗</h2>
      <input type="hidden" id="expenseId">
      <label>Title<input id="title" name="title" required placeholder="e.g. Milk tea"></label>
      <label>Category<select id="category" name="category" required><option>Foods</option><option>Drinks</option><option>School</option><option>Personal</option><option>Needs</option><option>Wants</option><option>Groceries</option><option>Electronics</option><option>Bills</option><option>Other</option></select></label>
      <label>Amount<input id="amount" name="amount" required type="number" step="0.01" placeholder="0.00"></label>
      <label>Date<input id="expense_date" name="expense_date" required type="date"></label>
      <label>Notes<textarea id="notes" name="notes" placeholder="Optional notes"></textarea></label>
      <button class="save-btn" type="submit">Save Expense ✨</button>
    </form>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script src="assets/script.js"></script>

  <script>
    const notificationBell = document.getElementById("notificationBell");
    const notificationDropdown = document.getElementById("notificationDropdown");
    const notificationList = document.getElementById("notificationList");
    const notificationCount = document.getElementById("notificationCount");

    notificationBell.addEventListener("click", function(event) {
      event.stopPropagation();
      notificationDropdown.classList.toggle("show");
    });

    document.addEventListener("click", function(event) {
      if (!notificationDropdown.contains(event.target) && !notificationBell.contains(event.target)) {
        notificationDropdown.classList.remove("show");
      }
    });

    async function loadSystemActivities() {
      let systemExpenses = [];

      try {
        const response = await fetch("api/read_expenses.php");
        systemExpenses = await response.json();

        if (!Array.isArray(systemExpenses)) {
          systemExpenses = [];
        }
      } catch (error) {
        systemExpenses = [];
      }

      const activities = [];
      const today = new Date().toISOString().split("T")[0];

      activities.push({
        title: "Login activity",
        message: "You successfully opened your MyBudget dashboard today."
      });

      if (systemExpenses.length === 0) {
        activities.push({
          title: "No expenses recorded yet",
          message: "Start by clicking Add Expense to save your first spending record."
        });
      } else {
        const total = systemExpenses.reduce((sum, expense) => sum + Number(expense.amount || 0), 0);
        const todayExpenses = systemExpenses.filter(expense => expense.expense_date === today);
        const latestExpense = systemExpenses[0];

        activities.push({
          title: "Expense records loaded",
          message: `You currently have ${systemExpenses.length} saved expense record(s).`
        });

        activities.push({
          title: "Total spending update",
          message: `Your total recorded expenses are ₱${total.toLocaleString("en-PH", { minimumFractionDigits: 2, maximumFractionDigits: 2 })}.`
        });

        if (latestExpense) {
          activities.push({
            title: "Latest expense",
            message: `${latestExpense.title} was saved under ${latestExpense.category} for ₱${Number(latestExpense.amount).toLocaleString("en-PH", { minimumFractionDigits: 2, maximumFractionDigits: 2 })}.`
          });
        }

        if (todayExpenses.length > 0) {
          activities.push({
            title: "Today’s activity",
            message: `You added ${todayExpenses.length} expense(s) today.`
          });
        } else {
          activities.push({
            title: "Daily reminder",
            message: "You have not added an expense today. Don’t forget to track your spending."
          });
        }
      }

      notificationCount.textContent = activities.length;

      notificationList.innerHTML = activities.map(activity => `
        <div class="notification-item">
          <strong>${activity.title}</strong>
          <small>${activity.message}</small>
        </div>
      `).join("");
    }

    loadSystemActivities();
  </script>

</body>
</html>
