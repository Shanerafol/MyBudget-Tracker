const API = {
  create: "api/create_expense.php",
  read: "api/read_expenses.php",
  update: "api/update_expense.php",
  delete: "api/delete_expense.php"
};

let expenses = [];
let editingId = null;
let lineChart, donutChart;

const categories = [
  ["🍔", "Foods"], ["🥤", "Drinks"], ["📚", "School"], ["💅", "Personal"], ["🛒", "Needs"],
  ["💖", "Wants"], ["🥫", "Groceries"], ["💻", "Electronics"], ["💡", "Bills"], ["✨", "Other"]
];

const peso = value => "₱" + Number(value || 0).toLocaleString("en-PH", {minimumFractionDigits:2, maximumFractionDigits:2});
const modal = document.getElementById("expenseModal");
const form = document.getElementById("expenseForm");

document.getElementById("openModal").onclick = () => openModal();
document.getElementById("openExpenseSide").onclick = e => { e.preventDefault(); openModal(); };
document.getElementById("closeModal").onclick = () => closeModal();
document.getElementById("searchInput").oninput = renderTable;

document.querySelectorAll(".nav-link").forEach(link => {
  link.addEventListener("click", e => {
    const page = link.dataset.page;
    if (!page || page === "expenses") return;
    e.preventDefault();
    showPage(page);
    document.querySelectorAll("nav a").forEach(a => a.classList.remove("active"));
    link.classList.add("active");
  });
});

function showPage(page){
  dashboardPage.style.display = page === "dashboard" ? "block" : "none";
  categoriesPage.style.display = page === "categories" ? "block" : "none";
  reportsPage.style.display = page === "reports" ? "block" : "none";
  document.getElementById("expenses-section").style.display = page === "dashboard" || page === "expenses" ? "block" : "none";
  if(page === "categories") renderCategories();
  if(page === "reports") renderReports();
}

function renderCategories(){
  categoryGrid.innerHTML = categories.map(([emoji, name]) => {
    const total = expenses.filter(e => e.category === name).reduce((sum,e)=>sum+Number(e.amount),0);
    return `<div class="category-card"><div class="emoji">${emoji}</div><h3>${name}</h3><p>${peso(total)}</p></div>`;
  }).join("");
}

function renderReports(){
  const total = expenses.reduce((sum,e)=>sum+Number(e.amount),0);
  const highest = expenses.length ? Math.max(...expenses.map(e=>Number(e.amount))) : 0;
  const counts = {};
  expenses.forEach(e => counts[e.category] = (counts[e.category] || 0) + 1);
  const top = Object.keys(counts).sort((a,b)=>counts[b]-counts[a])[0] || "None";

  reportTotal.textContent = peso(total);
  reportRecords.textContent = expenses.length;
  reportHighest.textContent = peso(highest);
  reportTopCategory.textContent = top;
  reportTable.innerHTML = expenses.length ? expenses.map((e,i)=>`
    <tr><td>${i+1}</td><td>${e.title}</td><td>${e.category}</td><td>${peso(e.amount)}</td><td>${e.expense_date}</td></tr>
  `).join("") : `<tr><td colspan="5" class="empty">No reports yet. Add expenses first ✨</td></tr>`;
}

function openModal(expense = null){
  modal.classList.add("show");
  if(expense){
    editingId = expense.id;
    document.getElementById("modalTitle").textContent = "Edit Expense 💗";
    title.value = expense.title;
    category.value = expense.category;
    amount.value = expense.amount;
    expense_date.value = expense.expense_date;
    notes.value = expense.notes || "";
  }else{
    editingId = null;
    document.getElementById("modalTitle").textContent = "Add New Expense 💗";
    form.reset();
    expense_date.value = new Date().toISOString().split("T")[0];
  }
}

function closeModal(){ modal.classList.remove("show"); form.reset(); editingId = null; }

form.onsubmit = async e => {
  e.preventDefault();
  const data = new FormData();
  data.append("title", title.value);
  data.append("category", category.value);
  data.append("amount", amount.value);
  data.append("expense_date", expense_date.value);
  data.append("notes", notes.value);

  if(editingId){
    data.append("id", editingId);
    await fetch(API.update, { method:"POST", body:data });
  }else{
    await fetch(API.create, { method:"POST", body:data });
  }

  closeModal();
  loadExpenses();
};

