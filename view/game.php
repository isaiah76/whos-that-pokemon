<?php
require_once __DIR__ . '/header.php';
renderHeader("game");
?>

<style>
 html, body {
    -ms-overflow-style: none;
    scrollbar-width: none;
  }
  html::-webkit-scrollbar, body::-webkit-scrollbar {
    display: none;
  }

  .game-col-left, .game-col-right {
    gap: 0 !important;
  }
  .panel-top { border-bottom-left-radius: 0; border-bottom-right-radius: 0; }
  .panel-mid { border-radius: 0; margin-top: -1px; position: relative; z-index: 1; }
  .panel-bot { border-top-left-radius: 0; border-top-right-radius: 0; margin-top: -1px; position: relative; z-index: 1; }

  #pokemon-img { pointer-events: none; user-select: none; -webkit-user-drag: none; }

  #guess-input.shake + #letter-progress {
    animation: shakeX 0.35s ease;
  }
</style>

<div class="lobby-overlay open" id="lobby-overlay">
  <div class="lobby-modal">
    <div class="lobby-title">WHO'S THAT POKÉMON?</div>

    <div class="lobby-section">
      <div class="lobby-section-label">GENERATIONS <span style="font-size:11px;color:var(--text-dim);font-family:var(--body-font);letter-spacing:0;">(click to toggle)</span></div>
      <div class="gen-grid" id="lobby-gen-grid"></div>
    </div>

    <div class="lobby-section">
      <div class="lobby-section-label">DIFFICULTY</div>
      <div class="lobby-diff-grid">
        <button class="lobby-diff-btn easy" data-diff="easy">
          <span class="diff-name">Easy</span>
          <span class="diff-desc">5 lives · 3 hints</span>
          <span class="diff-desc">+10 pts</span>
          <span class="diff-timer">60s</span>
        </button>
        <button class="lobby-diff-btn normal active" data-diff="normal">
          <span class="diff-name">Normal</span>
          <span class="diff-desc">3 lives · 3 hints</span>
          <span class="diff-desc">+20 pts</span>
          <span class="diff-timer">30s</span>
        </button>
        <button class="lobby-diff-btn hard" data-diff="hard">
          <span class="diff-name">Hard</span>
          <span class="diff-desc">1 life · No hints</span>
          <span class="diff-desc">+40 pts</span>
          <span class="diff-timer">15s</span>
        </button>
      </div>
    </div>

    <button class="btn btn-yellow btn-pixel lobby-start-btn" id="lobby-start-btn">START GAME</button>
  </div>
</div>

