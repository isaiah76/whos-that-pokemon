<?php
require_once __DIR__ . '/header.php';
renderHeader("admin");
?>

<main class="page">
  <div class="container">

    <div class="admin-header">
      <h1 class="admin-title">ADMIN DASHBOARD</h1>
      <a href="/view/game.php" class="btn btn-ghost btn-sm">← Back</a>
    </div>

    <div class="admin-stats-row">
      <div class="admin-stat-card">
        <span class="value" id="stat-total">—</span>
        <span class="label">Total Users</span>
      </div>
      <div class="admin-stat-card">
        <span class="value" id="stat-active" style="color:var(--green)">—</span>
        <span class="label">Active</span>
      </div>
      <div class="admin-stat-card">
        <span class="value" id="stat-disabled" style="color:var(--red)">—</span>
        <span class="label">Disabled</span>
      </div>
    </div>

    <div class="admin-filter-bar">
      <input
        type="text"
        id="user-search"
        class="form-input"
        placeholder="Search by username or email…"
      />
    </div>

    <div class="admin-table-wrap">
      <table class="admin-table">
        <thead>
          <tr>
            <th>USERNAME</th>
            <th>EMAIL</th>
            <th>ROLE</th>
            <th>JOINED</th>
            <th>STATUS</th>
            <th>ACTION</th>
          </tr>
        </thead>
        <tbody id="users-tbody">
          <tr>
            <td colspan="6" style="text-align:center; padding:40px;">
              <div class="spinner" style="margin:0 auto;"></div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <p id="users-empty" style="display:none; text-align:center; color:var(--text-muted); padding:24px;">
      No users found.
    </p>

  </div>
</main>

<div id="access-denied" class="modal-overlay" style="display:none;">
  <div class="modal">
    <div class="modal-title" style="color:var(--red)">ACCESS DENIED</div>
    <p class="modal-body">You must be an admin to view this page.</p>
    <div class="modal-btns">
      <a href="/index.php" class="btn btn-yellow">Go Home</a>
    </div>
  </div>
</div>

<?php
require_once __DIR__ . '/footer.php';
renderFooter(['/public/js/admin.js']);
?>

<script>
  Auth.init().then(user => {
    if (!user || user.role !== 'admin') {
      const denied = document.getElementById('access-denied');
      if (denied) {
        denied.style.display = 'flex';
        setTimeout(() => { window.location.href = '/index.php'; }, 2500);
      }
      return;
    }

    Admin.init();
  });
</script>
