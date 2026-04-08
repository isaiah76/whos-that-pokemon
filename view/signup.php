<?php require_once __DIR__ . '/header.php';
renderHeader("signup");
?>
<main class="auth-page">
  <div class="auth-card animate-in">
    <h1 class="auth-title">SIGN UP</h1>
    <form id="signup-form" novalidate>
      <div class="form-group"><label class="form-label" for="username">Username</label><input class="form-input"
          type="text" id="username" name="username" placeholder="Enter your username" autocomplete="username"
          maxlength="30" required /></div>
      <div class="form-group"><label class="form-label" for="email">Email</label><input class="form-input" type="email"
          id="email" name="email" placeholder="Enter your email" autocomplete="email" required /></div>
      <div class="form-group"><label class="form-label" for="password">Password</label>
        <div class="input-wrap"><input class="form-input" type="password" id="password" name="password"
            placeholder="Enter your password" autocomplete="new-password" minlength="8" required /><button type="button"
            class="eye_password" onclick="toggleEyePassword('password', this)"><svg xmlns="http://www.w3.org/2000/svg"
              width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
              stroke-linecap="round" stroke-linejoin="round">
              <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
              <circle cx="12" cy="12" r="3" />
            </svg></button></div>
      </div>
      <div class="form-group"><label class="form-label" for="confirm_password">Confirm Password</label>
        <div class="input-wrap"><input class="form-input" type="password" id="confirm_password" name="confirm_password"
            placeholder="Confirm your password" autocomplete="new-password" minlength="8" required /><button
            type="button" class="eye_password" onclick="toggleEyePassword('confirm_password', this)"><svg
              xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
              stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
              <circle cx="12" cy="12" r="3" />
            </svg></button></div>
      </div>
      <p class="form-error" id="signup-error"></p><button type="submit" class="btn btn-yellow btn-full btn-pixel"
        data-label="CREATE ACCOUNT" style="margin-top:8px;">CREATE ACCOUNT</button>
    </form>
    <div class="auth-footer">
      <p>Already have an account? <a href="/view/login.php">Log in</a></p>
      <p style="margin-top:8px;"><a href="/view/game.php">Play as Guest</a></p>
    </div>
  </div>
</main>
<?php require_once __DIR__ . '/footer.php';
renderFooter();

?>
<script>
  Auth.init().then(user => {
    if (user) {
      window.location.href = '/game.php';
      return;
    }

    Auth.bindSignupForm('signup-form');
  });
</script>
