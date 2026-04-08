<?php
require_once __DIR__ . '/header.php';
renderHeader("leaderboard");
?>
<main class="page">
  <div class="container">

    <div class="leaderboard-header">
      <h1 class="section-title">HALL OF FAME</h1>
    </div>

    <div class="lb-tabs">
      <button class="lb-tab active" data-diff="all">ALL</button>
      <div class="lb-tab-group">
        <button class="lb-tab lb-tab-easy"   data-diff="easy">EASY</button>
        <button class="lb-tab lb-tab-normal" data-diff="normal">NORMAL</button>
        <button class="lb-tab lb-tab-hard"   data-diff="hard">HARD</button>
      </div>
    </div>

    <div id="leaderboard-loading" style="display:flex;justify-content:center;padding:60px;">
      <div class="spinner"></div>
    </div>
    <p id="leaderboard-empty" style="display:none;text-align:center;color:var(--text-muted);padding:60px;">
      No scores yet for this filter. <a href="/view/game.php">Be the first!</a>
    </p>

    <div class="leaderboard-table-wrap" id="leaderboard-table-container" style="display:none;">
      <table class="leaderboard-table">
        <thead>
          <tr>
            <th>#</th>
            <th>TRAINER</th>
            <th>GENERATIONS</th>
            <th>DIFFICULTY</th>
            <th>MAX STREAK</th>
            <th>SCORE</th>
          </tr>
        </thead>
        <tbody id="leaderboard-tbody"></tbody>
      </table>
    </div>

    <div style="text-align:center;margin-top:40px;" id="leaderboard-cta">
      <p style="color:var(--text-muted);margin-bottom:16px;">Want to appear on the leaderboard?</p>
      <a href="/view/signup.php" class="btn btn-yellow btn-pixel">CREATE ACCOUNT</a>
    </div>

  </div>
</main>

<div class="modal-overlay" id="lb-profile-overlay">
  <div class="modal lb-profile-modal" id="lb-profile-modal">
    <div style="text-align:center;padding:60px 40px;"><div class="spinner"></div></div>
  </div>
</div>

<?php
require_once __DIR__ . '/footer.php';
renderFooter(['/public/js/leaderboard.js']);
?>
<script>
  Auth.init().then(user => {
    if (user) document.getElementById('leaderboard-cta').style.display = 'none';
    Leaderboard.init();
  });
  document.getElementById('lb-profile-overlay')?.addEventListener('click', e => {
    if (e.target === e.currentTarget) e.currentTarget.classList.remove('open');
  });
  document.addEventListener('keydown', e => {
    if (e.key === 'Escape') document.getElementById('lb-profile-overlay')?.classList.remove('open');
  });
</script>
