/** Native Autocomplete für Benutzer über die Benutzer-API. */
(() => {
    const scriptUrl = document.currentScript?.src;
    const apiUrl = scriptUrl
        ? new URL('../API/BenutzerAutoComp_API.php', scriptUrl).href
        : '../API/BenutzerAutoComp_API.php';
    const minimumLength = 2;

    function initBenutzerAutocomplete() {
        const input = document.querySelector('#benutzer');
        if (!input || input.dataset.autocompleteReady === 'true') {
            return;
        }

        input.dataset.autocompleteReady = 'true';
        input.setAttribute('autocomplete', 'off');
        input.setAttribute('aria-autocomplete', 'list');
        input.parentElement?.classList.add('benutzer-autocomplete-container');

        const hiddenInput = document.querySelector('#ben_id');
        const list = document.createElement('ul');
        list.className = 'benutzer-autocomplete-list';
        list.id = `${input.id}-suggestions`;
        list.setAttribute('role', 'listbox');
        list.hidden = true;
        input.setAttribute('aria-controls', list.id);
        input.setAttribute('aria-expanded', 'false');
        input.insertAdjacentElement('afterend', list);

        let selectedIndex = -1;
        let requestSequence = 0;
        let debounceTimer;

        function closeList() {
            list.hidden = true;
            list.replaceChildren();
            selectedIndex = -1;
            input.setAttribute('aria-expanded', 'false');
            input.removeAttribute('aria-activedescendant');
        }

        function selectItem(item) {
            input.value = item.value || item.label;
            if (hiddenInput) {
                hiddenInput.value = item.id;
                hiddenInput.dispatchEvent(new Event('change', { bubbles: true }));
            }
            input.dispatchEvent(new Event('change', { bubbles: true }));
            closeList();
        }

        function renderItems(items) {
            list.replaceChildren();
            selectedIndex = -1;

            items.forEach((item, index) => {
                const option = document.createElement('li');
                option.className = 'benutzer-autocomplete-option';
                option.id = `${list.id}-${index}`;
                option.setAttribute('role', 'option');
                option.textContent = item.label;
                option.addEventListener('mousedown', (event) => {
                    event.preventDefault();
                    selectItem(item);
                });
                list.append(option);
            });

            list.hidden = items.length === 0;
            input.setAttribute('aria-expanded', String(items.length > 0));
        }

        function updateActiveOption(index) {
            const options = [...list.children];
            options.forEach((option, optionIndex) => {
                const active = optionIndex === index;
                option.classList.toggle('is-active', active);
                option.setAttribute('aria-selected', String(active));
            });
            selectedIndex = index;
            if (index >= 0) {
                input.setAttribute('aria-activedescendant', options[index].id);
            } else {
                input.removeAttribute('aria-activedescendant');
            }
        }

        async function loadSuggestions(term) {
            const sequence = ++requestSequence;
            const url = new URL(apiUrl);
            url.searchParams.set('term', term);

            try {
                const response = await fetch(url, {
                    headers: { Accept: 'application/json' }
                });
                if (!response.ok) {
                    throw new Error(`Autocomplete HTTP ${response.status}`);
                }
                const data = await response.json();
                if (sequence === requestSequence) {
                    renderItems(Array.isArray(data) ? data : (data.results ?? []));
                }
            } catch (error) {
                if (sequence === requestSequence) {
                    console.error('Benutzer-Autocomplete fehlgeschlagen:', error);
                    closeList();
                }
            }
        }

        input.addEventListener('input', () => {
            window.clearTimeout(debounceTimer);
            if (hiddenInput) {
                hiddenInput.value = '';
            }
            const term = input.value.trim();
            if (term.length < minimumLength) {
                requestSequence++;
                closeList();
                return;
            }
            debounceTimer = window.setTimeout(() => loadSuggestions(term), 180);
        });

        input.addEventListener('keydown', (event) => {
            const options = [...list.children];
            if (list.hidden || options.length === 0) {
                return;
            }
            if (event.key === 'ArrowDown') {
                event.preventDefault();
                updateActiveOption((selectedIndex + 1) % options.length);
            } else if (event.key === 'ArrowUp') {
                event.preventDefault();
                updateActiveOption(selectedIndex <= 0 ? options.length - 1 : selectedIndex - 1);
            } else if (event.key === 'Enter' && selectedIndex >= 0) {
                event.preventDefault();
                options[selectedIndex].dispatchEvent(new MouseEvent('mousedown', { bubbles: true }));
            } else if (event.key === 'Escape') {
                closeList();
            }
        });

        document.addEventListener('click', (event) => {
            if (event.target !== input && !list.contains(event.target)) {
                closeList();
            }
        });
    }

    function addStyles() {
        const style = document.createElement('style');
        style.textContent = `
            .benutzer-autocomplete-container { position:relative; }
            .benutzer-autocomplete-list { background:#fff; border:1px solid #aaa; list-style:none; margin:2px 0 0; max-height:200px; overflow-y:auto; padding:0; position:absolute; top:100%; left:0; width:100%; z-index:9999; }
            .benutzer-autocomplete-option { cursor:pointer; padding:.35rem .55rem; }
            .benutzer-autocomplete-option.is-active, .benutzer-autocomplete-option:hover { background:#e8f0fe; }
        `;
        document.head.append(style);
    }

    function start() {
        addStyles();
        initBenutzerAutocomplete();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', start, { once: true });
    } else {
        start();
    }
})();
