<?php
require_once __DIR__ . '/header.php';
renderHeader('settings');
?>

<div class="profile-page">
  <div class="profile-container" id="settings-container">

    <div class="profile-header-card">
      <img class="profile-avatar-large" id="settings-avatar-preview" src="" alt="Avatar" />
      <div class="profile-header-info">
        <div class="profile-header-name" id="settings-username">—</div>
        <div class="profile-header-meta" style="color:var(--text-muted);">Manage your account settings</div>
      </div>
    </div>

    <div class="settings-card">
      <div class="settings-card-title">PROFILE PICTURE</div>
      <div class="settings-section">
        <div class="settings-row">
          <label class="form-label">Choose your avatar</label>
          <div class="avatar-grid" id="avatar-grid"></div>
        </div>
        <button class="btn btn-yellow settings-save-btn" id="save-avatar-btn">Save Avatar</button>
        <div class="form-error" id="avatar-error"></div>
      </div>
    </div>

    <div class="settings-card">
      <div class="settings-card-title">ACCOUNT</div>
      <div class="settings-section">
        <div class="settings-row">
          <label class="form-label" for="new-username">Username</label>
          <input type="text" id="new-username" class="form-input" placeholder="New username" autocomplete="off" />
          <div class="form-error" id="username-error"></div>
        </div>
        <button class="btn btn-yellow settings-save-btn" id="save-username-btn">Update Username</button>
      </div>
    </div>

    <div class="settings-card">
      <div class="settings-card-title">EMAIL</div>
      <div class="settings-section">
        <div class="settings-row">
          <label class="form-label" for="new-email">Email Address</label>
          <input type="email" id="new-email" class="form-input" placeholder="New email address" autocomplete="email" />
          <div class="form-error" id="email-error"></div>
        </div>
        <button class="btn btn-yellow settings-save-btn" id="save-email-btn">Update Email</button>
      </div>
    </div>

    <div class="settings-card">
      <div class="settings-card-title">CHANGE PASSWORD</div>
      <div class="settings-section">
        <div class="settings-row">
          <label class="form-label" for="current-password">Current Password</label>
          <input type="password" id="current-password" class="form-input" placeholder="Current password" autocomplete="current-password" />
        </div>
        <div class="settings-row">
          <label class="form-label" for="new-password">New Password</label>
          <input type="password" id="new-password" class="form-input" placeholder="New password (min 8 chars)" autocomplete="new-password" />
        </div>
        <div class="settings-row">
          <label class="form-label" for="confirm-password">Confirm New Password</label>
          <input type="password" id="confirm-password" class="form-input" placeholder="Confirm new password" autocomplete="new-password" />
        </div>
        <div class="form-error" id="password-error"></div>
        <button class="btn btn-yellow settings-save-btn" id="save-password-btn">Update Password</button>
      </div>
    </div>

  </div>

  <div id="settings-guest-msg" style="display:none; text-align:center; padding:80px 24px; color:var(--text-muted);">
    <p style="font-size:18px; margin-bottom:16px;">You need to be logged in to access settings.</p>
    <a href="/view/login.php" class="btn btn-yellow btn-pixel">Login</a>
  </div>
</div>

<?php
require_once __DIR__ . '/footer.php';
renderFooter([]);
?>

<script>
const AVATARS = [
  'eevee.jpg','bulbasaur.jpg','cubone.jpg','meowth.jpg','munchlax.jpg',
  'pikachu.jpg','piplup.jpg','snivy.jpg','togepi.jpg'
];

let currentUser = null;
let selectedAvatar = null;

Auth.init().then(user => {
  if (!user) {
    document.getElementById('settings-container').style.display = 'none';
    document.getElementById('settings-guest-msg').style.display = '';
    return;
  }

  currentUser = user;
  document.getElementById('settings-username').textContent = user.username;
  document.getElementById('new-username').value = user.username;
  document.getElementById('new-email').value = user.email || '';
  document.getElementById('settings-avatar-preview').src = Auth.getAvatarUrl(user);

  selectedAvatar = user.avatar || AVATARS[(user.id || 0) % AVATARS.length];

  const grid = document.getElementById('avatar-grid');
  AVATARS.forEach(fname => {
    const div = document.createElement('div');
    div.className = 'avatar-option' + (selectedAvatar === fname ? ' selected' : '');
    div.dataset.avatar = fname;
    div.innerHTML = `<img src="/assets/${fname}" alt="${fname.replace('.jpg','')}" />`;
    div.addEventListener('click', () => {
      document.querySelectorAll('.avatar-option').forEach(o => o.classList.remove('selected'));
      div.classList.add('selected');
      selectedAvatar = fname;
      document.getElementById('settings-avatar-preview').src = `/assets/${fname}`;
    });
    grid.appendChild(div);
  });
});

