// Use a relative API base to avoid issues with spaces or URL encoding
const apiBase = 'api';
let currentUser = null;

document.addEventListener('DOMContentLoaded', ()=>{
  const body = document.body;
  const toggle = document.getElementById('toggleTheme');
  toggle.addEventListener('click', ()=>{ body.classList.toggle('dark'); toggle.textContent = body.classList.contains('dark') ? 'Light' : 'Dark'; });

  // navigation
  document.querySelectorAll('.sidebar .nav-link').forEach(a=>a.addEventListener('click', (e)=>{ e.preventDefault(); showView(a.dataset.view); document.querySelectorAll('.sidebar .nav-link').forEach(x=>x.classList.remove('active')); a.classList.add('active'); }));

  // login button
  document.getElementById('btnLogin').addEventListener('click', ()=>{ showModal('#loginModal'); });

  document.getElementById('loginForm').addEventListener('submit', async (e)=>{
    e.preventDefault();
    const fd = new FormData(e.target);
    const payload = { email: fd.get('email'), password: fd.get('password') };
    const res = await fetch(apiBase + '/auth.php?action=login',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(payload)});
    const j = await res.json();
    if (j.user) { currentUser = j.user; afterLogin(); hideModal('#loginModal'); } else alert('Login failed');
  });

  document.getElementById('btnRegister').addEventListener('click', ()=>{
    const form = document.getElementById('loginForm'); const fd = new FormData(form); const email = fd.get('email'), name = prompt('Name for registration'); const pass = fd.get('password'); if (!name) return; fetch(apiBase + '/auth.php?action=register',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({email,name,password:pass})}).then(r=>r.json()).then(j=>{ if (j.success) alert('Registered. Please sign in.'); else alert('Registration failed'); });
  });

  // Quick create
  document.getElementById('quickCreate').addEventListener('submit', async (e)=>{
    e.preventDefault();
    const fd = new FormData(e.target);
    const payload = { title: fd.get('title'), priority: fd.get('priority') };
    const res = await fetch(apiBase + '/tasks.php?action=create',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(payload)});
    const j = await res.json();
    if (j.success) { loadTasks(); alert('Task created'); }
  });

  // New Task button
  document.getElementById('btnNewTask').addEventListener('click', ()=>{ openTaskModal(); });

  // Task form save
  document.getElementById('saveTask').addEventListener('click', async ()=>{
    const form = document.getElementById('taskForm');
    const data = Object.fromEntries(new FormData(form).entries());
    // handle subtasks string -> array
    if (data.subtasks) data.subtasks = data.subtasks.split(/\r?\n/).map(s=>s.trim()).filter(Boolean);
    const id = data.id;
    if (id) {
      await fetch(apiBase + '/tasks.php?action=update',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(data)});
      if (form.querySelector('input[name="attachment"]').files.length) await uploadAttachment(id, form.querySelector('input[name="attachment"]'));
    } else {
      const res = await fetch(apiBase + '/tasks.php?action=create',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(data)});
      const j = await res.json();
      if (j.id) {
        if (form.querySelector('input[name="attachment"]').files.length) await uploadAttachment(j.id, form.querySelector('input[name="attachment"]'));
      }
    }
    hideModal('#taskModal'); loadTasks();
  });

  // daily form
  document.getElementById('dailyForm').addEventListener('submit', async (e)=>{
    e.preventDefault(); const fd = new FormData(e.target); const title = fd.get('title'); if (!title) return;
    // Persist to server
    const res = await fetch(apiBase + '/daily.php', {method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify({title})});
    const j = await res.json(); if (j.success) { loadDaily(); }
    e.target.reset();
  });

  // load initial data
  showView('dashboard'); fetchCurrentUser();
});

function afterLogin(){
  document.getElementById('currentUser').textContent = currentUser.name + ' (' + (currentUser.role_id==1? 'Admin' : currentUser.role_id==2? 'Team Leader' : 'Member') + ')';
  document.getElementById('btnLogin').textContent = 'Sign Out';
  document.getElementById('btnLogin').removeEventListener('click', ()=>{});
  document.getElementById('btnLogin').addEventListener('click', async ()=>{ await fetch(apiBase + '/auth.php?action=logout',{method:'POST'}); currentUser=null; location.reload(); });
  loadTasks(); loadDashboard(); loadUsersIfAdmin();
}

async function fetchCurrentUser(){ const res = await fetch(apiBase + '/auth.php'); const j = await res.json(); if (j.user) { currentUser = j.user; afterLogin(); } }

