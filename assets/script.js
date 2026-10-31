
const API = {
  create: 'create_expense.php',
  read: 'read_expenses.php',
  update: 'update_expense.php',
  delete: 'delete_expense.php',
  saveBudget: 'save_budget.php',
  readBudgets: 'read_budgets.php'
};

let expenses = [];
let monthlyBudgets = {};
let editingId = null;
let lineChart, donutChart;
let dataReady = false;

const categories = [
  ["🍔", "Foods"], ["🥤", "Drinks"],
  ["📚", "School"], ["💅", "Personal"],
  ["🛒", "Needs"], ["💖", "Wants"],
  ["🥫", "Groceries"], ["💻", "Electronics"],
  ["💡", "Bills"], ["✨", "Other"]
];

const $ = id => document.getElementById(id);

const escapeHtml = value =>
  String(value ?? '').replace(/[&<>"']/g, ch => ({
    '&': '&amp;', '<': '&lt;', '>': '&gt;',
    '"': '&quot;', "'": '&#39;'
  }[ch]));

const peso = value =>
  '₱' + Number(value || 0).toLocaleString('en-PH', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  });

function localMonth(date = new Date()) {
  return date.getFullYear() + '-' +
    String(date.getMonth() + 1).padStart(2, '0');
}

const currentMonth = () => localMonth();

function todayLocal() {
  const d = new Date();
  return localMonth(d) + '-' +
    String(d.getDate()).padStart(2, '0');
}

const expenseMonth = date => String(date || '').slice(0, 7);

const hasBudget = month =>
  Object.prototype.hasOwnProperty.call(monthlyBudgets, month);

const modal = $('expenseModal');
const form = $('expenseForm');
const budgetModal = $('budgetModal');
const budgetForm = $('budgetForm');

// ====================================
// BUDGET-FIRST INTERFACE
// ====================================

function renderBudget() {
  const month = currentMonth();
  const set = hasBudget(month);
  const budget = Number(monthlyBudgets[month] || 0);

  const spent = expenses
    .filter(e => expenseMonth(e.expense_date) === month)
    .reduce((sum, e) => sum + Number(e.amount), 0);

  // Only show the setup card when there is no budget.
  $('budgetSetup').hidden = set;
  $('budgetOverview').hidden = !set;

  // Disable Add Expense until the budget is saved.
  $('openModal').disabled = !dataReady || !set;

  $('addExpenseIcon').className =
    set ? 'fa-solid fa-plus' : 'fa-solid fa-lock';

  $('addExpenseLabel').textContent = set
    ? 'Add Expense ✨'
    : 'Add Expense (Locked)';

  $('openExpenseSide').classList.toggle('locked-link', !set);
  $('openExpenseSide').setAttribute(
    'aria-disabled', String(!set)
  );

  // Budget summary values.
  $('budgetMonthLabel').textContent =
    new Date(month + '-01T12:00:00')
      .toLocaleDateString('en-PH', {
        month: 'long',
        year: 'numeric'
      });

  $('monthlyBudget').textContent =
    set ? peso(budget) : 'Not set';

  $('monthlySpent').textContent = peso(spent);

  $('remainingBudget').textContent =
    set ? peso(budget - spent) : '—';

  const percent =
    set && budget > 0 ? (spent / budget) * 100 : 0;

  const visiblePercent = Math.min(
    100, Math.max(0, percent)
  );

  $('budgetProgressBar').style.width = visiblePercent + '%';

  $('budgetProgress').setAttribute(
    'aria-valuenow', String(Math.round(visiblePercent))
  );

  $('budgetProgress').classList.toggle(
    'over', percent > 100
  );

  $('budgetMessage').textContent =
    !set
      ? 'Set your monthly budget before adding expenses.'
      : percent > 100
        ? 'You have exceeded this month’s budget.'
        : `${Math.round(percent)}% of your monthly budget used.`;

  window.refreshSystemActivities?.();
}

// ====================================
// BUDGET MODAL
// ====================================

function showBudgetModal(month = currentMonth()) {
  $('budgetMonth').value = month;
  $('budgetAmount').value =
    hasBudget(month) ? monthlyBudgets[month] : '';

  $('budgetError').textContent = '';
  budgetModal.classList.add('show');
  $('budgetAmount').focus();
}

function hideBudgetModal() {
  budgetModal.classList.remove('show');
}

$('startBudgetSetup').onclick =
  () => showBudgetModal();