document.getElementById('save-avatar-btn').addEventListener('click', async () => {
  const errEl = document.getElementById('avatar-error');
  errEl.classList.remove('visible');
  const btn = document.getElementById('save-avatar-btn');
  btn.disabled = true; btn.textContent = 'Saving...';

  const res = await fetch('/api/update_profile.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ action: 'avatar', avatar: selectedAvatar }),
  });
  const data = await res.json();
  btn.disabled = false; btn.textContent = 'Save Avatar';

  if (data.success) {
    showToast('Avatar updated!', 'success');
    if (window._authUser) { window._authUser.avatar = selectedAvatar; Auth.updateNav(); }
    document.getElementById('nav-avatar-img') && (document.getElementById('nav-avatar-img').src = `/assets/${selectedAvatar}`);
  } else {
    errEl.textContent = data.message || 'Failed to save.';
    errEl.classList.add('visible');
  }
});

document.getElementById('save-username-btn').addEventListener('click', async () => {
  const errEl = document.getElementById('username-error');
  errEl.classList.remove('visible');
  const newUsername = document.getElementById('new-username').value.trim();
  if (!newUsername) { errEl.textContent = 'Username cannot be empty.'; errEl.classList.add('visible'); return; }

  const btn = document.getElementById('save-username-btn');
  btn.disabled = true; btn.textContent = 'Saving...';

  const res = await fetch('/api/update_profile.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ action: 'username', username: newUsername }),
  });
  const data = await res.json();
  btn.disabled = false; btn.textContent = 'Update Username';

  if (data.success) {
    showToast('Username updated!', 'success');
    document.getElementById('settings-username').textContent = newUsername;
  } else {
    errEl.textContent = data.message || 'Failed to update.';
    errEl.classList.add('visible');
  }
});

document.getElementById('save-email-btn').addEventListener('click', async () => {
  const errEl = document.getElementById('email-error');
  errEl.classList.remove('visible');
  
  const newEmail = document.getElementById('new-email').value.trim();
  if (!newEmail) { 
    errEl.textContent = 'Email cannot be empty.'; 
    errEl.classList.add('visible'); 
    return; 
  }

  if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(newEmail)) {
    errEl.textContent = 'Please enter a valid email address.'; 
    errEl.classList.add('visible'); 
    return;
  }

  const btn = document.getElementById('save-email-btn');
  btn.disabled = true; 
  btn.textContent = 'Saving...';

  const res = await fetch('/api/update_profile.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ action: 'email', email: newEmail }),
  });
  const data = await res.json();
  
  btn.disabled = false; 
  btn.textContent = 'Update Email';

  if (data.success) {
    showToast('Email updated!', 'success');
    if (window._authUser) { window._authUser.email = newEmail; }
    currentUser.email = newEmail;
  } else {
    errEl.textContent = data.message || 'Failed to update.';
    errEl.classList.add('visible');
  }
});

document.getElementById('save-password-btn').addEventListener('click', async () => {
  const errEl = document.getElementById('password-error');
  errEl.classList.remove('visible');
  const current = document.getElementById('current-password').value;
  const next    = document.getElementById('new-password').value;
  const confirm = document.getElementById('confirm-password').value;

  if (!current || !next || !confirm) {
    errEl.textContent = 'All password fields are required.'; errEl.classList.add('visible'); return;
  }
  if (next !== confirm) {
    errEl.textContent = 'New passwords do not match.'; errEl.classList.add('visible'); return;
  }
  if (next.length < 8) {
    errEl.textContent = 'Password must be at least 8 characters.'; errEl.classList.add('visible'); return;
  }

  const btn = document.getElementById('save-password-btn');
  btn.disabled = true; btn.textContent = 'Saving...';

  const res = await fetch('/api/update_profile.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ action: 'password', current_password: current, new_password: next }),
  });
  const data = await res.json();
  btn.disabled = false; btn.textContent = 'Update Password';

  if (data.success) {
    showToast('Password updated!', 'success');
    document.getElementById('current-password').value = '';
    document.getElementById('new-password').value = '';
    document.getElementById('confirm-password').value = '';
  } else {
    errEl.textContent = data.message || 'Failed to update.';
    errEl.classList.add('visible');
  }
});
</script>