function showModal(sel){ const el = document.querySelector(sel); const modal = new bootstrap.Modal(el); modal.show(); }
function hideModal(sel){ const el = document.querySelector(sel); const m = bootstrap.Modal.getInstance(el); if (m) m.hide(); }

function openTaskModal(task){
  const form = document.getElementById('taskForm'); form.reset(); form.querySelector('input[name="id"]').value = task?.id || '';
  if (task) { form.title.value = task.title; form.description.value = task.description; form.priority.value = task.priority; form.status.value = task.status; form.deadline.value = task.deadline ? task.deadline.replace(' ','T') : ''; form.assigned_to.value = task.assigned_to || ''; }
  showModal('#taskModal');
}

async function uploadAttachment(taskId, input){ const f = input.files[0]; if (!f) return; const fd = new FormData(); fd.append('file', f); const res = await fetch(apiBase + '/tasks.php?action=upload&id=' + taskId, {method:'POST', body:fd}); return res.json(); }

// Tasks: list, view
async function loadTasks(){
  const q = document.getElementById('searchInput')?.value || '';
  const status = document.getElementById('filterStatus')?.value || '';
  const pr = document.getElementById('filterPriority')?.value || '';
  const url = new URL(apiBase + '/tasks.php?action=list', location.origin);
  if (q) url.searchParams.set('q', q);
  if (status) url.searchParams.set('status', status);
  if (pr) url.searchParams.set('priority', pr);
  const res = await fetch(url);
  const data = await res.json();
  const el = document.getElementById('taskList'); el.innerHTML = '';
  (data.tasks || []).forEach(t=>{
    const d = document.createElement('div'); d.className = 'card p-2 mb-2 task-card';
    d.innerHTML = `<div class="d-flex"><div><strong>${escapeHtml(t.title)}</strong><div class="text-muted small">${t.priority} • ${t.status}</div></div><div class="ms-auto text-end"><small>Assigned: ${t.assigned_name||'—'}</small><div class="small text-muted">Deadline: ${t.deadline||'—'}</div></div></div>`;
    d.addEventListener('click', ()=> viewTask(t.id));
    el.appendChild(d);
  });
}

async function viewTask(id){ const res = await fetch(apiBase + '/tasks.php?action=view&id=' + id); const j = await res.json(); if (!j.task) return; openTaskModal(j.task); // load comments
  const comments = j.comments || []; const ctn = document.getElementById('taskComments'); ctn.innerHTML = '<h6>Comments</h6>' + '<div id="commentsList"></div>' + `<div class="mt-2"><textarea id="newComment" class="form-control mb-2" placeholder="Add comment"></textarea><button id="postComment" class="btn btn-sm btn-primary">Post</button></div>`;
  const list = ctn.querySelector('#commentsList'); comments.forEach(c=>{ const it = document.createElement('div'); it.className='mb-2 small'; it.innerHTML = `<strong>${escapeHtml(c.name)}</strong> <span class="text-muted small">${c.created_at}</span><div>${escapeHtml(c.body)}</div>`; list.appendChild(it); });
  ctn.querySelector('#postComment').addEventListener('click', async ()=>{ const body = ctn.querySelector('#newComment').value; if (!body) return; const r = await fetch(apiBase + '/tasks.php?action=comment',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({task_id:id,body})}); const jr = await r.json(); if (jr.success) { viewTask(id); } });
}

async function loadDashboard(){ const res = await fetch(apiBase + '/tasks.php?action=list'); const j = await res.json(); const tasks = j.tasks || []; const total = tasks.length; const completed = tasks.filter(t=>t.status==='Completed').length; const pending = tasks.filter(t=>t.status!=='Completed').length; document.getElementById('statsRow').innerHTML = `<div class="p-2 bg-light rounded">Total: <strong>${total}</strong></div><div class="p-2 bg-light rounded">Completed: <strong>${completed}</strong></div><div class="p-2 bg-light rounded">Pending: <strong>${pending}</strong></div>`; const late = tasks.filter(t=>t.deadline && new Date(t.deadline) < new Date() && t.status !== 'Completed'); const lateEl = document.getElementById('lateTasks'); lateEl.innerHTML = late.map(t=>`<div class="small">${escapeHtml(t.title)} — ${t.deadline}</div>`).join('');
  // chart update
  const ctx = document.getElementById('chart'); if (ctx) { const c = ctx.getContext('2d'); if (window.overviewChart) window.overviewChart.destroy(); window.overviewChart = new Chart(c,{type:'doughnut',data:{labels:['Completed','Pending'],datasets:[{data:[completed,pending],backgroundColor:['#4caf50','#f44336']}]}}); }
}

