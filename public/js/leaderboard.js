const Leaderboard = (() => {

  function avatarImg(filename, cls, alt) {
    const primary  = filename ? `/assets/avatars/${filename}` : '/assets/pokeball.png';
    const fallback = filename ? `/assets/${filename}`         : '/assets/pokeball.png';
    return `<img class="${cls}" src="${primary}" alt="${alt}" loading="lazy"
              onerror="if(this.src!=='${fallback}'){this.onerror=null;this.src='${fallback}'}" />`;
  }

  function genIcons(gens) {
    if (!gens) return '<span style="color:var(--text-dim)">—</span>';
    const nums = gens.split(',').map(Number).filter(n => n >= 1 && n <= 9);
    if (!nums.length) return '<span style="color:var(--text-dim)">—</span>';
    return nums.map(n =>
      `<img class="lb-gen-icon" src="/assets/gen${n}.png" alt="Gen ${n}" title="Gen ${n}"
            onerror="this.style.display='none'" />`
    ).join('');
  }

  function diffLabel(d) {
    return { easy: 'Easy', normal: 'Normal', hard: 'Hard' }[d] || (d || '—');
  }

  function formatDate(iso) {
    if (!iso) return '—';
    return new Date(iso).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
  }

  function formatMonth(iso) {
    if (!iso) return '—';
    return new Date(iso).toLocaleDateString('en-US', { month: 'long', year: 'numeric' });
  }

  function setLoading(on) {
    document.getElementById('leaderboard-loading').style.display         = on ? 'flex' : 'none';
    document.getElementById('leaderboard-table-container').style.display = on ? 'none' : '';
    document.getElementById('leaderboard-empty').style.display            = 'none';
  }

  // ── Render table ───────────────────────────────────────────
  function renderRows(rows) {
    const container = document.getElementById('leaderboard-table-container');
    const empty     = document.getElementById('leaderboard-empty');
    const tbody     = document.getElementById('leaderboard-tbody');

    if (!rows || !rows.length) {
      container.style.display = 'none';
      empty.style.display     = '';
      return;
    }

    tbody.innerHTML = rows.map((row, i) => {
      const rank  = i + 1;
      const badge = rank === 1 ? 'gold' : rank === 2 ? 'silver' : rank === 3 ? 'bronze' : '';
      const diff  = row.fav_difficulty || 'normal';
      const streak = row.max_streak != null ? row.max_streak : '—';

      return `<tr class="lb-clickable" data-user-id="${row.user_id}">
        <td><span class="rank-badge ${badge}">${rank}</span></td>
        <td>
          <div class="lb-trainer-cell">
            ${avatarImg(row.avatar, 'lb-avatar', row.username)}
            <span class="lb-username">${row.username}</span>
          </div>
        </td>
        <td><div class="lb-gen-cell">${genIcons(row.gens || row.top_gens)}</div></td>
        <td><span class="diff-pill diff-pill-${diff}">${diffLabel(diff)}</span></td>
        <td class="lb-streak-val">${streak}</td>
        <td><span class="score-value">${Number(row.highest_score).toLocaleString()}</span></td>
      </tr>`;
    }).join('');

    container.style.display = 'block';

    tbody.querySelectorAll('.lb-clickable').forEach(tr => {
      tr.addEventListener('click', () => openUserProfile(parseInt(tr.dataset.userId, 10)));
    });
  }

  // ── Fetch ──────────────────────────────────────────────────
  async function load(diff) {
    setLoading(true);
    const data = await API.getLeaderboard(50, diff === 'all' ? undefined : diff);
    setLoading(false);
    renderRows(data.scores || data.leaderboard || []);
  }

  // ── Tabs ───────────────────────────────────────────────────
  function bindTabs() {
    document.querySelectorAll('.lb-tab').forEach(btn => {
      btn.addEventListener('click', () => {
        document.querySelectorAll('.lb-tab').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        load(btn.dataset.diff);
      });
    });
  }

  // ── Profile modal ──────────────────────────────────────────
  async function openUserProfile(userId) {
    const overlay = document.getElementById('lb-profile-overlay');
    const modal   = document.getElementById('lb-profile-modal');
    if (!overlay || !modal) return;

    modal.innerHTML = `<div style="text-align:center;padding:60px 40px;"><div class="spinner"></div></div>`;
    overlay.classList.add('open');

    const closeModal = () => overlay.classList.remove('open');

    try {
      const data = await API.getUserStats(userId);
      if (!data.success) throw new Error(data.message || 'Failed');

      const { user, stats, history } = data;
      const noGames = !stats || !stats.games_played;

      // Diff cards — best score only, no sub-text
      const diffCards = ['easy', 'normal', 'hard'].map(d => {
        const info = stats?.by_difficulty?.[d];
        const val  = info ? Number(info.best).toLocaleString() : '—';
        return `<div class="lb-diff-card lb-diff-${d}">
          <div class="lb-diff-name">${diffLabel(d)}</div>
          <div class="lb-diff-best">${val}</div>
        </div>`;
      }).join('');

      // Recent game rows
      const histRows = history.map(h => `
        <tr>
          <td><span class="diff-pill diff-pill-${h.difficulty}">${diffLabel(h.difficulty)}</span></td>
          <td><span class="score-value" style="font-size:12px;">${Number(h.score).toLocaleString()}</span></td>
          <td class="lb-modal-muted">${h.correct_guesses}/${h.total_guesses}</td>
          <td class="lb-modal-dim">${formatDate(h.created_at)}</td>
        </tr>`).join('');

      modal.innerHTML = `
        <button class="lb-modal-close" id="lb-modal-close" aria-label="Close">✕</button>

        <div class="lb-modal-header">
          ${avatarImg(user.avatar, 'lb-modal-avatar', user.username)}
          <div class="lb-modal-info">
            <div class="lb-modal-username">${user.username}</div>
            <div class="lb-modal-role">${user.role === 'admin' ? 'Administrator' : 'Trainer'}</div>
            <div class="lb-modal-since">Member since ${formatMonth(user.created_at)}</div>
          </div>
        </div>

        ${noGames
          ? `<p class="lb-modal-no-games">No games played yet.</p>`
          : `
<div class="lb-modal-stats" style="grid-template-columns: repeat(3, 1fr);">
            <div class="lb-stat-box">
              <div class="lb-stat-val lb-stat-blue">${stats.games_played ?? 0}</div>
              <div class="lb-stat-label">GAMES</div>
            </div>
            <div class="lb-stat-box">
              <div class="lb-stat-val lb-stat-yellow">${Number(stats.best_score ?? 0).toLocaleString()}</div>
              <div class="lb-stat-label">BEST</div>
            </div>
            <div class="lb-stat-box">
              <div class="lb-stat-val lb-stat-green">${stats.accuracy ?? 0}%</div>
              <div class="lb-stat-label">ACCURACY</div>
            </div>
          </div>

          <div class="lb-modal-section-title">BEST BY DIFFICULTY</div>
          <div class="lb-diff-row">${diffCards}</div>

          ${history.length > 0 ? `
            <div class="lb-modal-section-title">RECENT GAMES</div>
            <table class="lb-history-table">
              <thead>
                <tr><th>MODE</th><th>SCORE</th><th>CORRECT</th><th>DATE</th></tr>
              </thead>
              <tbody>${histRows}</tbody>
            </table>` : ''}
        `}
      `;

      document.getElementById('lb-modal-close')?.addEventListener('click', closeModal);

    } catch {
      modal.innerHTML = `
        <button class="lb-modal-close" id="lb-modal-close">✕</button>
        <p class="lb-modal-error">Could not load profile. Please try again.</p>`;
      document.getElementById('lb-modal-close')?.addEventListener('click', closeModal);
    }
  }

  // ── Init ───────────────────────────────────────────────────
  async function init() {
    bindTabs();
    await load('all');
  }

  return { init };
})();
