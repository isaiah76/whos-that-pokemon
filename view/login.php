<?php require_once __DIR__ . '/header.php';
renderHeader("login");
?>
<main class="auth-page">
  <div class="auth-card animate-in">
    <h1 class="auth-title">LOG IN</h1>
    <form id="login-form" novalidate>
    <div class="form-group">
        <label class="form-label" for="username">Username or Email</label>
        <input class="form-input" type="text" id="username" name="username" 
               placeholder="Enter your username or email" autocomplete="username" required />
      </div>
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
      <p class="form-error" id="login-error"></p><button type="submit" class="btn btn-yellow btn-full btn-pixel"
        data-label="LOGIN" style="margin-top:8px;">LOGIN</button>
    </form>
    <div class="auth-footer">
      <p>Don't have an account? <a href="/view/signup.php">Sign up</a></p>
      <p style="margin-top:8px;"><a href="/view/game.php">Play as Guest</a></p>
    </div>
  </div>
</main>
<?php require_once __DIR__ . '/footer.php';
renderFooter();

?>
<script>
  Auth.init().then(user => {

    // if already logged in redirect
    if (user) {
      if (user.role === 'admin') {
        window.location.href = '/view/admin.php';
      } else {
        window.location.href = '/index.php';
      }
      return;
    }

    Auth.bindLoginForm('login-form');
  });
</script>
