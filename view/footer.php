<?php function renderFooter(array $extraScripts = []): void
{
?>
  <script src="/public/js/ui.js"></script>
  <script src="/public/js/api.js"></script>
  <script src="/public/js/auth.js"></script>
  <?php foreach ($extraScripts as $src): ?>
    <script src="<?= htmlspecialchars($src) ?>"></script>
  <?php endforeach;

  ?>
  <script>
    (function() {
      const burger = document.getElementById('nav-burger');
      const links = document.getElementById('nav-links');
      const helpBtn = document.getElementById('help-btn');
      const overlay = document.getElementById('help-panel-overlay');
      const closeBtn = document.getElementById('help-panel-close');

      // hamburger
      if (burger && links) {
        burger.addEventListener('click', () => {
          const isOpen = links.classList.toggle('open');
          burger.classList.toggle('open', isOpen);
          document.body.style.overflow = isOpen ? 'hidden' : '';
        });

        links.querySelectorAll('a').forEach(a => {
          a.addEventListener('click', () => {
            links.classList.remove('open');
            burger.classList.remove('open');
            document.body.style.overflow = '';
          });
        });
      }

      // help panel
      function openHelp() {
        overlay?.classList.add('open');
        document.body.style.overflow = 'hidden';
      }

      function closeHelp() {
        overlay?.classList.remove('open');
        document.body.style.overflow = '';
      }

      helpBtn?.addEventListener('click', openHelp);
      closeBtn?.addEventListener('click', closeHelp);

      overlay?.addEventListener('click', e => {
        if (e.target === overlay) closeHelp();
      });

      document.addEventListener('keydown', e => {
        if (e.key === 'Escape') closeHelp();
      });
    })();
  </script>
  <script>
    Auth.init().then(() => {
      const logoutLink = document.getElementById('logout-link');
      if (!logoutLink) return;

      logoutLink.addEventListener('click', async e => {
        e.preventDefault();
        await API.logout();
        window.location.href = '/index.php';
      });
    });
  </script>
  </body>

  </html>
<?php
}
