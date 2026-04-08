const Autocomplete = (() => {
  let allNames    = []; 
  let onSelect    = null; 
  let inputEl     = null;
  let dropdownEl  = null;
  let highlighted = -1; 
  let visibleItems = []; 
  let isEnabled = true;

  async function loadNames() {
    if (allNames.length > 0) return;
    try {
      const res  = await fetch('https://pokeapi.co/api/v2/pokemon?limit=10000');
      const data = await res.json();
      allNames = data.results.map(p => p.name);
    } catch (err) {
      console.error('Autocomplete: failed to load Pokémon names', err);
    }
  }

  function init(inputSelector, dropdownSelector, selectCallback) {
    inputEl    = document.querySelector(inputSelector);
    dropdownEl = document.querySelector(dropdownSelector);
    onSelect   = selectCallback;

    if (!inputEl || !dropdownEl) return;

    loadNames();

    inputEl.addEventListener('input',   handleInput);
    inputEl.addEventListener('keydown', handleKeydown);
    inputEl.addEventListener('blur',    () => setTimeout(close, 160));
    inputEl.addEventListener('focus',   () => {
      if (inputEl.value.trim().length >= 1) handleInput();
    });
  }

  function handleInput() {
    if (!isEnabled) return;

    const query = inputEl.value.trim().toLowerCase();
    if (query.length < 1) { close(); return; }

    const matches = allNames
      .filter(name => name.startsWith(query))
      .slice(0, 8);

    render(matches, query.length);
  }

  function handleKeydown(e) {
    if (!isEnabled || !dropdownEl.classList.contains('open')) return;

    switch (e.key) {
      case 'ArrowDown':
        e.preventDefault();
        highlighted = Math.min(highlighted + 1, visibleItems.length - 1);
        updateHighlight();
        break;
      case 'ArrowUp':
        e.preventDefault();
        highlighted = Math.max(highlighted - 1, -1);
        updateHighlight();
        break;
      case 'Enter':
        e.preventDefault();
        if (highlighted >= 0 && visibleItems[highlighted]) {
          selectItem(visibleItems[highlighted].dataset.name);
        }
        break;
      case 'Escape':
        close();
        break;
    }
  }

  function updateHighlight() {
    visibleItems.forEach((item, i) => {
      item.classList.toggle('highlighted', i === highlighted);
    });
  }

  function render(matches, prefixLen) {
    dropdownEl.innerHTML = '';
    visibleItems  = [];
    highlighted   = -1;

    if (matches.length === 0) { close(); return; }

    const frag = document.createDocumentFragment();

    matches.forEach(name => {
      const item = document.createElement('div');
      item.className    = 'autocomplete-item';
      item.dataset.name = name;

      const bold = `<span class="match-highlight">${name.slice(0, prefixLen)}</span>`;
      const rest = name.slice(prefixLen);
      item.innerHTML = bold + rest;

      item.addEventListener('mousedown', (e) => {
        e.preventDefault(); 
        selectItem(name);
      });

      frag.appendChild(item);
      visibleItems.push(item);
    });

    dropdownEl.appendChild(frag);
    dropdownEl.classList.add('open');
  }

  function selectItem(name) {
    inputEl.value = name;
    close();
    if (typeof onSelect === 'function') onSelect(name);
  }

  function close() {
    dropdownEl.classList.remove('open');
    dropdownEl.innerHTML = '';
    visibleItems  = [];
    highlighted   = -1;
  }

  function clear() {
    if (inputEl) inputEl.value = '';
    close();
  }

  function setOnSelect(fn) {
    onSelect = fn;
  }

  function setEnabled(val) {
    isEnabled = val;
    if (!isEnabled) close();
  }

  return {
    init,
    loadNames,
    clear,
    setOnSelect,
    setEnabled,
    getAllNames: () => allNames,
  };
})();
