/** Native Autocomplete für Mandanten; liefert die Auswahl als ei_id. */
(() => {
    const scriptUrl = document.currentScript?.src;
    const apiUrl = scriptUrl
        ? new URL('../API/MandantenAutoComp_API.php', scriptUrl).href
        : '../API/MandantenAutoComp_API.php';
    const minimumLength = 2;

    function initMandantenAutocomplete(selector) {
        const input = document.querySelector(selector);
        if (!input || input.dataset.autocompleteReady === 'true') return;
        input.dataset.autocompleteReady = 'true';
        input.setAttribute('autocomplete', 'off');
        input.setAttribute('aria-autocomplete', 'list');

        const hiddenInput = document.querySelector(`#${input.id}_id`) || document.querySelector('#mandant_id') || document.querySelector('#mand_id');
        const list = document.createElement('ul');
        list.className = 'mandanten-autocomplete-list';
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
        }
  
        function selectItem(item) {
            const value = item.value || item.label;
            input.value = value;
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
                option.className = 'mandanten-autocomplete-option';
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
            [...list.children].forEach((option, optionIndex) => {
                const active = optionIndex === index;
                option.classList.toggle('is-active', active);
                option.setAttribute('aria-selected', String(active));
            });
            selectedIndex = index;
        }

        async function loadSuggestions(term) {
            const sequence = ++requestSequence;
            const url = new URL(apiUrl);
            url.searchParams.set('term', term);
            try {
                const response = await fetch(url, { headers: { Accept: 'application/json' } });
                if (!response.ok) throw new Error(`Autocomplete HTTP ${response.status}`);
                const data = await response.json();
                if (sequence === requestSequence) renderItems(Array.isArray(data) ? data : (data.results ?? []));
            } catch (error) {
                if (sequence === requestSequence) {
                    console.error('Mandanten-Autocomplete fehlgeschlagen:', error);
                    closeList();
                }
            }
        }

        input.addEventListener('input', () => {
            window.clearTimeout(debounceTimer);
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
            if (list.hidden || options.length === 0) return;
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
            if (event.target !== input && !list.contains(event.target)) closeList();
        });
    }

    function addStyles() {
        const style = document.createElement('style');
        style.textContent = `
            .mandant-autocomplete-toggle { background:#f9d9bd; border:1px solid #c58b61; cursor:pointer; padding:.4rem .7rem; }
            .mandant-autocomplete-panel { max-height:0; opacity:0; overflow:hidden; transition:max-height .25s ease, opacity .2s ease; }
            .mandant-autocomplete-panel.is-open { max-height:12rem; opacity:1; overflow:visible; }
            .mandanten-autocomplete-container { position:relative; }
            .mandanten-autocomplete-list { background:#fff; border:1px solid #aaa; list-style:none; margin:2px 0 0; max-height:200px; overflow-y:auto; padding:0; position:absolute; width:100%; z-index:9999; }
            .mandanten-autocomplete-option { cursor:pointer; padding:.35rem .55rem; }
            .mandanten-autocomplete-option.is-active, .mandanten-autocomplete-option:hover { background:#e8f0fe; }
        `;
        document.head.append(style);
    }

    function start() {
        addStyles();
        const toggle = document.querySelector('.mandant-autocomplete-toggle');
        const panel = document.querySelector('#mandant-autocomplete-panel');
        if (toggle && panel) {
            toggle.addEventListener('click', () => {
                const open = panel.classList.toggle('is-open');
                panel.hidden = !open;
                toggle.setAttribute('aria-expanded', String(open));
                if (open) document.querySelector('#mandant')?.focus();
            });
        }
        document.querySelectorAll('[data-mandant-autocomplete] input[type="text"]').forEach((input) => {
            initMandantenAutocomplete(`#${CSS.escape(input.id)}`);
        });
    }

    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', start, { once: true });
    else start();
})();
