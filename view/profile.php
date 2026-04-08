<?php
require_once __DIR__ . '/header.php';
renderHeader('profile');
?>

<div class="profile-page">
  <div class="profile-container" id="profile-container">

    <div class="profile-header-card" id="profile-header-card">
      <img class="profile-avatar-large" id="profile-avatar-large" src="" alt="Avatar" />
      <div class="profile-header-info">
        <div class="profile-header-name" id="profile-header-name">—</div>
        <div class="profile-header-meta">
          <span id="profile-header-role">—</span>
        </div>
        <div class="profile-header-since" id="profile-header-since"></div>
      </div>
      <a href="/view/settings.php" class="btn btn-ghost profile-settings-btn">⚙ Settings</a>
    </div>

    <div id="profile-stats-loading" style="display:flex;justify-content:center;padding:40px;">
      <div class="spinner"></div>
    </div>

    <div id="profile-stats-area" style="display:none; display:flex; flex-direction:column; gap:24px;"></div>

  </div>

  <div id="profile-guest-msg" style="display:none; text-align:center; padding:80px 24px; color:var(--text-muted);">
    <p style="font-size:18px; margin-bottom:16px;">You need to be logged in to view your profile.</p>
    <a href="/view/login.php" class="btn btn-yellow btn-pixel">Login</a>
  </div>
</div>

<?php
require_once __DIR__ . '/footer.php';
renderFooter([]);
?>

<script>
Auth.init().then(async user => {
  if (!user) {
    document.getElementById('profile-container').style.display = 'none';
    document.getElementById('profile-guest-msg').style.display = '';
    return;
  }

  document.getElementById('profile-avatar-large').src = Auth.getAvatarUrl(user);
  document.getElementById('profile-header-name').textContent = user.username;
  document.getElementById('profile-header-role').textContent =
    user.role === 'admin' ? 'Administrator' : 'Trainer';

  let data;
  try {
    const res = await fetch('/api/user_stats.php?user_id=' + user.id);
    data = await res.json();
  } catch (e) {
    data = { success: false };
  }

  document.getElementById('profile-stats-loading').style.display = 'none';

  if (!data.success) {
    document.getElementById('profile-stats-area').innerHTML =
      '<p style="text-align:center;color:var(--text-muted)">Could not load stats.</p>';
    document.getElementById('profile-stats-area').style.display = 'flex';
    return;
  }

  const { user: fullUser, stats, history } = data;

  if (fullUser.created_at) {
    const since = new Date(fullUser.created_at).toLocaleDateString('en-US', { month: 'long', year: 'numeric' });
    document.getElementById('profile-header-since').textContent = 'Member since ' + since;
  }

  const noGames = !stats || !stats.games_played;
  const area = document.getElementById('profile-stats-area');
  area.style.display = 'flex';
  area.style.flexDirection = 'column';
  area.style.gap = '24px';

  if (noGames) {
    area.innerHTML = `
      <div class="settings-card">
        <div class="settings-card-title">STATS</div>
        <div class="settings-section" style="text-align:center;color:var(--text-muted);">
          <p>You haven't played any games yet.</p>
          <a href="/view/game.php" class="btn btn-yellow btn-pixel" style="margin-top:16px;">▶ PLAY NOW</a>
        </div>
      </div>`;
    return;
  }

  const fmt = n => (n == null ? '—' : Number(n).toLocaleString());
  function diffCard(d, label) {
    const info = stats.by_difficulty?.[d];
    if (!info) return `
      <div class="profile-diff-card profile-diff-${d}">
        <div class="profile-diff-name">${label}</div>
        <div class="profile-diff-best" style="color:var(--text-dim)">—</div>
        <div class="profile-diff-sub">No games</div>
      </div>`;
    return `
      <div class="profile-diff-card profile-diff-${d}">
        <div class="profile-diff-name">${label}</div>
        <div class="profile-diff-best">${fmt(info.best)}</div>
        <div class="profile-diff-sub">${info.games} game${info.games > 1 ? 's' : ''}</div>
      </div>`;
  }

  const historyRows = history.map(h => {
    const date = new Date(h.created_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
    const acc  = h.total_guesses > 0 ? Math.round((h.correct_guesses / h.total_guesses) * 100) : 0;
    return `<tr>
      <td><span class="diff-pill diff-pill-${h.difficulty}">${h.difficulty.charAt(0).toUpperCase() + h.difficulty.slice(1)}</span></td>
      <td><span class="score-value" style="font-size:13px;">${fmt(h.score)}</span></td>
      <td style="color:var(--text-muted)">${h.correct_guesses}/${h.total_guesses}</td>
      <td style="color:var(--blue);font-weight:700;">${acc}%</td>
      <td style="color:var(--text-dim);font-size:13px;">${date}</td>
    </tr>`;
  }).join('');

  area.innerHTML = `

    <div class="settings-card">
      <div class="settings-card-title">OVERVIEW</div>
      <div class="settings-section">
        <div class="profile-stats-grid">
          <div class="result-card">
            <div class="result-val accuracy">${stats.games_played ?? '—'}</div>
            <div class="result-label">GAMES PLAYED</div>
          </div>
          <div class="result-card">
            <div class="result-val correct">${fmt(stats.best_score)}</div>
            <div class="result-label">BEST SCORE</div>
          </div>
          <div class="result-card">
            <div class="result-val total">${stats.accuracy ?? '—'}%</div>
            <div class="result-label">ACCURACY</div>
          </div>
          <div class="result-card">
            <div class="result-val streak">${fmt(stats.max_streak)}</div>
            <div class="result-label">MAX STREAK</div>
          </div>
          <div class="result-card">
            <div class="result-val total">${stats.total_correct ?? '—'}</div>
            <div class="result-label">TOTAL CORRECT</div>
          </div>
          <div class="result-card">
            <div class="result-val accuracy">${stats.total_attempts ?? '—'}</div>
            <div class="result-label">TOTAL GUESSES</div>
          </div>
        </div>
      </div>
    </div>

    <div class="settings-card">
      <div class="settings-card-title">BY DIFFICULTY</div>
      <div class="settings-section">
        <div class="profile-diff-grid">
          ${diffCard('easy',   'Easy')}
          ${diffCard('normal', 'Normal')}
          ${diffCard('hard',   'Hard')}
        </div>
      </div>
    </div>

    ${history.length > 0 ? `
    <div class="settings-card">
      <div class="settings-card-title">RECENT GAMES</div>
      <div style="overflow-x:auto;">
        <table class="lb-history-table" style="width:100%;">
          <thead>
            <tr><th>DIFF</th><th>SCORE</th><th>CORRECT</th><th>ACCURACY</th><th>DATE</th></tr>
          </thead>
          <tbody>${historyRows}</tbody>
        </table>
      </div>
    </div>` : ''}

  `;
});
</script>