<div class="game-page">

  <div class="mobile-stats-bar" style="display:none;">
    <div class="mobile-stat">
      <span class="mobile-stat-label">SCORE</span>
      <span class="mobile-stat-value" id="m-score-value">0</span>
    </div>
    <div class="mobile-stat">
      <span class="mobile-stat-label">STREAK</span>
      <span class="mobile-stat-value streak" id="m-streak-value">0</span>
    </div>
    <div class="mobile-stat">
      <span class="mobile-stat-label">LIVES</span>
      <div class="lives-display" id="m-lives-display" style="justify-content:center;font-size:14px;gap:3px;"></div>
    </div>
    <div class="mobile-stat" id="m-timer-block">
      <span class="mobile-stat-label">TIME</span>
      <span class="mobile-stat-value timer" id="m-timer-value">—</span>
    </div>
  </div>

  <div class="mobile-action-bar" style="display:none;">
    <button class="segment" id="m-hint-btn" disabled title="Hint">
      <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <circle cx="12" cy="12" r="10"/>
        <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/>
        <line x1="12" y1="17" x2="12.01" y2="17"/>
      </svg>
    </button>
    <button class="segment" id="m-skip-btn" disabled title="Skip">
      <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <polygon points="5 4 15 12 5 20 5 4"/>
        <line x1="19" y1="5" x2="19" y2="19"/>
      </svg>
    </button>
    <button class="segment" id="m-restart-btn" title="Settings">
      <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <circle cx="12" cy="12" r="3"/>
        <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/>
      </svg>
    </button>
  </div>

  <div class="game-3col">

    <aside class="game-col-left">
      <div class="game-panel panel-top">
        <div class="panel-title">GENERATIONS</div>
        <div class="gen-chips" id="sidebar-gen-chips"></div>
      </div>

      <div id="auth-notice" class="panel-bot" style="
        font-size:14px; color:var(--text-dim);
        background:var(--bg-card); border:1px solid var(--border);
        padding:16px; line-height:1.6; text-align:center;
      ">
        <a href="/view/login.php">Log in</a> or <a href="/view/signup.php">sign up</a> to save scores!
      </div>
    </aside>

    <main class="game-col-center">

      <div class="pokemon-stage" id="pokemon-stage">
        <div class="loading-overlay" id="loading-overlay"><div class="spinner"></div></div>
        <img id="pokemon-img" src="" alt="" draggable="false" />
      </div>

      <div class="pokemon-name-reveal" id="pokemon-name-reveal"></div>

      <div class="guess-area" id="guess-area" style="margin-top: 32px;">
        <div style="display: flex; align-items: stretch;">
          
          <div class="guess-box-wrap" style="position: relative; flex: 1; background: transparent; border: none; display: flex; align-items: center; justify-content: center; min-height: 76px;">
            <input type="text" id="guess-input"
              autocomplete="off" spellcheck="false" autocapitalize="none" disabled 
              style="position: absolute; inset: 0; opacity: 0; width: 100%; height: 100%; z-index: 10; cursor: text; outline: none; border: none; background: transparent;" />
            <div class="letter-progress" id="letter-progress" style="margin: 0; width: 100%;"></div>
          </div>
          
        </div>
        <div class="autocomplete-dropdown" id="autocomplete-dropdown"></div>
      </div>

      <div id="hint-area" class="hint-area"></div>

    </main>

    <aside class="game-col-right">
      <div class="game-panel panel-top">
        <div class="panel-title">STATS</div>

        <div class="stat-block">
          <div class="stat-label">SCORE</div>
          <span class="stat-value" id="score-value">0</span>
        </div>

        <div class="stat-block streak">
          <div class="stat-label">STREAK</div>
          <span class="stat-value" id="streak-value">0</span>
        </div>

        <div class="stat-block lives">
          <div class="stat-label">LIVES</div>
          <div class="lives-display" id="lives-display" style="margin-top:4px;"></div>
        </div>

        <div class="stat-block timer" id="timer-block">
          <div class="stat-label">TIME</div>
          <span class="stat-value" id="timer-value">—</span>
        </div>
      </div>

      <div class="game-panel panel-mid">
        <div class="panel-title">DIFFICULTY</div>
        <div id="sidebar-diff-display"></div>
      </div>

      <div class="game-panel panel-bot">
        <div class="panel-title">CONTROLS</div>
        <div class="segmented-control">
          <button class="segment" id="hint-btn" disabled title="Hint">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="12" cy="12" r="10"/>
              <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/>
              <line x1="12" y1="17" x2="12.01" y2="17"/>
            </svg>
          </button>
          <button class="segment" id="skip-btn" disabled title="Skip">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <polygon points="5 4 15 12 5 20 5 4"/>
              <line x1="19" y1="5" x2="19" y2="19"/>
            </svg>
          </button>
          <button class="segment" id="restart-btn" title="Settings">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="12" cy="12" r="3"/>
              <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/>
            </svg>
          </button>
        </div>
      </div>
    </aside>

  </div>
</div>

<div class="modal-overlay" id="game-over-modal">
  <div class="modal scale-in">
    <div class="modal-title">GAME OVER</div>

    <div class="modal-score-row">
      <span class="modal-score-label">FINAL SCORE</span>
      <span class="modal-score" id="final-score">0</span>
    </div>

    <div class="results-grid">
      <div class="result-card">
        <div class="result-val correct" id="final-correct">0</div>
        <div class="result-label">CORRECT</div>
      </div>
      <div class="result-card">
        <div class="result-val accuracy" id="final-accuracy">0%</div>
        <div class="result-label">ACCURACY</div>
      </div>
      <div class="result-card">
        <div class="result-val total" id="final-total">0</div>
        <div class="result-label">GUESSES</div>
      </div>
      <div class="result-card">
        <div class="result-val streak" id="final-streak">0</div>
        <div class="result-label">BEST STREAK</div>
      </div>
    </div>

    <p id="modal-save-notice" style="margin-top:16px;font-size:13px;color:var(--text-muted);text-align:center;"></p>

    <div class="modal-btns">
      <button class="btn btn-yellow btn-pixel modal-btn-primary" id="play-again-btn">PLAY AGAIN</button>
      <div class="modal-btn-row">
        <button class="btn btn-ghost" id="change-settings-btn">Settings</button>
        <a href="/view/leaderboard.php" class="btn btn-ghost">Leaderboard</a>
      </div>
    </div>
  </div>
</div>

<?php
require_once __DIR__ . '/footer.php';
renderFooter([
    '/public/js/autocomplete.js',
    '/public/js/game.js',
]);
?>