// Daily todos: load and render
async function loadDaily(){
  if (!currentUser) return;
  const res = await fetch(apiBase + '/daily.php'); const j = await res.json(); const ul = document.getElementById('dailyList'); ul.innerHTML = '';
  (j.todos || []).forEach(t=>{
    const li = document.createElement('li'); li.className = 'list-group-item d-flex justify-content-between align-items-center';
    li.innerHTML = `<div><input type="checkbox" data-id="${t.id}" ${t.is_done? 'checked':''} /> <span class="ms-2">${escapeHtml(t.title)}</span></div><div><button data-id="${t.id}" class="btn btn-sm btn-danger btn-del">Delete</button></div>`;
    li.querySelector('input[type="checkbox"]').addEventListener('change', async (e)=>{ await fetch(apiBase + '/daily.php', {method:'PATCH', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body:`id=${t.id}&is_done=${e.target.checked?1:0}`}); loadDaily(); });
    li.querySelector('.btn-del').addEventListener('click', async ()=>{ await fetch(apiBase + '/daily.php', {method:'DELETE', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body:`id=${t.id}`}); loadDaily(); });
    ul.appendChild(li);
  });
}

// Admin: create user modal
document.addEventListener('click', (e)=>{
  if (e.target && e.target.id === 'btnCreateUser') {
    showModal('#createUserModal'); loadTeamsIntoSelect();
  }
});

document.getElementById('saveUserBtn')?.addEventListener('click', async ()=>{
  const form = document.getElementById('createUserForm'); const data = Object.fromEntries(new FormData(form).entries()); if (!data.email || !data.password) return alert('Email and password required');
  try {
    const res = await fetch(apiBase + '/users.php', {method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify(data)});
    const j = await res.json();
    if (j.success) {
      hideModal('#createUserModal'); loadUsersIfAdmin();
    } else {
      alert(j.error || j.message || 'Failed to create user');
      console.error('Create user failed:', j);
    }
  } catch (err) {
    alert('Failed to create user (network error)');
    console.error(err);
  }
});

async function loadTeamsIntoSelect(){ const res = await fetch(apiBase + '/teams.php'); const j = await res.json(); const sel = document.getElementById('teamSelect'); sel.innerHTML = '<option value="">No team</option>' + (j.teams||[]).map(t=>`<option value="${t.id}">${escapeHtml(t.name)}</option>`).join(''); }

// Theme persistence
if (localStorage.getItem('meister_theme') === 'dark') document.body.classList.add('dark');
document.getElementById('toggleTheme').addEventListener('click', ()=>{ const isDark = document.body.classList.toggle('dark'); localStorage.setItem('meister_theme', isDark? 'dark' : 'light'); document.getElementById('toggleTheme').textContent = isDark? 'Light' : 'Dark'; });

async function loadUsersIfAdmin(){ if (!currentUser || currentUser.role_id != 1) return; const res = await fetch(apiBase + '/users.php'); const j = await res.json(); const el = document.getElementById('usersList'); el.innerHTML = '<table class="table table-sm"><thead><tr><th>Name</th><th>Email</th><th>Role</th></tr></thead><tbody>' + (j.users||[]).map(u=>`<tr><td>${escapeHtml(u.name)}</td><td>${escapeHtml(u.email)}</td><td>${escapeHtml(u.role)}</td></tr>`).join('') + '</tbody></table>'; }

function showView(name){ document.querySelectorAll('.view').forEach(v=>v.classList.add('d-none')); const el = document.getElementById('view-' + name); if (el) el.classList.remove('d-none'); if (name === 'tasks') loadTasks(); if (name === 'dashboard') loadDashboard(); if (name === 'calendar') renderCalendar(); }

let calendarInstance = null;
async function renderCalendar(){ if (calendarInstance) return; const calendarEl = document.getElementById('calendar'); calendarInstance = new FullCalendar.Calendar(calendarEl,{ initialView:'dayGridMonth', editable:true, height:600, events: async function(fetchInfo, success){ const res = await fetch(apiBase + '/tasks.php?action=list'); const json = await res.json(); const events = (json.tasks||[]).map(t=>({id:t.id,title:t.title,start:t.deadline,backgroundColor: t.priority==='High'? '#ff4d4f' : t.priority==='Low' ? '#ffd666' : '#69c0ff'})); success(events); }, eventDrop: async function(info){ await fetch(apiBase + '/tasks.php?action=update',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({id:info.event.id,deadline:info.event.start.toISOString()})}); } }); calendarInstance.render(); }

function escapeHtml(s){ return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }
