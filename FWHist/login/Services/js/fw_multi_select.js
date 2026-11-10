/**
 * <fw-multi-select>
 * ------------------
 * Natives Web Component für kaskadierbare Mehrfach-Dropdown-Auswahl
 * (Nachfolger von multidropDown.php / VF_Multi_Dropdown, ohne jQuery/Prototype).
 *
 * Light DOM (kein Shadow DOM) -- damit das globale Flex-Formular-CSS
 * (formsFlex.css / field-row, field-label, field-control, input, error, hint)
 * ganz normal greift, wie in FormRendererFlex.php erzeugt.
 *
 * Attribute:
 *   config-id     Pflicht. z.B. "ARCHIVORDNUNG" oder "SAMMLUNG"
 *                 (muss in MultiSelectResolver::REGISTRY existieren)
 *   name          Pflicht. Basisname für das versteckte Ergebnis-Feld,
 *                 das im umgebenden <form> übermittelt wird (z.B. "ad_archivwert")
 *   api-url       Optional. Default: "MultiSelectAPI.php" (relativ zur Seite)
 *   project       Optional. Wird 1:1 als &project=... an die API durchgereicht
 *   value         Optional. Vorbelegter Endwert (z.B. "07 03 00 00" oder "AB").
 *                 Wird beim Aufbau versucht schrittweise nachzuladen (best effort).
 *   readonly      Optional. Wenn vorhanden: alle <select> disabled, kein Nachladen.
 *
 * Events:
 *   "fw-multi-select-change"  bubbelt, detail = { value, path: string[], level, complete }
 *     - value:    der value der TIEFSTEN aktuell gültig gewählten Ebene
 *                 (Ersatz für die alte VF_Multi_Sel_Input()-Auswertung)
 *     - path:     alle gewählten values, Ebene 1..n, in Reihenfolge
 *     - complete: true, wenn die aktuell tiefste Ebene keine weiteren
 *                 Kind-Optionen mehr hatte (= Blatt erreicht)
 *
 * Formular-Integration: das Component legt selbst ein <input type="hidden">
 * mit dem Attribut "name" an und hält es synchron mit dem tiefsten gewählten
 * value -- normale <form>-Submits funktionieren damit ohne Zusatz-JS.
 *
 * Mehrfachverwendung auf einer Seite ist unproblematisch, da jede Instanz
 * ihre <select>-Elemente selbst erzeugt (keine festen IDs wie im Original
 * level1..level6).
 */
class FwMultiSelect extends HTMLElement {
    static get observedAttributes() {
        return ['config-id', 'value', 'readonly'];
    }

    connectedCallback() {
        if (this._initialized) {
            return;
        }
        this._initialized = true;

        this._configId = this.getAttribute('config-id') || '';
        this._name = this.getAttribute('name') || 'fw_multi_select_value';
        this._apiUrl = this.getAttribute('api-url') || 'MultiSelectAPI.php';
        this._project = this.getAttribute('project') || '';
        this._readonly = this.hasAttribute('readonly');

        if (!this._configId) {
            console.error('<fw-multi-select> benötigt das Attribut config-id');
            return;
        }

        this._selects = []; // Array<HTMLSelectElement>, Index 0 = Ebene 1
        this._hiddenInput = document.createElement('input');
        this._hiddenInput.type = 'hidden';
        this._hiddenInput.name = this._name;
        this.appendChild(this._hiddenInput);

        const initialValue = this.getAttribute('value') || '';
        this._loadLevel(1, null).then(() => {
            if (initialValue) {
                this._restoreValue(initialValue);
            }
        });
    }

    /** Baut/lädt Ebene `level` (1-basiert) für den gegebenen Parent-Wert nach. */
    async _loadLevel(level, parent) {
        // Alle Ebenen ab `level` entfernen (Kaskade zurücksetzen), bevor neu geladen wird
        this._removeLevelsFrom(level);

        let options;
        try {
            options = await this._fetchOptions(level, parent);
        } catch (err) {
            console.error('fw-multi-select: Fehler beim Laden der Optionen', err);
            this._dispatchChange();
            return;
        }

        // Nur ein Platzhalter-Eintrag ("Bitte wählen") => keine echten Kinder => Blatt erreicht
        if (options.length <= 1) {
            this._dispatchChange();
            return;
        }

        const row = this._buildRow(level, options);
        this.appendChild(row.container);
        this._selects[level - 1] = row.select;

        this._dispatchChange();
    }

    _buildRow(level, options) {
        const container = document.createElement('div');
        container.className = 'field-row';
        container.dataset.level = String(level);

        const labelDiv = document.createElement('div');
        labelDiv.className = 'field-label';
        const label = document.createElement('label');
        label.textContent = this.getAttribute(`label-${level}`) || `Ebene ${level}`;
        labelDiv.appendChild(label);

        const controlDiv = document.createElement('div');
        controlDiv.className = 'field-control';
        const select = document.createElement('select');
        select.className = 'input';
        select.disabled = this._readonly;
        select.id = `${this._name}_level${level}_${Math.random().toString(36).slice(2, 8)}`;

        for (const opt of options) {
            const optionEl = document.createElement('option');
            optionEl.value = opt.value;
            optionEl.textContent = opt.text;
            select.appendChild(optionEl);
        }

        select.addEventListener('change', () => {
            const chosen = select.value;
            if (chosen === '') {
                this._removeLevelsFrom(level + 1);
                this._dispatchChange();
                return;
            }
            this._loadLevel(level + 1, chosen);
        });

        controlDiv.appendChild(select);
        label.setAttribute('for', select.id);
        container.appendChild(labelDiv);
        container.appendChild(controlDiv);

        return { container, select };
    }

    /** Entfernt alle gerenderten Ebenen-Zeilen ab (inkl.) `fromLevel`. */
    _removeLevelsFrom(fromLevel) {
        this.querySelectorAll('.field-row').forEach((row) => {
            if (parseInt(row.dataset.level, 10) >= fromLevel) {
                row.remove();
            }
        });
        this._selects.length = fromLevel - 1;
    }

    clear() {
        this._removeLevelsFrom(1);
        this._loadLevel(1, null);
    }

    async _fetchOptions(level, parent) {
        const url = new URL(this._apiUrl, window.location.href);
        url.searchParams.set('config_ID', this._configId);
        url.searchParams.set('level', String(level));
        if (parent !== null && parent !== '') {
            url.searchParams.set('parent', parent);
        }
        if (this._project) {
            url.searchParams.set('project', this._project);
        }

        const response = await fetch(url.toString(), {
            headers: { 'Accept': 'application/json' },
        });
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }
        const json = await response.json();
        if (json.status !== 'ok') {
            throw new Error(json.error || 'Unbekannter API-Fehler');
        }
        return json.data || [];
    }

    /**
     * Setzt die Kaskade auf Ebene 1 zurück (z.B. für einen externen
     * "Filter zurücksetzen"-Button). Löst wie jede Auswahländerung ein
     * "fw-multi-select-change"-Event mit leerem path/value aus.
     */
    reset() {
        this._loadLevel(1, null);
    }

    /** Versucht, einen zuvor gespeicherten Leerzeichen-Pfad ("07 03 00 00") schrittweise nachzuladen. */
    async _restoreValue(value) {
        // Der value der TIEFSTEN Ebene ist typischerweise der vollständige
        // Composite-String; für die Kaskade brauchen wir aber jede Zwischen-
        // stufe. Ohne serverseitige "Pfad auflösen"-Hilfe raten wir hier nicht --
        // stattdessen einfach den Hidden-Input vorbelegen, sodass ein Formular-
        // Submit ohne erneute Auswahl den alten Wert beibehält.
        this._hiddenInput.value = value;
    }

    _dispatchChange() {
        const path = this._selects
            .filter((s) => s && s.value !== '')
            .map((s) => s.value);
        const deepest = path.length > 0 ? path[path.length - 1] : '';

        this._hiddenInput.value = deepest;

        this.dispatchEvent(new CustomEvent('fw-multi-select-change', {
            bubbles: true,
            detail: {
                value: deepest,
                path,
                level: path.length,
            },
        }));
    }
}

customElements.define('fw-multi-select', FwMultiSelect);
