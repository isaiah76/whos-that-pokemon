const Auth = (() => {
  let currentUser = null;

  async function init() {
    const result = await API.checkSession();
    if (result.success && result.user) {
      currentUser = result.user;
    }
    updateNav();
    return currentUser;
  }

  function getUser() {
    return currentUser;
  }
  function isLoggedIn() {
    return currentUser !== null;
  }
  function isAdmin() {
    return currentUser?.role === "admin";
  }

  const AVATARS = [
    'eevee.jpg','bulbasaur.jpg','cubone.jpg','meowth.jpg','munchlax.jpg',
    'pikachu.jpg','piplup.jpg','snivy.jpg','togepi.jpg'
  ];

  function getAvatarUrl(user) {
    if (user.avatar) return `/assets/${user.avatar}`;
    const idx = (user.id || 0) % AVATARS.length;
    return `/assets/${AVATARS[idx]}`;
  }

  function updateNav() {
    const navLogin      = document.getElementById('nav-login');
    const navSignup     = document.getElementById('nav-signup');
    const navAdmin      = document.getElementById('nav-admin');
    const navAvatarWrap = document.getElementById('nav-avatar-wrap');
    const navAvatarImg  = document.getElementById('nav-avatar-img');
    const navDropdownUsername = document.getElementById('nav-dropdown-username');
    const navDropdownRole     = document.getElementById('nav-dropdown-role');
    const navDropdownAdmin    = document.getElementById('nav-dropdown-admin-item');

    if (isLoggedIn()) {
      if (navLogin)  navLogin.style.display  = 'none';
      if (navSignup) navSignup.style.display = 'none';
      if (navAdmin)  navAdmin.style.display  = 'none';

      if (navAvatarWrap) navAvatarWrap.style.display = '';
      if (navAvatarImg)  navAvatarImg.src = getAvatarUrl(currentUser);
      if (navDropdownUsername) navDropdownUsername.textContent = currentUser.username;
      if (navDropdownRole)     navDropdownRole.textContent = currentUser.role === 'admin' ? 'Administrator' : 'Trainer';
      if (navDropdownAdmin)    navDropdownAdmin.style.display = isAdmin() ? '' : 'none';

      const btn  = document.getElementById('nav-avatar-btn');
      const drop = document.getElementById('nav-avatar-dropdown');
      if (btn && drop && !btn._dropBound) {
        btn._dropBound = true;
        btn.addEventListener('click', (e) => {
          e.stopPropagation();
          drop.classList.toggle('open');
        });
        document.addEventListener('click', () => drop.classList.remove('open'));
      }

      const logoutBtn = document.getElementById('nav-dropdown-logout');
      if (logoutBtn && !logoutBtn._logoutBound) {
        logoutBtn._logoutBound = true;
        logoutBtn.addEventListener('click', async () => {
          await API.logout();
          currentUser = null;
          window.location.href = '/index.php';
        });
      }
    } else {
      if (navAvatarWrap) navAvatarWrap.style.display = 'none';
      if (navLogin)  navLogin.style.display  = '';
      if (navSignup) navSignup.style.display = '';
      if (navAdmin)  navAdmin.style.display  = 'none';
    }
  }

  function bindLoginForm(formId) {
    const form = document.getElementById(formId);
    const errEl = document.getElementById("login-error");
    const submitBtn = form?.querySelector('[type="submit"]');

    if (!form) return;

    form.addEventListener("submit", async (e) => {
      e.preventDefault();
      if (errEl) errEl.classList.remove("visible");

      const username = form.querySelector('[name="username"]').value.trim();
      const password = form.querySelector('[name="password"]').value;

      if (!username || !password) {
        showError(errEl, "Please fill in all fields.");
        return;
      }

      setLoading(submitBtn, true);
      const result = await API.login(username, password);
      setLoading(submitBtn, false);

      if (result.success) {
        currentUser = result.user;
        showToast("Welcome back, " + result.user.username + "!", "success");
        setTimeout(() => {
          if (currentUser.role === 'admin') {
            window.location.href = "/view/admin.php";
          } else {
            window.location.href = "/view/game.php";
          }
        }, 700);
      } else {
        showError(errEl, result.message || "Login failed.");
      }
    });
  }

  function bindSignupForm(formId) {
    const form = document.getElementById(formId);
    const errEl = document.getElementById("signup-error");
    const submitBtn = form?.querySelector('[type="submit"]');

    if (!form) return;

    form.addEventListener("submit", async (e) => {
      e.preventDefault();
      if (errEl) errEl.classList.remove("visible");

      const username = form.querySelector('[name="username"]').value.trim();
      const email = form.querySelector('[name="email"]').value.trim();
      const password = form.querySelector('[name="password"]').value;
      const confirm = form.querySelector('[name="confirm_password"]').value;

      if (!username || !email || !password) {
        showError(errEl, "All fields are required.");
        return;
      }

      if (password !== confirm) {
        showError(errEl, "Passwords do not match.");
        return;
      }

      if (password.length < 8) {
        showError(errEl, "Password must be at least 8 characters.");
        return;
      }

      setLoading(submitBtn, true);
      const result = await API.register(username, email, password);
      setLoading(submitBtn, false);

      if (result.success) {
        showToast("Account created! Logging you in...", "success");
        const loginResult = await API.login(username, password);
        if (loginResult.success) {
          currentUser = loginResult.user;
          setTimeout(() => {
            window.location.href = "game.php";
          }, 700);
        } else {
          setTimeout(() => {
            window.location.href = "login.php";
          }, 700);
        }
      } else {
        showError(errEl, result.message || "Registration failed.");
      }
    });
  }

  function bindLogout(btnId) {
    const btn = document.getElementById(btnId);
    if (!btn) return;

    btn.addEventListener("click", async (e) => {
      e.preventDefault();
      await API.logout();
      currentUser = null;
      window.location.href = "index.php";
    });
  }

  function showError(el, msg) {
    if (el) {
      el.textContent = msg;
      el.classList.add("visible");
    }
  }

  function setLoading(btn, loading) {
    if (!btn) return;
    btn.disabled = loading;
    btn.textContent = loading
      ? "Please wait..."
      : btn.dataset.label || btn.textContent;
  }

  return {
    init,
    getUser,
    isLoggedIn,
    isAdmin,
    updateNav,
    getAvatarUrl,
    bindLoginForm,
    bindSignupForm,
    bindLogout,
  };
})();
