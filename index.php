<?php require_once __DIR__ . '/view/header.php';
renderHeader("home");
?>
<main>
  <section class="home">
    <div class="home-content">
      <h1 class="home-title">WHO'S THAT<br>POKÉMON?</h1>
      <p class="home-desc">Guess the Pokémon from its silhouette before your lives run out. Do you have what it takes to
        climb the leaderboard and become a Pokémon master. </p>
      <div class="home-actions"><a href="/view/game.php" class="btn btn-yellow btn-lg btn-pixel">&#9654;PLAY</a></div>
    </div>
    <div class="home-silhouettes"><img class="home-sil-img" id="sil-1" src="" /><img class="home-sil-img" id="sil-2"
        src="" /><img class="home-sil-img" id="sil-3" src="" /><img class="home-sil-img" id="sil-4" src="" /><img
        class="home-sil-img" id="sil-5" src="" /></div>
  </section>
</main>
<?php require_once __DIR__ . '/view/footer.php';
renderFooter();

?>
<script>
  (function() {
    var silIds = [6, 25, 150, 143, 94];
    var base = 'https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/pokemon/other/official-artwork/';

    silIds.forEach(function(id, i) {
      var el = document.getElementById('sil-' + (i + 1));
      if (el) el.src = base + id + '.png';
    });
  })();
</script>