<script>
const GENERATIONS = [
  { num:1, label:'Gen 1', range:'1–151',   min:1,   max:151  },
  { num:2, label:'Gen 2', range:'152–251',  min:152, max:251  },
  { num:3, label:'Gen 3', range:'252–386',  min:252, max:386  },
  { num:4, label:'Gen 4', range:'387–493',  min:387, max:493  },
  { num:5, label:'Gen 5', range:'494–649',  min:494, max:649  },
  { num:6, label:'Gen 6', range:'650–721',  min:650, max:721  },
  { num:7, label:'Gen 7', range:'722–809',  min:722, max:809  },
  { num:8, label:'Gen 8', range:'810–905',  min:810, max:905  },
  { num:9, label:'Gen 9', range:'906–1025', min:906, max:1025 },
];

const DIFF_META = {
  easy:   { timer:60 },
  normal: { timer:30 },
  hard:   { timer:15 },
};

let selectedGens = new Set([1,2,3,4,5,6,7,8,9]);
let lobbyDiff    = 'normal';

(function() {
  const grid = document.getElementById('lobby-gen-grid');
  GENERATIONS.forEach(g => {
    const btn = document.createElement('button');
    btn.className = 'gen-btn active';
    btn.dataset.gen = g.num;
    btn.innerHTML = `<span class="gen-num">${g.label}</span><span class="gen-range">${g.range}</span>`;
    btn.addEventListener('click', () => {
      const isActive = selectedGens.has(g.num);
      if (isActive && selectedGens.size === 1) return;
      if (isActive) { selectedGens.delete(g.num); btn.classList.remove('active'); }
      else           { selectedGens.add(g.num);    btn.classList.add('active'); }
    });
    grid.appendChild(btn);
  });

  const pokeImg = document.getElementById('pokemon-img');
  pokeImg.addEventListener('dragstart', e => e.preventDefault());
})();

document.querySelectorAll('.lobby-diff-btn').forEach(btn => {
  btn.addEventListener('click', () => {
    document.querySelectorAll('.lobby-diff-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    lobbyDiff = btn.dataset.diff;
  });
});

function updateSidebars() {
  const chipsEl = document.getElementById('sidebar-gen-chips');
  if (chipsEl) {
    chipsEl.innerHTML = '';
    GENERATIONS.forEach(g => {
      const on  = selectedGens.has(g.num);
      const div = document.createElement('div');
      div.className = `gen-chip${on ? ' on' : ''}`;
      div.innerHTML = `
        <span class="gen-chip-label">${g.label}</span>
        <img src="/assets/gen${g.num}.png" class="gen-chip-img" alt="Gen ${g.num}" onerror="this.style.display='none'">
      `;
      chipsEl.appendChild(div);
    });
  }

  const diffEl = document.getElementById('sidebar-diff-display');
  if (diffEl) {
    diffEl.innerHTML = `
      <div class="segmented-control">
        <div class="segment diff-easy ${lobbyDiff === 'easy' ? 'active' : ''}">Easy</div>
        <div class="segment diff-normal ${lobbyDiff === 'normal' ? 'active' : ''}">Normal</div>
        <div class="segment diff-hard ${lobbyDiff === 'hard' ? 'active' : ''}">Hard</div>
      </div>
    `;
  }
}

document.getElementById('lobby-start-btn').addEventListener('click', () => {
  document.getElementById('lobby-overlay').classList.remove('open');
  updateSidebars();
  const genRanges = GENERATIONS
    .filter(g => selectedGens.has(g.num))
    .map(g => ({ min: g.min, max: g.max }));
  const genNums = [...selectedGens].sort((a,b) => a-b);
  Game.init(window._authUser || null, {
    genRanges,
    genNums,
    difficulty: lobbyDiff,
    timerSecs:  DIFF_META[lobbyDiff].timer,
  });
});

document.getElementById('play-again-btn').addEventListener('click', () => {
  document.getElementById('game-over-modal').classList.remove('open');
  const genRanges = GENERATIONS
    .filter(g => selectedGens.has(g.num))
    .map(g => ({ min: g.min, max: g.max }));
  const genNums = [...selectedGens].sort((a,b) => a-b);
  Game.restart({ genRanges, genNums, difficulty: lobbyDiff, timerSecs: DIFF_META[lobbyDiff].timer });
});

document.getElementById('change-settings-btn').addEventListener('click', () => {
  document.getElementById('game-over-modal').classList.remove('open');
  document.getElementById('lobby-overlay').classList.add('open');
});

Auth.init().then(user => {
  window._authUser = user;
  if (user) document.getElementById('auth-notice').style.display = 'none';
  const mn = document.getElementById('modal-save-notice');
  if (mn) mn.textContent = user ? '' : 'Log in to save your score.';
  Autocomplete.init('#guess-input', '#autocomplete-dropdown');
});
</script>
