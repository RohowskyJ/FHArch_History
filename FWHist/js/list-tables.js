// list-tables.js
document.addEventListener("DOMContentLoaded", function () {
    const listConfigs = Array.isArray(globalThis.lists)
        ? globalThis.lists
        : (typeof lists !== 'undefined' && Array.isArray(lists) ? lists : null);

    if (!listConfigs) {
        console.error("Variable 'lists' ist nicht definiert oder kein Array.");
        return;
    }

    const tableRegistry = {};

    initAllLists();

    async function initAllLists() {
        for (const list of listConfigs) {
            try {
                await initList(list);
            } catch (err) {
                console.error("Fehler bei Liste", list.id, err);
                const el = document.getElementById(list.container);
                if (el) {
                    const message = err.code === "TABLE_NOT_FOUND"
                        ? err.message
                        : 'Fehler beim Laden der Liste ' + list.id + '.';
                    const createLink = err.code === "TABLE_NOT_FOUND" && list.editScript
                        ? '<a class="btn" href="' + escapeAttr(list.editScript) + '">' +
                            escapeHtml(list.newLabel || 'Neuen Datensatz anlegen') + '</a>'
                        : '';
                    el.innerHTML = '<div class="error-box">' + escapeHtml(message) + '</div>' + createLink;
                }
            }
        }
    }

    async function initList(list) {
        const root = document.getElementById(`root-${list.id}`);
        const tableEl = document.getElementById(list.container);

        if (!root || !tableEl) {
            throw new Error(`Container für ${list.id} nicht gefunden`);
        }

        const apiUrl = list.apiUrl || 'ListHostAPI.php';
        const apiSeparator = apiUrl.includes('?') ? '&' : '?';
        const project = globalThis.globalParams?.project ?? null;
		/* Löschkandidat fw_id */
        const fwId = globalThis.globalParams?.fw_id ?? null;
		const selId = globalThis.globalParams?.sel_id ?? null;
		const editProtect = globalThis.globalParams?.edit_Protect ?? false;
        const projectQuery = project
            ? `&project=${encodeURIComponent(project)}`
            : '';
	    /* Löschkandidat fwIDQuer */
        const fwIdQuery = (fwId !== null && fwId !== '' && fwId !== undefined)
            ? `&fw_id=${encodeURIComponent(fwId)}`
            : '';
		const selIdQuery = (selId !== null && selId !== '' && selId !== undefined)
			           ? `&sel_id=${encodeURIComponent(selId)}`
			           : ''; 
	    const editProtectQuery = (editProtect !== null && editProtect !== '' && editProtect !== undefined)	
		               ? `&edit_Protect=${encodeURIComponent(editProtect)}`		
					   : false;   
        const apiQuery = `${apiSeparator}list_ID=${encodeURIComponent(list.id)}${projectQuery}${selIdQuery}${editProtectQuery}`;
        const [columns, filtersMeta, data] = await Promise.all([
            fetchJson(`${apiUrl}${apiQuery}&action=columns`),
            fetchJson(`${apiUrl}${apiQuery}&action=filters`),
            fetchJson(`${apiUrl}${apiQuery}&action=data`)
        ]);

        const statusBlock = createStatusBlock(list, filtersMeta, data);
        root.insertBefore(statusBlock, tableEl);

        const resolvedColumns = resolveColumnFormatters(columns);
        const fullColumns = list.showActionColumn === false
            ? resolvedColumns
            : [createActionColumn(list), ...resolvedColumns];

        const initialPageSize = Array.isArray(list.paginationSizes) && list.paginationSizes.length ? list.paginationSizes[0] : 10;

        const table = new Tabulator(`#${list.container}`, {
            layout: "fitColumns",
            placeholder: "Keine Daten vorhanden",
            columns: fullColumns,
            data: data,
            index: list.indexField || "id",
            pagination: "local",
            paginationSize: initialPageSize,
            paginationSizeSelector: list.paginationSizes || [10, 25, 50],
            movableColumns: false
        });

        tableRegistry[list.id] = {
            table,
            list,
            filtersMeta,
            root,
            tableEl,
            originalCount: data.length,
            multiSelectPath: []
        };

        wireFilterEvents(list.id);
        wireMultiSelectFilter(list.id);
        applyFilters(list.id);
    }

    /**
     * Zerlegt einen Composite-Wert aus <fw-multi-select> ("07.01 01 00")
     * in seine Einzelfelder ["07","01","01","00"], analog zu
     * ArchivordnungMultiSelect::parseCompositeValue() auf PHP-Seite:
     * erstes Token wird an "." gesplittet (Ebene 1+2), alle weiteren
     * Tokens sind an Leerzeichen getrennt (Ebene 3-6).
     */
    function parseMultiSelectComposite(value) {
        if (!value) return [];
        const tokens = String(value).trim().split(' ').filter(Boolean);
        if (tokens.length === 0) return [];
        const first = tokens.shift();
        return first.split('.').concat(tokens);
    }

    /**
     * Bindet ein <fw-multi-select>-Widget (z.B. Archivordnung) als
     * zusätzliche Filterquelle an eine Liste. Das Widget liegt i.d.R.
     * AUSSERHALB des Listen-Root-Containers (siehe ArchivList.php), daher
     * wird global per document.querySelector gesucht, nicht per list.root.
     */
    function wireMultiSelectFilter(listId) {
        const registry = tableRegistry[listId];
        if (!registry) return;

        const cfg = registry.list.multiSelectFilter;
        registry.multiSelectFilters = [];
        if (!cfg || !cfg.selector || !Array.isArray(cfg.pathFields)) {
            return;
        }

        const widget = document.querySelector(cfg.selector);
        if (!widget) {
            console.warn(`fw-multi-select für Liste ${listId} nicht gefunden (${cfg.selector})`);
            return;
        }

        registry.multiSelectWidget = widget;

        widget.addEventListener('fw-multi-select-change', (e) => {
            const parts = parseMultiSelectComposite(e.detail && e.detail.value);
            registry.multiSelectFilters = cfg.pathFields
                .map((field, i) => (i < parts.length ? { field, type: "=", value: parts[i] } : null))
                .filter(Boolean);
            applyFilters(listId);
        });
    }

    function createStatusBlock(list, filtersMeta, data) {
        const block = document.createElement("div");
        block.className = "status-block";
        const originalCount = data.length;
        const newRecordAction = list.editScript
            ? `<a class="btn" href="${escapeAttr(list.editScript)}">${escapeHtml(list.newLabel || 'Neu anlegen')}</a>`
            : '';
        block.innerHTML = `
            <div class="status-top">
                <div class="status-actions">
                    ${newRecordAction}
                    <button class="btn secondary js-reset-filters" type="button" data-list-id="${escapeAttr(list.id)}">Filter zurücksetzen</button>
                </div>
                <div class="record-info" id="record-info-${escapeAttr(list.id)}">Datensätze: ${originalCount}</div>
            </div>
            <div class="status-filters" id="filters-${escapeAttr(list.id)}"></div>
        `;

        // Pagination Controls: Checkbox und Select
        const paginationControls = document.createElement("div");
        paginationControls.className = "pagination-controls";
        paginationControls.style.marginTop = "10px";
        paginationControls.innerHTML = `
            <label style="margin-right:10px;">
                <input type="checkbox" class="js-toggle-pagination" data-list-id="${escapeAttr(list.id)}" checked />
                Pagination aktiv
            </label>
            <label>
                Zeilen pro Seite:
                <select class="js-pagination-size" data-list-id="${escapeAttr(list.id)}">
                    ${list.paginationSizes.map(size => `<option value="${size}">${size}</option>`).join('')}
                </select>
            </label>
        `;
        block.appendChild(paginationControls);

        const filtersWrap = block.querySelector(`#filters-${CSS.escape(list.id)}`);

        if (list.dateFilter) {
            const { startField, endField } = list.dateFilter;
            const years = new Set();
            data.forEach((row) => {
                [startField, endField].forEach((field) => {
                    const year = getEventDateYear(row[field]);
                    if (year) years.add(year);
                });
            });

            const group = document.createElement("div");
            group.className = "filter-group";
            const label = document.createElement("label");
            label.setAttribute("for", `${list.id}-filter-event-date`);
            label.textContent = list.dateFilter.label || "Veranstaltungsdatum";

            const select = document.createElement("select");
            select.id = `${list.id}-filter-event-date`;
            select.className = "js-date-filter";
            select.dataset.listId = list.id;
            select.innerHTML = `
                <option value="future" selected>Zukünftige Termine</option>
                <option value="">Alle</option>
            `;
            [...years].sort().forEach((year) => {
                const option = document.createElement("option");
                option.value = year;
                option.textContent = year;
                select.appendChild(option);
            });

            group.appendChild(label);
            group.appendChild(select);
            filtersWrap.appendChild(group);
        }

        if (filtersMeta && filtersMeta.selectFilters) {
            Object.entries(filtersMeta.selectFilters).forEach(([field, cfg]) => {
                if (!Array.isArray(list.filterSelectFields) || !list.filterSelectFields.includes(field)) {
                    return;
                }

                const group = document.createElement("div");
                group.className = "filter-group";
                const label = document.createElement("label");
                label.setAttribute("for", `${list.id}-filter-${field}`);
                label.textContent = cfg.label || field;

                const select = document.createElement("select");
                select.id = `${list.id}-filter-${field}`;
                select.className = "js-filter";
                select.dataset.listId = list.id;
                select.dataset.field = field;
                select.dataset.filterType = "select";

                const defaultOption = document.createElement("option");
                defaultOption.value = "";
                defaultOption.textContent = "Alle";
                select.appendChild(defaultOption);

                const options = cfg.options || {};
                Object.entries(options).forEach(([value, text]) => {
                    const opt = document.createElement("option");
                    opt.value = value;
                    opt.textContent = text;
                    select.appendChild(opt);
                });

                group.appendChild(label);
                group.appendChild(select);
                filtersWrap.appendChild(group);
            });
        }

        if (filtersMeta && Array.isArray(filtersMeta.textFilters)) {
            filtersMeta.textFilters.forEach((filter) => {
                const field = typeof filter === "string" ? filter : filter?.field;
                if (!Array.isArray(list.filterTextFields) || !list.filterTextFields.includes(field)) {
                    return;
                }

                const group = document.createElement("div");
                group.className = "filter-group";

                const label = document.createElement("label");
                label.setAttribute("for", `${list.id}-filter-${field}`);
                const labelText = filtersMeta.textFilterLabels?.[field] ||
                    (typeof filter === "object" && filter?.label);
                label.textContent = labelText || buildPrettyFieldLabel(field);

                const input = document.createElement("input");
                input.type = "text";
                input.id = `${list.id}-filter-${field}`;
                input.className = "js-filter";
                input.dataset.listId = list.id;
                input.dataset.field = field;
                input.dataset.filterType = "text";
                input.placeholder = "Filtern ...";

                group.appendChild(label);
                group.appendChild(input);
                filtersWrap.appendChild(group);
            });
        }

        return block;
    }

    function resolveColumnFormatters(columns) {
        const formatters = globalThis.listTableFormatters || {};
        return columns.map((column) => {
            const resolved = { ...column };
            if (Array.isArray(column.columns)) {
                resolved.columns = resolveColumnFormatters(column.columns);
            }
            if (typeof column.formatter === "string" && typeof formatters[column.formatter] === "function") {
                resolved.formatter = formatters[column.formatter];
            }
            return resolved;
        });
    }

    function createActionColumn(list) {
        return {
            title: "Aktion",
            field: "_action",
            width: 90,
            hozAlign: "center",
            headerSort: false,
            headerFilter: false,
            formatter: function(cell) {
                const rowData = cell.getRow().getData();
                if (typeof rowData.action === 'string' && rowData.action !== '') {
                    return rowData.action;
                }
                const rowId = rowData.fw_id ?? rowData.id ?? '';
                const actionUrl = new URL(list.actionScript || list.editScript, window.location.href);
                actionUrl.searchParams.set('ID', rowId);
                const href = escapeAttr(actionUrl.toString());
                return `<a class="action-link" href="${href}">${escapeHtml(list.actionLabel || 'Bearbeiten')}</a>`;
            }
        };
    }

    function wireFilterEvents(listId) {
        const registry = tableRegistry[listId];
        if (!registry) return;

        const root = registry.root;
        const table = registry.table;

        // Filter-Events
        root.querySelectorAll(".js-filter").forEach((input) => {
            const evtName = input.tagName === "SELECT" ? "change" : "input";
            input.addEventListener(evtName, () => applyFilters(listId));
        });
        root.querySelector(".js-date-filter")?.addEventListener("change", () => applyFilters(listId));

        const multiSelectConfig = registry.list.multiSelectFilter;
        if (multiSelectConfig?.selector) {
            const multiSelect = document.querySelector(multiSelectConfig.selector);
            multiSelect?.addEventListener("fw-multi-select-change", (event) => {
                registry.multiSelectPath = Array.isArray(event.detail?.path)
                    ? event.detail.path
                    : [];
                applyFilters(listId);
            });
        }

        // Reset-Button
        root.querySelector(".js-reset-filters")?.addEventListener("click", function() {
            root.querySelectorAll(".js-filter").forEach((el) => {
                el.value = "";
            });
            const dateFilter = root.querySelector(".js-date-filter");
            if (dateFilter) dateFilter.value = "future";
            const multiSelectConfig = registry.list.multiSelectFilter;
            const multiSelect = multiSelectConfig?.selector
                ? document.querySelector(multiSelectConfig.selector)
                : null;
            registry.multiSelectPath = [];
            multiSelect?.clear?.();
            applyFilters(listId);
        });

        // Pagination ein-/ausschalten
        const togglePagination = root.querySelector(".js-toggle-pagination");
        const sizeSelect = root.querySelector(".js-pagination-size");

        if (togglePagination) {
            togglePagination.addEventListener("change", (e) => {
                const enabled = e.target.checked;
                setPaginationEnabled(listId, enabled);
                updateRecordInfo(listId);
            });
        }

        // Zeilen pro Seite ändern
        sizeSelect?.addEventListener("change", (e) => {
            const size = parseInt(e.target.value, 10);
            if (togglePagination && togglePagination.checked) {
                table.setPageSize(size);
            }
        });
    }

    function setPaginationEnabled(listId, enabled) {
        const registry = tableRegistry[listId];
        if (!registry) return;

        const { table, root, tableEl } = registry;
        const togglePagination = root.querySelector(".js-toggle-pagination");
        const sizeSelect = root.querySelector(".js-pagination-size");
        const footer = tableEl.querySelector(".tabulator-footer");

        if (enabled) {
            const size = parseInt(sizeSelect?.value, 10) || 10;
            table.setPageSize(size);
            if (footer) footer.hidden = false;
        } else {
            const activeCount = table.getDataCount("active");
            table.setPageSize(Math.max(activeCount, 1));
            if (footer) footer.hidden = true;
        }

        if (togglePagination) togglePagination.checked = enabled;
    }

    function applyFilters(listId) {
        const registry = tableRegistry[listId];
        if (!registry) return;

        const { table, root } = registry;
        const filterInputs = root.querySelectorAll(".js-filter");
        const filters = [...(registry.multiSelectFilters || [])];

        filterInputs.forEach((input) => {
            const value = (input.value || "").trim();
            if (!value) return;

            const field = input.dataset.field;
            const type = input.dataset.filterType;

            if (type === "select") {
                filters.push({
                    field,
                    type: registry.list.selectFilterOperators?.[field] || "=",
                    value
                });
            } else {
                filters.push({
                    field,
                    type: "like",
                    value
                });
            }
        });

        const multiSelectConfig = registry.list.multiSelectFilter;
        const path = registry.multiSelectPath;
        if (multiSelectConfig && path.length > 0) {
            const selectedValue = path[path.length - 1];
            const selectedParts = selectedValue.split(/[.\s]+/).filter(Boolean);
            const pathFields = multiSelectConfig.pathFields || [];

            selectedParts.slice(0, pathFields.length).forEach((value, index) => {
                filters.push({
                    field: pathFields[index],
                    type: "=",
                    value
                });
            });
        }

        table.setFilter(filters);
        const dateFilterValue = root.querySelector(".js-date-filter")?.value;
        if (dateFilterValue) {
            const dateFilter = registry.list.dateFilter;
            table.addFilter((row) => {
                const startDate = getEventDateValue(row[dateFilter.startField]);
                const endDate = getEventDateValue(row[dateFilter.endField]);
                if (dateFilterValue === "future") {
                    return (startDate !== null && startDate >= dateFilter.today)
                        || (endDate !== null && endDate >= dateFilter.today);
                }
                return startDate?.startsWith(dateFilterValue) === true
                    || endDate?.startsWith(dateFilterValue) === true;
            });
        }
        updateRecordInfo(listId);
    }

    function getEventDateValue(value) {
        const date = String(value ?? "").trim();
        return /^\d{4}-\d{2}-\d{2}$/.test(date) && !date.startsWith("0000-")
            ? date
            : null;
    }

    function getEventDateYear(value) {
        return getEventDateValue(value)?.slice(0, 4) ?? null;
    }

    function updateRecordInfo(listId) {
        const registry = tableRegistry[listId];
        if (!registry) return;

        const { table, originalCount, root } = registry;
        const filteredCount = table.getDataCount("active");
        const infoEl = root.querySelector(`#record-info-${CSS.escape(listId)}`);

        let text = `Datensätze gesamt: ${originalCount}`;
        if (filteredCount !== originalCount) {
            text += ` | gefiltert sichtbar: ${filteredCount}`;
        }

        if (infoEl) infoEl.textContent = text;
    }

    async function fetchJson(url) {
        const response = await fetch(url, {
            headers: {
                "X-Requested-With": "XMLHttpRequest"
            }
        });

        if (!response.ok) {
            const payload = await response.json();
            const error = new Error(payload.error || `HTTP ${response.status} bei ${url}`);
            error.code = payload.code;
            throw error;
        }

        return await response.json();
    }

    function buildPrettyFieldLabel(field) {
        const map = {
            name: "Name",
            description: "Beschreibung",
            fw_name: "Feuerwehr Wappen",
            badge_name: "Abzeichen",
            st_abkzg: "Staat/Land",
            status: "Status",
            fw_st_name: "Land / Region"
        };
        return map[field] || field;
    }

    function escapeHtml(str) {
        return String(str)
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    function escapeAttr(str) {
        return escapeHtml(str);
    }

    // Druck-Handling: Pagination vor dem Drucken aus, danach wieder an
    window.addEventListener("beforeprint", () => {
        Object.keys(tableRegistry).forEach((listId) => {
            setPaginationEnabled(listId, false);
        });
    });

    window.addEventListener("afterprint", () => {
        Object.entries(tableRegistry).forEach(([listId, {root}]) => {
            const checkbox = root.querySelector(".js-toggle-pagination");

            if (checkbox && checkbox.checked) {
                setPaginationEnabled(listId, true);
            }
        });
    });
});