$('openBudget').onclick =
  () => showBudgetModal();

$('openBudgetSide').onclick = e => {
  e.preventDefault();
  showBudgetModal();
};

$('closeBudgetModal').onclick = hideBudgetModal;

$('budgetMonth').addEventListener('change', e => {
  const month = e.target.value;
  $('budgetAmount').value =
    hasBudget(month) ? monthlyBudgets[month] : '';
});

budgetForm.addEventListener('submit', async e => {
  e.preventDefault();

  const error = $('budgetError');
  const btn = budgetForm.querySelector('[type="submit"]');

  error.textContent = '';
  btn.disabled = true;

  try {
    const data = new FormData(budgetForm);

    await apiRequest(API.saveBudget, {
      method: 'POST',
      body: data
    });

    // Reload from MySQL before changing the interface.
    await loadBudgets();

    hideBudgetModal();

    if (hasBudget(currentMonth())) {
      renderAll();
    }

  } catch (err) {
    error.textContent = err.message;
  } finally {
    btn.disabled = false;
  }
});

async function loadBudgets() {
  const result = await apiRequest(API.readBudgets);

  if (!Array.isArray(result.budgets)) {
    throw new Error('Unable to retrieve monthly budgets.');
  }

  monthlyBudgets = {};

  result.budgets.forEach(b => {
    monthlyBudgets[b.month] = Number(b.amount);
  });

  renderBudget();
}

// ====================================
// EXPENSE MODAL
// ====================================

$('openModal').onclick = () => openModal();

$('openExpenseSide').onclick = e => {
  e.preventDefault();

  if (!dataReady || !hasBudget(currentMonth())) {
    showBudgetModal();
    return;
  }

  openModal();
};

$('closeModal').onclick = closeModal;
$('searchInput').oninput = renderTable;

function openModal(expense = null) {
  if (!dataReady) {
    alert('Please wait until your budget and expenses have loaded.');
    return;
  }

  const month = expense
    ? expenseMonth(expense.expense_date)
    : currentMonth();

  if (!hasBudget(month)) {
    showBudgetModal(month);
    return;
  }

  modal.classList.add('show');

  if (expense) {
    editingId = expense.id;

    $('modalTitle').textContent = 'Edit Expense 💗';
    $('title').value = expense.title;
    $('category').value = expense.category;
    $('amount').value = expense.amount;
    $('expense_date').value = expense.expense_date;
    $('notes').value = expense.notes || '';

  } else {
    editingId = null;

    $('modalTitle').textContent = 'Add New Expense 💗';
    form.reset();
    $('expense_date').value = todayLocal();
  }
}

function closeModal() {
  modal.classList.remove('show');
  form.reset();
  editingId = null;
}

// ====================================
// SAVE EXPENSE
// ====================================

form.onsubmit = async e => {
  e.preventDefault();

  const data = new FormData(form);
  const month = expenseMonth(data.get('expense_date'));

  if (!hasBudget(month)) {
    alert('Set a budget for ' + month + ' first.');
    closeModal();
    showBudgetModal(month);
    return;
  }

  if (editingId !== null) {
    data.set('id', editingId);
  }

  const btn = form.querySelector('[type="submit"]');
  btn.disabled = true;

  try {
    await apiRequest(
      editingId !== null ? API.update : API.create,
      { method: 'POST', body: data }
    );

    closeModal();
    await loadExpenses();

  } catch (err) {
    alert(err.message);
  } finally {
    btn.disabled = false;
  }
};

// ====================================
// LOAD EXPENSES
// ====================================

async function loadExpenses() {
  const records = await apiRequest(API.read);

  if (!Array.isArray(records)) {
    throw new Error('Unable to retrieve expenses.');
  }

  expenses = records;
  renderAll();
}

// ====================================
// NAVIGATION
// ====================================

document.querySelectorAll('.nav-link').forEach(link => {
  link.addEventListener('click', e => {
    const page = link.dataset.page;

    if (!page) return;

    e.preventDefault();
    showPage(page);

    document.querySelectorAll('nav a').forEach(a =>
      a.classList.remove('active')
    );

    link.classList.add('active');
  });
});

