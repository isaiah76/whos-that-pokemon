const Game = (() => {

  const DIFFICULTY_CONFIG = {
    easy:   { lives:5, pts:10, streakBonus:2,  hints:true  },
    normal: { lives:3, pts:20, streakBonus:5,  hints:true  },
    hard:   { lives:1, pts:40, streakBonus:15, hints:false },
  };

  const POKEAPI_BASE    = 'https://pokeapi.co/api/v2';
  const AUTO_NEXT_DELAY = 1600; 

  let S = {
    pokemon:        null,
    score:          0,
    streak:         0,
    bestStreak:     0,
    lives:          3,
    totalGuesses:   0,
    correctGuesses: 0,
    seenIds:        new Set(),
    difficulty:     'normal',
    genRanges:      [{ min:1, max:151 }],
    genNums:        [1],
    timerSecs:      30,
    timerLeft:      0,
    timerInterval:  null,
    autoNextTimeout:null,
    isGuessing:     true,
    hintsUsed:      0,
    gameOver:       false,
    sessionUser:    null,
    correctLetters: 0,
    revealCanvas:   null,
    revealCtx:      null,
    pokemonImage:   null,
    bounds:         null,
  };

  let dom = {};
  let bound = false;

  async function init(sessionUser, config = {}) {
    S.sessionUser = sessionUser;
    S.difficulty  = config.difficulty || 'normal';
    S.genRanges   = config.genRanges  || [{ min:1, max:151 }];
    S.genNums     = config.genNums    || [1];
    S.timerSecs   = config.timerSecs  ?? 30;

    dom = {
      pokemonImg:     document.getElementById('pokemon-img'),
      pokemonStage:   document.getElementById('pokemon-stage'),
      pokemonName:    document.getElementById('pokemon-name-reveal'),
      guessInput:     document.getElementById('guess-input'),
      guessBtn:       document.getElementById('guess-btn'),
      skipBtn:        document.getElementById('skip-btn'),
      hintBtn:        document.getElementById('hint-btn'),
      restartBtn:     document.getElementById('restart-btn'),
      scoreVal:       document.getElementById('score-value'),
      streakVal:      document.getElementById('streak-value'),
      livesDisplay:   document.getElementById('lives-display'),
      timerBlock:     document.getElementById('timer-block'),
      timerVal:       document.getElementById('timer-value'),
      loadingOverlay: document.getElementById('loading-overlay'),
      gameOverModal:  document.getElementById('game-over-modal'),
      finalScore:     document.getElementById('final-score'),
      finalCorrect:   document.getElementById('final-correct'),
      finalTotal:     document.getElementById('final-total'),
      finalAccuracy:  document.getElementById('final-accuracy'),
      finalStreak:    document.getElementById('final-streak'),
      hintArea:       document.getElementById('hint-area'),
      letterProgress: document.getElementById('letter-progress'),
      mScoreVal:      document.getElementById('m-score-value'),
      mStreakVal:     document.getElementById('m-streak-value'),
      mLivesDisplay:  document.getElementById('m-lives-display'),
      mTimerBlock:    document.getElementById('m-timer-block'),
      mTimerVal:      document.getElementById('m-timer-value'),
      mSkipBtn:       document.getElementById('m-skip-btn'),
      mHintBtn:       document.getElementById('m-hint-btn'),
      mRestartBtn:    document.getElementById('m-restart-btn'),
    };

    if (!bound) {
      dom.guessBtn?.addEventListener('click', handleGuess);
      dom.guessInput?.addEventListener('input',   handleTyping);
      dom.guessInput?.addEventListener('keydown', e => {
        if (e.key === 'Enter') handleGuess();
      });
      dom.skipBtn?.addEventListener('click', handleSkip);
      dom.hintBtn?.addEventListener('click', showHint);
      dom.mSkipBtn?.addEventListener('click', handleSkip);
      dom.mHintBtn?.addEventListener('click', showHint);

      const openSettings = () => {
        stopTimer();
        clearTimeout(S.autoNextTimeout);
        document.getElementById('game-over-modal')?.classList.remove('open');
        document.getElementById('lobby-overlay')?.classList.add('open');
      };
      dom.restartBtn?.addEventListener('click', openSettings);
      dom.mRestartBtn?.addEventListener('click', openSettings);

      document.querySelector('.game-page')?.addEventListener('mousedown', e => {
        const tag = e.target.tagName;
        if (tag === 'BUTTON' || tag === 'INPUT' || tag === 'A') return;
        if (!S.isGuessing || S.gameOver) return;
        e.preventDefault();
        dom.guessInput?.focus();
      });

      bound = true;
    }

    Autocomplete.setOnSelect(name => {
      if (dom.guessInput) dom.guessInput.value = name;
      handleTyping(); 
    });

    setupCanvas();
    resetRound();
    await loadNext();
  }

  async function restart(config = {}) {
    clearTimeout(S.autoNextTimeout);
    stopTimer();
    if (config.difficulty) S.difficulty = config.difficulty;
    if (config.genRanges)  S.genRanges  = config.genRanges;
    if (config.genNums)    S.genNums    = config.genNums;
    if (config.timerSecs !== undefined) S.timerSecs = config.timerSecs;
    resetRound();
    await loadNext();
  }

  function setupCanvas() {
    document.getElementById('reveal-canvas')?.remove();
    const c = document.createElement('canvas');
    c.id = 'reveal-canvas';
    dom.pokemonStage.appendChild(c);
    S.revealCanvas = c;
    S.revealCtx    = c.getContext('2d');
  }

  function resetRound() {
    const cfg        = DIFFICULTY_CONFIG[S.difficulty];
    S.score          = 0;
    S.streak         = 0;
    S.bestStreak     = 0;
    S.lives          = cfg.lives;
    S.totalGuesses   = 0;
    S.correctGuesses = 0;
    S.seenIds        = new Set();
    S.gameOver       = false;
    S.hintsUsed      = 0;
    S.correctLetters = 0;
    stopTimer();
    clearTimeout(S.autoNextTimeout);

    refreshScore();
    refreshStreak();
    refreshLives();
    hideModal();
    clearHints();
    hideName();

    if (Autocomplete.setEnabled) {
      Autocomplete.setEnabled(S.difficulty === 'easy');
    }

    document.body.classList.remove('difficulty-easy', 'difficulty-normal', 'difficulty-hard');
    document.body.classList.add(`difficulty-${S.difficulty}`);

    if (dom.timerBlock) {
      dom.timerBlock.style.display = S.timerSecs > 0 ? '' : 'none';
    }
  }

  async function loadNext() {
    if (S.gameOver) return;

    S.isGuessing     = true;
    S.hintsUsed      = 0;
    S.correctLetters = 0;

    stopTimer();
    clearTimeout(S.autoNextTimeout);
    showLoading(true);
    clearHints();
    hideName();
    resetStage();
    clearLetterProgress();
    setControls(false);

    try {
      const pokemon = await fetchRandom();
      S.pokemon = pokemon;

      const img = new Image();
      img.crossOrigin = 'anonymous';
      img.src = spriteUrl(pokemon);

      img.onload = () => {
        S.pokemonImage = img;
        
        S.revealCanvas.width  = img.naturalWidth;
        S.revealCanvas.height = img.naturalHeight;

        S.bounds = calculateSpriteBounds(img, img.naturalWidth, img.naturalHeight);

        const TYPE_COLORS = {
          fire: 'rgba(240,128,48,0.65)', water: 'rgba(104,144,240,0.65)',
          grass: 'rgba(120,200,80,0.65)', electric: 'rgba(248,208,48,0.65)',
          psychic: 'rgba(248,88,136,0.65)', ice: 'rgba(152,216,216,0.65)',
          dragon: 'rgba(112,56,248,0.65)', dark: 'rgba(160,120,90,0.65)',
          fairy: 'rgba(238,153,172,0.65)', fighting: 'rgba(192,48,40,0.65)',
          poison: 'rgba(160,64,160,0.65)', ground: 'rgba(224,192,104,0.65)',
          flying: 'rgba(168,144,240,0.65)', bug: 'rgba(168,184,32,0.65)',
          rock: 'rgba(184,160,56,0.65)', ghost: 'rgba(112,88,152,0.65)',
          steel: 'rgba(184,184,208,0.65)', normal: 'rgba(168,168,120,0.65)',
        };
        const primaryType = pokemon.types?.[0]?.type?.name || 'normal';
        const glowColor = TYPE_COLORS[primaryType] || 'rgba(232,200,64,0.55)';
        document.documentElement.style.setProperty('--pokemon-outline-color', glowColor);
        
        dom.pokemonImg.style.transition = 'none';
        dom.pokemonImg.src = img.src;
        dom.pokemonImg.classList.remove('revealed');
        void dom.pokemonImg.offsetWidth;
        dom.pokemonImg.style.transition = ''; 

        drawReveal(0);
        showLoading(false);
        buildLetterBoxes(pokemon.name);
        setControls(true);
        dom.guessInput.value = '';
        dom.guessInput.focus();
        startTimer();
      };

      img.onerror = () => { showLoading(false); loadNext(); };

    } catch(e) {
      console.error(e);
      showLoading(false);
      showToast('Failed to load Pokémon. Check your connection.', 'error');
    }
  }

  function calculateSpriteBounds(img, w, h) {
    const c = document.createElement('canvas');
    c.width = w; c.height = h;
    const ctx = c.getContext('2d', { willReadFrequently: true });
    ctx.drawImage(img, 0, 0);
    const data = ctx.getImageData(0,0,w,h).data;
    let top = h, bottom = 0;
    for(let y=0; y<h; y+=2){ 
      let hasPixel = false;
      for(let x=0; x<w; x+=2){
        if(data[(y*w + x)*4 + 3] > 10) { hasPixel = true; break; }
      }
      if(hasPixel) {
        if(y < top) top = y;
        if(y > bottom) bottom = y;
      }
    }
    return { top, bottom };
  }

  function startTimer() {
    if (S.timerSecs <= 0) return;
    S.timerLeft = S.timerSecs;
    refreshTimerDisplay();

    S.timerInterval = setInterval(() => {
      S.timerLeft = Math.max(0, S.timerLeft - 1);
      refreshTimerDisplay();
      if (S.timerLeft <= 0) { stopTimer(); onTimerExpired(); }
    }, 1000);
  }

  function stopTimer() {
    clearInterval(S.timerInterval);
    S.timerInterval = null;
  }

  function refreshTimerDisplay() {
    if (S.timerSecs <= 0) return;
    const txt = S.timerLeft + 's';
    const pct = S.timerLeft / S.timerSecs;
    if (dom.timerVal) dom.timerVal.textContent = txt;
    if (dom.mTimerVal) dom.mTimerVal.textContent = txt;
    if (dom.timerBlock) {
      dom.timerBlock.classList.remove('warning', 'danger');
      if      (pct <= 0.25) dom.timerBlock.classList.add('danger');
      else if (pct <= 0.50) dom.timerBlock.classList.add('warning');
    }
    if (dom.mTimerBlock) {
      dom.mTimerBlock.classList.remove('warning', 'danger');
      if      (pct <= 0.25) dom.mTimerBlock.classList.add('danger');
      else if (pct <= 0.50) dom.mTimerBlock.classList.add('warning');
    }
  }

  function onTimerExpired() {
    if (!S.isGuessing || S.gameOver) return;
    S.streak = 0;
    S.lives  = Math.max(0, S.lives - 1);
    refreshLives();
    refreshStreak();
    showToast("Time's up!", 'error');
    dom.pokemonStage.classList.add('flash-wrong');
    setTimeout(() => dom.pokemonStage.classList.remove('flash-wrong'), 500);

    if (S.lives <= 0) {
      handleGameOver();
    } else {
      drawReveal(1);
      revealPokemon();
      scheduleAutoNext();
    }
  }

async function fetchRandom() {
    const ranges = S.genRanges;
    if (!ranges || ranges.length === 0) throw new Error('No gen ranges configured');

    const totalPool = ranges.reduce((acc, r) => acc + (r.max - r.min + 1), 0);
    let id, tries = 0;

    do {
      let pos = Math.floor(Math.random() * totalPool);
      for (const r of ranges) {
        const size = r.max - r.min + 1;
        if (pos < size) { id = r.min + pos; break; }
        pos -= size;
      }
      tries++;
    } while (S.seenIds.has(id) && tries < 80);

    S.seenIds.add(id);
    const res = await fetch(`${POKEAPI_BASE}/pokemon/${id}`);
    if (!res.ok) throw new Error(`PokeAPI ${res.status}`);
    
    const data = await res.json();
    
    try {
      const speciesRes = await fetch(`${POKEAPI_BASE}/pokemon-species/${id}`);
      if (speciesRes.ok) {
        data.speciesData = await speciesRes.json();
      }
    } catch (e) {
      console.warn("Could not load species data for hints", e);
    }
    
    return data;
  }

  function spriteUrl(p) {
    return p.sprites?.other?.['home']?.front_default
        || p.sprites?.other?.['official-artwork']?.front_default
        || p.sprites?.front_default || '';
  }

  function handleTyping() {
    if (!S.isGuessing || S.gameOver || !S.pokemon) return;

    const target = S.pokemon.name.toLowerCase();
    let raw = dom.guessInput.value.toLowerCase().replace(/-/g, '');

    let composed = '';
    let rawIdx = 0;
    for (let i = 0; i < target.length; i++) {
      if (target[i] === '-') {
        if (rawIdx > 0 && rawIdx < raw.length) {
          composed += '-';
        } else {
          break;
        }
      } else {
        if (rawIdx < raw.length) { composed += raw[rawIdx++]; }
        else break;
      }
    }

    if (dom.guessInput.value.toLowerCase() !== composed) {
      dom.guessInput.value = composed;
    }

    let match = 0;
    for (let i = 0; i < composed.length && i < target.length; i++) {
      if (composed[i] === target[i]) match = i + 1;
      else break;
    }
    S.correctLetters = match;
    updateLetterBoxes(composed, target);
    drawReveal(match / target.length);

    if (composed === target) {
      dom.guessInput.disabled = true;
      setTimeout(() => { if (S.isGuessing && !S.gameOver) submitGuess(composed); }, 300);
    }
  }

  function handleGuess() {
    if (!S.isGuessing || S.gameOver) return;
    const guess = dom.guessInput.value.trim().toLowerCase();
    if (!guess) return;
    submitGuess(guess);
  }

  function submitGuess(guess) {
    if (!S.isGuessing || S.gameOver || !S.pokemon) return;
    S.totalGuesses++;
    if (guess === S.pokemon.name.toLowerCase()) handleCorrect();
    else handleWrong();
  }

  function handleCorrect() {
    stopTimer();
    const cfg  = DIFFICULTY_CONFIG[S.difficulty];
    S.streak++;
    S.bestStreak = Math.max(S.bestStreak, S.streak);
    S.correctGuesses++;
    const bonus = S.streak > 1 ? cfg.streakBonus * (S.streak - 1) : 0;
    const pts   = cfg.pts + bonus;
    S.score    += pts;

    revealPokemon();
    refreshScore();
    refreshStreak();

    dom.pokemonStage.classList.add('flash-correct');
    setTimeout(() => dom.pokemonStage.classList.remove('flash-correct'), 600);
    showToast(`+${pts} pts`, 'success');
    scheduleAutoNext();
  }

  function handleWrong() {
    S.streak = 0;
    S.lives  = Math.max(0, S.lives - 1);
    refreshLives();
    refreshStreak();

    dom.pokemonStage.classList.add('flash-wrong');
    dom.guessInput.classList.add('shake');
    setTimeout(() => {
      dom.pokemonStage.classList.remove('flash-wrong');
      dom.guessInput.classList.remove('shake');
    }, 450);

    Autocomplete.clear();
    dom.guessInput.value = '';
    drawReveal(0);
    updateLetterBoxes('', S.pokemon?.name?.toLowerCase() || '');
    dom.guessInput.focus();

    if (S.lives <= 0) handleGameOver();
    else showToast(`Wrong!`, 'error');
  }

function handleSkip() {
    if (!S.isGuessing || S.gameOver) return;
    stopTimer();

    S.streak = 0;
    S.lives = Math.max(0, S.lives - 1);
    S.totalGuesses++;

    refreshStreak();
    refreshLives();
    revealPokemon();
    showToast(`It was ${S.pokemon.name}!`, 'info');

    if (S.lives <= 0) {
      handleGameOver();
    } else {
      scheduleAutoNext();
    }
  }

  function scheduleAutoNext() {
    S.isGuessing = false;
    setControls(false);
    S.autoNextTimeout = setTimeout(() => loadNext(), AUTO_NEXT_DELAY);
  }

  function revealPokemon() {
    if (S.revealCanvas) {
      S.revealCanvas.style.transition = 'none';
      S.revealCanvas.style.opacity = '0';
    }

    dom.pokemonImg.classList.add('revealed');
    dom.pokemonStage.classList.add('revealed');
    dom.pokemonName.textContent = S.pokemon.name;
    dom.pokemonName.classList.add('show');
  }

function showHint() {
    if (!S.isGuessing || S.gameOver) return;
    if (!DIFFICULTY_CONFIG[S.difficulty].hints) return;
    const p = S.pokemon;
    if (!p) return;
    S.hintsUsed++;

    let html = '';
    switch (S.hintsUsed) {
      case 1:
        html = `<span class="hint-label">First letter</span><strong>${p.name[0]}</strong>`;
        break;
      case 2: {
        let desc = 'Description unavailable.';
        if (p.speciesData && p.speciesData.flavor_text_entries) {
          const entry = p.speciesData.flavor_text_entries.find(e => e.language.name === 'en');
          if (entry) {
            desc = entry.flavor_text.replace(/[\n\f\r]/g, ' ');
            
            let engName = p.name;
            const nameObj = p.speciesData.names?.find(n => n.language.name === 'en');
            if (nameObj) engName = nameObj.name;
            
            const nameRegex = new RegExp(engName, 'gi');
            desc = desc.replace(nameRegex, '???');
            const rawRegex = new RegExp(p.name, 'gi');
            desc = desc.replace(rawRegex, '???');
          }
        }
        html = `<span class="hint-label">Pokédex</span><strong style="font-weight: normal; font-size: 0.95em; line-height: 1.4;">${desc}</strong>`;
        break;
      }
      case 3: {
        const t = p.types.map(t =>
          `<span class="type-badge type-${t.type.name}">${t.type.name}</span>`
        ).join(' ');
        html = `<span class="hint-label">Type</span>${t}`;
        break;
      }
      default:
        showToast('No more hints!', 'info');
        return;
    }

    const el = document.createElement('div');
    el.className = 'hint-item animate-in';
    el.innerHTML = html;
    dom.hintArea.appendChild(el);

    S.score -= 5;
    refreshScore();
  }

  async function handleGameOver() {
    S.gameOver   = true;
    S.isGuessing = false;
    stopTimer();
    clearTimeout(S.autoNextTimeout);
    revealPokemon();
    setControls(false);

    const acc = S.totalGuesses > 0
      ? Math.round((S.correctGuesses / S.totalGuesses) * 100) : 0;

    if (dom.finalScore)    dom.finalScore.textContent    = S.score;
    if (dom.finalCorrect)  dom.finalCorrect.textContent  = S.correctGuesses;
    if (dom.finalTotal)    dom.finalTotal.textContent    = S.totalGuesses;
    if (dom.finalAccuracy) dom.finalAccuracy.textContent = acc + '%';
    if (dom.finalStreak)   dom.finalStreak.textContent   = S.bestStreak;

    if (S.sessionUser) {
      await API.saveScore({
        score:       S.score,
        correct:     S.correctGuesses,
        total:       S.totalGuesses,
        difficulty:  S.difficulty,
        best_streak: S.bestStreak,
        gens:        S.genNums.slice().sort((a,b)=>a-b).join(','),
      });
    }

    setTimeout(showModal, 800);
  }

  function drawReveal(progress) {
    const c   = S.revealCanvas;
    const ctx = S.revealCtx;
    const img = S.pokemonImage;
    if (!c || !ctx || !img || !S.bounds) return;

    const W = c.width, H = c.height;
    ctx.clearRect(0, 0, W, H);
    if (progress <= 0) return;

    ctx.globalCompositeOperation = 'source-over';
    ctx.drawImage(img, 0, 0, W, H);

    const mask = document.createElement('canvas');
    mask.width = W; mask.height = H;
    const mCtx = mask.getContext('2d');

    const spriteH = S.bounds.bottom - S.bounds.top;
    const revealHeight = spriteH * progress;
    const startY = S.bounds.bottom - revealHeight;

    mCtx.fillStyle = '#fff';
    mCtx.fillRect(0, startY, W, H - startY); 

    ctx.globalCompositeOperation = 'destination-in';
    ctx.drawImage(mask, 0, 0);
    ctx.globalCompositeOperation = 'source-over';
  }

  function buildLetterBoxes(name) {
    if (!dom.letterProgress) return;
    dom.letterProgress.innerHTML = '';
    if (dom.guessInput) dom.guessInput.maxLength = name.length;
    for (const ch of name) {
      const b = document.createElement('div');
      b.className = 'letter-box';
      if (ch === '-') {
        b.textContent = '-';
        b.style.borderBottom = 'none';
        b.style.color = 'var(--text-dim)';
      }
      dom.letterProgress.appendChild(b);
    }
  }

  function updateLetterBoxes(typed, target) {
    if (!dom.letterProgress) return;
    const boxes = dom.letterProgress.querySelectorAll('.letter-box');
    boxes.forEach((box, i) => {
      if (target[i] === '-') return;
      if (i < typed.length) {
        const correct = typed[i] === target[i];
        box.classList.toggle('correct', correct);
        box.classList.toggle('wrong',   !correct);
        box.textContent = typed[i];
      } else {
        box.classList.remove('correct', 'wrong');
        box.textContent = '';
      }
    });
  }

  function clearLetterProgress() {
    if (dom.letterProgress) dom.letterProgress.innerHTML = '';
  }

  function showLoading(show) {
    if (dom.loadingOverlay) dom.loadingOverlay.style.display = show ? 'flex' : 'none';
  }

  function resetStage() {
    dom.pokemonImg?.classList.remove('revealed');
    dom.pokemonStage?.classList.remove('revealed');
    
    if (S.revealCanvas) {
      S.revealCanvas.style.transition = 'none';
      S.revealCanvas.style.transform = 'scale(1)';
      S.revealCanvas.style.opacity = '1';
    }

    hideName();
    clearHints();
    Autocomplete.clear();
    if (S.revealCtx && S.revealCanvas)
      S.revealCtx.clearRect(0, 0, S.revealCanvas.width, S.revealCanvas.height);
  }

  function hideName()   { dom.pokemonName?.classList.remove('show'); }
  function clearHints() { if (dom.hintArea) dom.hintArea.innerHTML = ''; }

  function setControls(on) {
    const isHard = S.difficulty === 'hard';
    [dom.guessInput, dom.guessBtn]
      .forEach(el => { if (el) el.disabled = !on; });
    if (dom.skipBtn)  dom.skipBtn.disabled  = !on || isHard;
    if (dom.hintBtn)  dom.hintBtn.disabled  = !on || isHard;
    if (dom.mSkipBtn) dom.mSkipBtn.disabled = !on || isHard;
    if (dom.mHintBtn) dom.mHintBtn.disabled = !on || isHard;
  }

  function refreshScore()  { if (dom.scoreVal)  dom.scoreVal.textContent  = S.score;  if (dom.mScoreVal)  dom.mScoreVal.textContent  = S.score; }
  function refreshStreak() { if (dom.streakVal) dom.streakVal.textContent = S.streak; if (dom.mStreakVal) dom.mStreakVal.textContent = S.streak; }
  function refreshLives() {
    const total = DIFFICULTY_CONFIG[S.difficulty].lives;
    const heartsHtml = Array.from({ length: total }, (_, i) =>
      `<span class="heart ${i >= S.lives ? 'lost' : ''}">❤</span>`
    ).join('');
    if (dom.livesDisplay)  dom.livesDisplay.innerHTML  = heartsHtml;
    if (dom.mLivesDisplay) dom.mLivesDisplay.innerHTML = heartsHtml;
  }

  function showModal() { dom.gameOverModal?.classList.add('open'); }
  function hideModal() { dom.gameOverModal?.classList.remove('open'); }

  return { init, restart };
})();