async function loadExpenses(){
  try{
    const res = await fetch(API.read);
    expenses = await res.json();
    if(!Array.isArray(expenses)) expenses = [];
  }catch(err){
    expenses = [];
  }
  renderAll();
}

function renderAll(){
  renderStats();
  renderTable();
  renderCharts();
  renderCategories();
  renderReports();
}

function renderStats(){
  const total = expenses.reduce((sum,e)=>sum+Number(e.amount),0);
  const now = new Date();
  const weekTotalValue = expenses.filter(e => {
    const d = new Date(e.expense_date);
    const diff = (now - d) / (1000*60*60*24);
    return diff >= 0 && diff <= 7;
  }).reduce((sum,e)=>sum+Number(e.amount),0);
  const monthTotalValue = expenses.filter(e => new Date(e.expense_date).getMonth() === now.getMonth()).reduce((sum,e)=>sum+Number(e.amount),0);

  totalExpenses.textContent = peso(total);
  weekTotal.textContent = peso(weekTotalValue);
  monthTotal.textContent = peso(monthTotalValue);
  recordCount.textContent = expenses.length;
  donutTotal.textContent = peso(total);
}

function renderTable(){
  const q = searchInput.value.toLowerCase();
  const filtered = expenses.filter(e => e.title.toLowerCase().includes(q) || e.category.toLowerCase().includes(q));
  expenseTable.innerHTML = filtered.length ? filtered.map((e,i)=>`
    <tr>
      <td>${i+1}</td>
      <td>${e.title} 💗</td>
      <td><span class="badge">${e.category}</span></td>
      <td>${peso(e.amount)}</td>
      <td>${e.expense_date}</td>
      <td>${e.notes || ""}</td>
      <td>
        <button class="action-btn" onclick='openModal(${JSON.stringify(e)})'>✏️</button>
        <button class="action-btn" onclick="deleteExpense(${e.id})">🗑️</button>
      </td>
    </tr>`).join("") : `<tr><td colspan="7" class="empty">No expenses yet. Click Add Expense ✨</td></tr>`;
}

async function deleteExpense(id){
  if(!confirm("Delete this expense?")) return;
  const data = new FormData();
  data.append("id", id);
  await fetch(API.delete, { method:"POST", body:data });
  loadExpenses();
}

function renderCharts(){
  const labels = expenses.slice(0,7).map(e => e.expense_date).reverse();
  const amounts = expenses.slice(0,7).map(e => Number(e.amount)).reverse();

  const categoryTotals = {};
  expenses.forEach(e => categoryTotals[e.category] = (categoryTotals[e.category] || 0) + Number(e.amount));
  const catLabels = Object.keys(categoryTotals);
  const catValues = Object.values(categoryTotals);

  if(lineChart) lineChart.destroy();
  if(donutChart) donutChart.destroy();

  lineChart = new Chart(document.getElementById("lineChart"), {
    type: "line",
    data: {
      labels: labels.length ? labels : ["No data"],
      datasets: [{
        data: amounts.length ? amounts : [0],
        borderColor: "#ff3f91",
        backgroundColor: "rgba(255,63,145,.12)",
        fill: true,
        tension: .45,
        pointBackgroundColor: "#ff3f91",
        pointRadius: 5
      }]
    },
    options: { plugins:{legend:{display:false}}, scales:{y:{beginAtZero:true}} }
  });

  donutChart = new Chart(document.getElementById("donutChart"), {
    type: "doughnut",
    data: {
      labels: catLabels.length ? catLabels : ["No data"],
      datasets: [{
        data: catValues.length ? catValues : [1],
        backgroundColor: ["#ff5aa5","#b46cff","#ffd166","#7db8ff","#ff8dc7","#8ee8d8","#ffb703","#c77dff","#90dbf4","#f7aef8"],
        borderWidth: 0
      }]
    },
    options: { cutout:"68%", plugins:{legend:{display:false}} }
  });

  const total = catValues.reduce((a,b)=>a+b,0);
  legend.innerHTML = catLabels.length ? catLabels.map((label,i)=>`
    <div class="legend-item">
      <span>${label}</span>
      <strong>${total ? Math.round(catValues[i] / total * 100) : 0}%</strong>
      <span>${peso(catValues[i])}</span>
    </div>`).join("") : `<div class="legend-item"><span>No expenses yet</span><strong>0%</strong><span>₱0.00</span></div>`;
}

loadExpenses();
