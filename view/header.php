<?php function renderHeader(string $active = ''): void
{
    ?>
  <!DOCTYPE html>
    <html lang="en">

    <head>
      <meta charset="UTF-8" />
      <meta name="viewport" content="width=device-width, initial-scale=1.0" />
      <title>Who's that Pokémon?</title>
      <link rel="icon" type="image/png" href="/assets/pokeball.png" />
      <link rel="shortcut icon" href="/assets/pokeball.png" />
      <link rel="stylesheet" href="/public/css/style.css" />
    </head>

    <body>
      <div id="page-loader">
        <div class="spinner"></div>
      </div>
      <nav class="nav"><a href="/index.php" class="nav-brand"><img src="/assets/pokeball.png" class="nav-icon"
            alt=""><span class="nav-logo">Who's that Pokémon?</span>
        </a>
        <div class="nav-right">
          <button class="nav-help-btn" id="help-btn">?</button>
          <ul class="nav-links" id="nav-links">
            <li><a href="/index.php" <?= $active === 'home' ? 'class="active"' : '' ?>>Home</a></li>
            <li><a href="/view/game.php" <?= $active === 'game' ? 'class="active"' : '' ?>>Play</a></li>
            <li><a href="/view/leaderboard.php" <?= $active === 'leaderboard' ? 'class="active"' : '' ?>>Leaderboard</a></li>
            <li id="nav-admin" style="display:none"><a href="/view/admin.php">Admin</a></li>
            <li id="nav-login" style="display:none"><a href="/view/login.php" <?= $active === 'login' ? 'class="active"' : '' ?>>Login</a></li>
            <li id="nav-signup" style="display:none"><a href="/view/signup.php" <?= $active === 'signup' ? 'class="active"' : '' ?>>Sign Up</a></li>
          </ul>

          <!-- Avatar dropdown (shown when logged in) -->
          <div class="nav-avatar-wrap" id="nav-avatar-wrap" style="display:none;">
            <button class="nav-avatar-btn" id="nav-avatar-btn" aria-label="Account menu">
              <img src="" alt="Avatar" id="nav-avatar-img" />
            </button>
            <div class="nav-avatar-dropdown" id="nav-avatar-dropdown">
              <div class="nav-dropdown-header">
                <div class="nav-dropdown-username" id="nav-dropdown-username"></div>
                <div class="nav-dropdown-role" id="nav-dropdown-role"></div>
              </div>
              <a href="/view/profile.php" class="nav-dropdown-item">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>
                Profile
              </a>
              <a href="/view/settings.php" class="nav-dropdown-item">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
                Settings
              </a>
              <div id="nav-dropdown-admin-item" style="display:none;">
                <div class="nav-dropdown-divider"></div>
                <a href="/view/admin.php" class="nav-dropdown-item">
                  <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/></svg>
                  Admin
                </a>
              </div>
              <div class="nav-dropdown-divider"></div>
              <button class="nav-dropdown-item danger" id="nav-dropdown-logout">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                Log out
              </button>
            </div>
          </div>

          <button class="nav-burger" id="nav-burger"><span></span><span></span><span></span></button>
        </div>
      </nav>
      <div class="help-panel-overlay" id="help-panel-overlay">
        <div class="help-panel" role="dialog">
          <div class="help-panel-header"> <span class="help-panel-title">HOW TO PLAY</span> <button
              class="help-panel-close" id="help-panel-close">Close</button> </div>
          <div class="help-section">
            <div class="help-section-title">GAMEPLAY</div>
            <div class="help-row"><span class="help-step">01</span><span>A Pokémon appears as a black silhouette.</span>
            </div>
            <div class="help-row"><span class="help-step">02</span><span>Type the Pokémons name in the input.</span>
            </div>
            <div class="help-row"><span class="help-step">03</span><span>Skip reveals the answer but gives no points and
                resets your streak.</span></div>
            <div class="help-row"><span class="help-step">04</span><span>Hints gives up to 3 clues. Each hint reduces your
                score by 5 pts.</span></div>
          </div>
          <div class="help-section">
            <div class="help-section-title">SCORING</div>
            <table class="help-table">
              <tr>
                <td>Correct guess</td>
                <td class="help-val yellow">plus points</td>
              </tr>
              <tr>
                <td>Streak</td>
                <td class="help-val yellow">multiplied points</td>
              </tr>
              <tr>
                <td>Timer expires</td>
                <td class="help-val red">−1 life</td>
              </tr>
              <tr>
                <td>Wrong guess</td>
                <td class="help-val red">−1 life</td>
              </tr> 
              <tr>
                <td>Skip</td>
                <td class="help-val red">-1 life</td>
              </tr>
              <tr>
                <td>Using a hint</td>
                <td class="help-val muted">−5 pts</td>
              </tr>
            </table>
          </div>
          <div class="help-section">
            <div class="help-section-title">DIFFICULTY</div>
            <div class="diff-container" style="display: flex; gap: 10px;">
              <div class="diff-row diff-easy" style="flex: 1;">
                <div class="diff-row-title">Easy</div>
                <div class="diff-row-desc">
                  <ul>
                    <li>5 lives</li>
                    <li>3 hints</li>
                    <li>Autocomplete</li>
                    <li>+10 pts</li>
                    <li>60s timer</li>
                  </ul>
                </div>
              </div>
              <div class="diff-row diff-normal" style="flex: 1;">
                <div class="diff-row-title">Normal</div>
                <div class="diff-row-desc">
                  <ul>
                    <li>3 lives</li>
                    <li>3 hints</li>
                    <li>+20 pts</li>
                    <li>30s timer</li>
                  </ul>
                </div>
              </div>
              <div class="diff-row diff-hard" style="flex: 1;">
                <div class="diff-row-title">Hard</div>
                <div class="diff-row-desc">
                  <ul>
                    <li>1 life</li>
                    <li>No hints</li>
                    <li>No skip</li>
                    <li>+40 pts</li>
                    <li>15s timer</li>
                  </ul>
                </div>
              </div>
            </div>
          </div>
          <div class="help-section">
            <div class="help-section-title">GENERATIONS</div>
            <p class="help-text">Select what generations you want before the game starts.</p>
          </div>
          <div class="help-section">
            <div class="help-section-title">LEADERBOARD</div>
            <p class="help-text">Create an account to save scores and appear on the leaderboard. Playing as guest is
              available but scores are not stored.</p>
          </div>
        </div>
      </div>
    <?php
}
