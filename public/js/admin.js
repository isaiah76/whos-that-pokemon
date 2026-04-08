const Admin = (() => {
  let allUsers = [];

  async function init() {
    const result = await API.getUsers();

    if (!result.success) {
      showToast(result.message || 'Failed to load users.', 'error');
      return;
    }

    allUsers = result.users || [];
    renderStats(allUsers);
    renderTable(allUsers);
    bindSearch();
  }

  function renderStats(users) {
    const total = users.length;
    const active = users.filter(u => u.status === 'active').length;
    const disabled = users.filter(u => u.status === 'disabled').length;
    const admins = users.filter(u => u.role === 'admin').length;

    setText('stat-total',    total);
    setText('stat-active',   active);
    setText('stat-disabled', disabled);
    setText('stat-admins',   admins);
  }

  function renderTable(users) {
    const tbody  = document.getElementById('users-tbody');
    const emptyEl = document.getElementById('users-empty');
    if (!tbody) return;

    tbody.innerHTML = '';

    if (!users.length) {
      if (emptyEl) emptyEl.style.display = 'block';
      return;
    }

    if (emptyEl) emptyEl.style.display = 'none';

    const frag = document.createDocumentFragment();

    users.forEach(user => {
      const tr = document.createElement('tr');
      tr.dataset.userId = user.id;

      const joinDate = new Date(user.created_at).toLocaleDateString('en-US', {
        year: 'numeric', month: 'short', day: 'numeric',
      });

      const isAdmin       = user.role === 'admin';
      const isActive      = user.status === 'active';
      const canToggle     = !isAdmin;

      tr.innerHTML = `
        <td><strong>${escapeHtml(user.username)}</strong></td>
        <td>${escapeHtml(user.email)}</td>
        <td><span class="role-badge ${user.role}">${user.role}</span></td>
        <td>${joinDate}</td>
        <td>
          <span class="status-badge ${user.status}" id="status-badge-${user.id}">
            ${user.status}
          </span>
        </td>
        <td>
          ${canToggle
            ? `<button
                class="btn btn-sm ${isActive ? 'btn-red' : 'btn-ghost'}"
                id="toggle-btn-${user.id}"
                onclick="Admin.toggleStatus(${user.id}, '${isActive ? 'disabled' : 'active'}')"
               >
                 ${isActive ? 'Disable' : 'Enable'}
               </button>`
            : `<span class="text-dim" style="color:var(--text-dim);font-size:12px;">Protected</span>`
          }
        </td>
      `;

      frag.appendChild(tr);
    });

    tbody.appendChild(frag);
  }

  async function toggleStatus(userId, newStatus) {
    const btn    = document.getElementById(`toggle-btn-${userId}`);
    const badge  = document.getElementById(`status-badge-${userId}`);

    if (btn) { btn.disabled = true; btn.textContent = '...'; }

    const result = await API.setUserStatus(userId, newStatus);

    if (result.success) {
      const user = allUsers.find(u => u.id == userId);
      if (user) user.status = newStatus;

      if (badge) {
        badge.className  = `status-badge ${newStatus}`;
        badge.textContent = newStatus;
      }

      if (btn) {
        btn.disabled     = false;
        const nowActive  = newStatus === 'active';
        btn.textContent  = nowActive ? 'Disable' : 'Enable';
        btn.className    = `btn btn-sm ${nowActive ? 'btn-red' : 'btn-ghost'}`;
        btn.onclick      = () => toggleStatus(userId, nowActive ? 'disabled' : 'active');
      }

      renderStats(allUsers);
      showToast(result.message, 'success');
    } else {
      if (btn) { btn.disabled = false; btn.textContent = newStatus === 'active' ? 'Enable' : 'Disable'; }
      showToast(result.message || 'Action failed.', 'error');
    }
  }

  function bindSearch() {
    const input = document.getElementById('user-search');
    if (!input) return;

    input.addEventListener('input', () => {
      const query   = input.value.trim().toLowerCase();
      const filtered = allUsers.filter(u =>
        u.username.toLowerCase().includes(query) ||
        u.email.toLowerCase().includes(query)
      );
      renderTable(filtered);
    });
  }

  function setText(id, val) {
    const el = document.getElementById(id);
    if (el) el.textContent = val;
  }

  function escapeHtml(str) {
    const div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
  }

  return { init, toggleStatus };
})();