function showPage(page) {
  $('dashboardPage').style.display =
    page === 'dashboard' ? 'block' : 'none';

  $('categoriesPage').style.display =
    page === 'categories' ? 'block' : 'none';

  $('reportsPage').style.display =
    page === 'reports' ? 'block' : 'none';

  $('expenses-section').style.display =
    page === 'dashboard' || page === 'expenses'
      ? 'block'
      : 'none';

  if (page === 'expenses') {
    $('expenses-section').scrollIntoView({
      behavior: 'smooth'
    });
  }

  if (page === 'categories') renderCategories();
  if (page === 'reports') renderReports();
}

// ====================================
// RENDER EVERYTHING
// ====================================

function renderAll() {
  renderStats();
  renderBudget();
  renderTable();
  renderCategories();
  renderReports();
  renderCharts();
}

// ====================================
// DASHBOARD STATISTICS
// ====================================

function renderStats() {
  const total = expenses.reduce(
    (sum, e) => sum + Number(e.amount), 0
  );

  const today = new Date();
  const current = currentMonth();

  // Monday-start calendar week.
  const weekStart = new Date(
    today.getFullYear(),
    today.getMonth(),
    today.getDate()
  );

  const day = (weekStart.getDay() + 6) % 7;
  weekStart.setDate(weekStart.getDate() - day);

  const weekStartDate =
    localMonth(weekStart) + '-' +
    String(weekStart.getDate()).padStart(2, '0');

  const weekTotalValue = expenses
    .filter(e =>
      e.expense_date >= weekStartDate &&
      e.expense_date <= todayLocal()
    )
    .reduce((sum, e) => sum + Number(e.amount), 0);

  const monthTotalValue = expenses
    .filter(e => expenseMonth(e.expense_date) === current)
    .reduce((sum, e) => sum + Number(e.amount), 0);

  $('totalExpenses').textContent = peso(total);
  $('weekTotal').textContent = peso(weekTotalValue);
  $('monthTotal').textContent = peso(monthTotalValue);
  $('recordCount').textContent = expenses.length;
  $('donutTotal').textContent = peso(total);
}

// ====================================
// EXPENSE TABLE
// ====================================

function renderTable() {
  const q = $('searchInput').value.trim().toLowerCase();

  const filtered = expenses.filter(e =>
    String(e.title).toLowerCase().includes(q) ||
    String(e.category).toLowerCase().includes(q)
  );

  $('expenseTable').innerHTML = filtered.length
    ? filtered.map((e, i) => `
      <tr>
        <td>${i + 1}</td>
        <td>${escapeHtml(e.title)} 💗</td>
        <td>
          <span class="badge">${escapeHtml(e.category)}</span>
        </td>
        <td>${peso(e.amount)}</td>
        <td>${escapeHtml(e.expense_date)}</td>
        <td>${escapeHtml(e.notes || '')}</td>
        <td>
          <button class="action-btn"
                  data-action="edit"
                  data-id="${Number(e.id)}">✏️</button>
          <button class="action-btn"
                  data-action="delete"
                  data-id="${Number(e.id)}">🗑️</button>
        </td>
      </tr>
    `).join('')
    : `<tr>
         <td colspan="7" class="empty">
           No expenses found. 💗
         </td>
       </tr>`;
}

$('expenseTable').addEventListener('click', e => {
  const button = e.target.closest('button[data-action]');

  if (!button) return;

  const id = Number(button.dataset.id);

  if (button.dataset.action === 'edit') {
    editExpenseById(id);
  }

  if (button.dataset.action === 'delete') {
    deleteExpense(id);
  }
});

function editExpenseById(id) {
  const record = expenses.find(
    e => Number(e.id) === Number(id)
  );

  if (record) openModal(record);
}

async function deleteExpense(id) {
  if (!confirm('Delete this expense?')) return;

  const data = new FormData();
  data.set('id', id);

  try {
    await apiRequest(API.delete, {
      method: 'POST',
      body: data
    });

    await loadExpenses();

  } catch (err) {
    alert(err.message);
  }
}

// ====================================
// CATEGORIES
// ====================================

function renderCategories() {
  $('categoryGrid').innerHTML =
    categories.map(([emoji, name]) => {
      const total = expenses
        .filter(e => e.category === name)
        .reduce((sum, e) => sum + Number(e.amount), 0);

      return `
        <div class="category-card">
          <div class="emoji">${emoji}</div>
          <h3>${name}</h3>
          <p>${peso(total)}</p>
        </div>
      `;
    }).join('');
}

// ====================================
// REPORTS
// ====================================

function renderReports() {
  const total = expenses.reduce(
    (sum, e) => sum + Number(e.amount), 0
  );

  const highest = expenses.length
    ? Math.max(...expenses.map(e => Number(e.amount)))
    : 0;

  const counts = {};

  expenses.forEach(e => {
    counts[e.category] = (counts[e.category] || 0) + 1;
  });

  const top = Object.keys(counts)
    .sort((a, b) => counts[b] - counts[a])[0] || 'None';

  $('reportTotal').textContent = peso(total);
  $('reportRecords').textContent = expenses.length;
  $('reportHighest').textContent = peso(highest);
  $('reportTopCategory').textContent = top;

  $('reportTable').innerHTML = expenses.length
    ? expenses.map((e, i) => `
      <tr>
        <td>${i + 1}</td>
        <td>${escapeHtml(e.title)}</td>
        <td>${escapeHtml(e.category)}</td>
        <td>${peso(e.amount)}</td>
        <td>${escapeHtml(e.expense_date)}</td>
      </tr>
    `).join('')
    : `<tr>
         <td colspan="5" class="empty">No reports yet. 💗</td>
       </tr>`;
}

// ====================================
// CHARTS
// ====================================

function renderCharts() {
  const labels = expenses.slice(0, 7)
    .map(e => e.expense_date).reverse();

  const amounts = expenses.slice(0, 7)
    .map(e => Number(e.amount)).reverse();

  const categoryTotals = {};

  expenses.forEach(e => {
    categoryTotals[e.category] =
      (categoryTotals[e.category] || 0) +
      Number(e.amount);
  });

  const catLabels = Object.keys(categoryTotals);
  const catValues = Object.values(categoryTotals);

  if (lineChart) lineChart.destroy();
  if (donutChart) donutChart.destroy();

  lineChart = new Chart($('lineChart'), {
    type: 'line',
    data: {
      labels: labels.length ? labels : ['No data'],
      datasets: [{
        data: amounts.length ? amounts : [0],
        borderColor: '#ff3f91',
        backgroundColor: 'rgba(255,63,145,.12)',
        fill: true,
        tension: .45,
        pointBackgroundColor: '#ff3f91',
        pointRadius: 5
      }]
    },
    options: {
      plugins: { legend: { display: false } },
      scales: { y: { beginAtZero: true } }
    }
  });

  donutChart = new Chart($('donutChart'), {
    type: 'doughnut',
    data: {
      labels: catLabels.length ? catLabels : ['No data'],
      datasets: [{
        data: catValues.length ? catValues : [1],
        backgroundColor: [
          '#ff5aa5', '#b46cff', '#ffd166',
          '#7db8ff', '#ff8dc7', '#8ee8d8',
          '#ffb703', '#c77dff', '#90dbf4',
          '#f7aef8'
        ],
        borderWidth: 0
      }]
    },
    options: {
      cutout: '68%',
      plugins: { legend: { display: false } }
    }
  });

  const total = catValues.reduce((a, b) => a + b, 0);

  $('legend').innerHTML = catLabels.length
    ? catLabels.map((label, i) => `
      <div class="legend-item">
        <span>${escapeHtml(label)}</span>
        <strong>${total
          ? Math.round(catValues[i] / total * 100)
          : 0}%</strong>
        <span>${peso(catValues[i])}</span>
      </div>
    `).join('')
    : `<div class="legend-item">
         <span>No expenses yet</span>
         <strong>0%</strong>
         <span>₱0.00</span>
       </div>`;
}

// ====================================
// INITIAL SESSION
// ====================================

(async () => {
  try {
    const session = await initializeSession();

    if (!session.authenticated) {
      location.replace('login.html');
      return;
    }

    document.querySelectorAll('.user-name').forEach(
      el => el.textContent = session.user.name
    );

    document.querySelectorAll('.user-email').forEach(
      el => el.textContent = session.user.email
    );

    const hour = new Date().getHours();

    $('greetingText').textContent =
      hour >= 5 && hour < 12
        ? 'Good morning'
        : hour >= 12 && hour < 18
          ? 'Good afternoon'
          : 'Good evening';

    await loadBudgets();
    await loadExpenses();

    dataReady = true;
    renderBudget();

  } catch (err) {
    console.error(err);
    alert('Unable to connect: ' + err.message);
  }
})();

// ====================================
// LOGOUT
// ====================================

$('logoutLink').addEventListener('click', async e => {
  e.preventDefault();

  try {
    await apiRequest('logout.php', { method: 'POST' });
    location.replace('login.html');
  } catch (err) {
    alert(err.message);
  }
});
