<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Meister ToDo — Task Management</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/css/style.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/main.min.css" rel="stylesheet">
</head>
<body>
  <div id="app" class="app-shell">
    <nav class="navbar navbar-expand-lg navbar-light bg-light shadow-sm">
      <div class="container-fluid">
        <a class="navbar-brand" href="#">Meister ToDo</a>
        <div class="d-flex align-items-center">
          <div id="currentUser" class="me-3 small text-muted">Not signed in</div>
          <button id="toggleTheme" class="btn btn-sm btn-outline-secondary me-2">Dark</button>
          <button id="btnLogin" class="btn btn-sm btn-primary">Sign In</button>
        </div>
      </div>
    </nav>

    <div class="d-flex">
      <aside class="sidebar bg-white border-end">
        <ul class="nav flex-column p-2">
          <li class="nav-item"><a class="nav-link active" href="#" data-view="dashboard">Dashboard</a></li>
          <li class="nav-item"><a class="nav-link" href="#" data-view="tasks">Tasks</a></li>
          <li class="nav-item"><a class="nav-link" href="#" data-view="calendar">Calendar</a></li>
          <li class="nav-item"><a class="nav-link" href="#" data-view="daily">Daily To-Do</a></li>
          <li class="nav-item"><a class="nav-link" href="#" data-view="admin">Admin</a></li>
          <li class="nav-item mt-2"><a class="nav-link text-muted" href="#" data-view="settings">Settings</a></li>
        </ul>
      </aside>

      <main class="flex-grow-1 p-3" id="mainView">
        <!-- Dashboard -->
        <section id="view-dashboard" class="view">
          <div class="row">
            <div class="col-md-8">
              <div class="card mb-3 p-3">
                <h5>Overview</h5>
                <div id="statsRow" class="d-flex gap-3"></div>
              </div>

              <div class="card p-3 mb-3">
                <h6>Late Tasks</h6>
                <div id="lateTasks"></div>
              </div>
            </div>

            <div class="col-md-4">
              <div class="card p-3 mb-3">
                <h6>Productivity</h6>
                <canvas id="chart" height="220"></canvas>
              </div>
              <div class="card p-3">
                <h6>Quick Create</h6>
                <form id="quickCreate" class="d-flex gap-2">
                  <input class="form-control" name="title" placeholder="Task title" required>
                  <select class="form-select" name="priority"><option>Medium</option><option>High</option><option>Low</option></select>
                  <button class="btn btn-primary">Create</button>
                </form>
              </div>
            </div>
          </div>
        </section>

        <!-- Tasks -->
        <section id="view-tasks" class="view d-none">
          <div class="d-flex mb-2 gap-2 align-items-center">
            <input id="searchInput" class="form-control form-control-sm" placeholder="Search tasks...">
            <select id="filterStatus" class="form-select form-select-sm w-auto"><option value="">All Status</option><option>Pending</option><option>In Progress</option><option>Review</option><option>Completed</option><option>Blocked</option></select>
            <select id="filterPriority" class="form-select form-select-sm w-auto"><option value="">All Priority</option><option>High</option><option>Medium</option><option>Low</option></select>
            <button id="btnNewTask" class="btn btn-sm btn-success ms-auto">New Task</button>
          </div>
          <div id="taskList"></div>
        </section>

        <!-- Calendar -->
        <section id="view-calendar" class="view d-none">
          <div id="calendar" class="card p-3"></div>
        </section>

        <!-- Daily -->
        <section id="view-daily" class="view d-none">
          <div class="card p-3">
            <h5>My Daily To-Do</h5>
            <form id="dailyForm" class="d-flex gap-2 mb-3"><input name="title" class="form-control" placeholder="Add today's task"><button class="btn btn-primary">Add</button></form>
            <ul id="dailyList" class="list-group"></ul>
          </div>
        </section>

        <!-- Admin -->
        <section id="view-admin" class="view d-none">
          <div class="card p-3">
            <h5>Users</h5>
            <div class="mb-2"><button id="btnCreateUser" class="btn btn-sm btn-outline-primary">Create User</button></div>
            <div id="usersList"></div>
            <!-- Create User Modal -->
            <div class="modal" id="createUserModal" tabindex="-1">
              <div class="modal-dialog">
                <div class="modal-content">
                  <div class="modal-header"><h5 class="modal-title">Create User</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
                  <div class="modal-body">
                    <form id="createUserForm">
                      <input name="name" class="form-control mb-2" placeholder="Full name" required>
                      <input name="email" class="form-control mb-2" placeholder="Email" required>
                      <input name="password" class="form-control mb-2" placeholder="Password" required>
                      <select name="role_id" class="form-select mb-2"><option value="3">Member</option><option value="2">Team Leader</option><option value="1">Admin</option></select>
                      <select name="team_id" id="teamSelect" class="form-select mb-2"><option value="">No team</option></select>
                    </form>
                  </div>
                  <div class="modal-footer"><button id="saveUserBtn" class="btn btn-primary">Save</button><button class="btn btn-secondary" data-bs-dismiss="modal">Close</button></div>
                </div>
              </div>
            </div>
          </div>
        </section>

        <!-- Settings -->
        <section id="view-settings" class="view d-none">
          <div class="card p-3">
            <h5>Settings</h5>
            <div class="mb-2">Theme: <button id="themePrimary" class="btn btn-sm btn-outline-secondary">Toggle</button></div>
          </div>
        </section>

      </main>
    </div>

    <!-- Task Modal -->
    <div class="modal" tabindex="-1" id="taskModal">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header"><h5 class="modal-title">Task</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
          <div class="modal-body">
            <form id="taskForm">
              <input type="hidden" name="id">
              <div class="mb-2"><input class="form-control" name="title" placeholder="Title" required></div>
              <div class="mb-2"><textarea class="form-control" name="description" placeholder="Description"></textarea></div>
              <div class="d-flex gap-2 mb-2">
                <select name="priority" class="form-select w-auto"><option>Medium</option><option>High</option><option>Low</option></select>
                <select name="status" class="form-select w-auto"><option>Pending</option><option>In Progress</option><option>Review</option><option>Completed</option><option>Blocked</option></select>
                <input type="datetime-local" name="deadline" class="form-control w-auto">
                <input name="assigned_to" class="form-control" placeholder="Assign to (user id)">
              </div>
              <div class="mb-2"><label class="form-label">Subtasks (one per line)</label><textarea name="subtasks" class="form-control" placeholder="Subtask1\nSubtask2"></textarea></div>
              <div class="mb-2"><label class="form-label">Attachments</label><input type="file" name="attachment" class="form-control" /></div>
            </form>
            <div id="taskComments" class="mt-3"></div>
          </div>
          <div class="modal-footer"><button id="saveTask" class="btn btn-primary">Save</button><button class="btn btn-secondary" data-bs-dismiss="modal">Close</button></div>
        </div>
      </div>
    </div>

    <!-- Login Modal -->
    <div class="modal" id="loginModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><h5 class="modal-title">Sign In</h5><button class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><form id="loginForm"><input class="form-control mb-2" name="email" placeholder="Email" required><input class="form-control mb-2" name="password" placeholder="Password" type="password" required><div class="d-flex gap-2"><button class="btn btn-primary">Sign In</button><button id="btnRegister" class="btn btn-outline-secondary" type="button">Register</button></div></form></div></div></div></div>

  </div>

  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.3.0/dist/chart.umd.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/main.global.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="assets/js/app.js"></script>
</body>
</html>
