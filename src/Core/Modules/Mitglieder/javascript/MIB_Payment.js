/**
 * MIB_Payment.js - Payment & Cancel Handler für Mitgliedsbeiträge
 * 
 * Dieses Script behandelt:
 * 1. Pay-Link Click: Zahlung verbuchen via AJAX
 * 2. Cancel-Link Click: Zahlung stornieren (Restore alte Werte)
 * 3. Row Refresh: Nach Update die Tabelle-Zeile aktualisieren
 * 
 * @author Korrektur 2026-02-26
 * @dependency Tabulator.js, Vanilla JavaScript
 */

(function() {
    'use strict';
    
    /**
     * Wartet bis Tabulator-Table ready ist
     * @param {string} tableSelector - CSS Selector für die Tabelle
     * @param {number} maxRetries - Max Versuche zu warten
     * @param {number} delay - Delay zwischen Versuchen in ms
     * @returns {Promise} - Resolved wenn Table ready
     */
    function waitForTable(tableSelector, maxRetries = 20, delay = 250) {
        return new Promise((resolve, reject) => {
            let retries = 0;
            const checkTable = () => {
                const tables = document.querySelectorAll(tableSelector);
                if (tables.length > 0 && window.Tabulator) {
                    resolve(tables[0]);
                } else if (retries < maxRetries) {
                    retries++;
                    setTimeout(checkTable, delay);
                } else {
                    reject(new Error('Table not found or Tabulator not loaded'));
                }
            };
            checkTable();
        });
    }

    /**
     * Pay-Handler: AJAX-Request für Zahlungsverbuchung
     * @param {HTMLElement} element - Der geklickte Link
     */
    async function handlePayClick(element) {
        // Daten aus HTML-Attributen extrahieren (werden von paymentFormatter gesetzt)
        let mi_id = element.getAttribute('data-mi_id');
        let field = element.getAttribute('data-field');
        let year = element.getAttribute('data-year');
        const changed_id = element.getAttribute('data-user_id') || 1; // Fallback User ID

        // Falls Attribute fehlen, versuche sie aus Tabulator-Zelle zu gewinnen
        if ((!mi_id || !field || !year) && window.Tabulator) {
            try {
                const table = Tabulator.findTable(element.closest('.tabulator'))[0];
                const cell = table ? table.getCellFromElement(element) : null;
                if (cell) {
                    const rowData = cell.getRow().getData();
                    mi_id = mi_id || rowData.mi_id;
                    field = field || cell.getField();
                    // berechne year aus field
                    if (!year && field) {
                        if (field.endsWith('_p')) year = new Date().getFullYear() + 1;
                        else year = new Date().getFullYear();
                    }
                }
            } catch (e) {
                console.warn('Fehler beim Ableiten der Attribute aus Tabulator', e);
            }
        }

        // Validierung
        if (!mi_id || !field || !year) {
            console.error('Ungültige Attribute auf Pay-Link:', { mi_id, field, year });
            alert('Fehler: Übergebensdaten unvollständig');
            return;
        }

        // Hinweis: rowElement wird im aktuellen Ablauf nicht benötigt. Fallbacks entfernt,
        // damit Tabulator-Warnungen (Find Error) wegfallen. Die Tabellenaktualisierung erfolgt
        // später über API-Aufrufe.

        // UI Feedback: Link deaktivieren während Request läuft
        const originalText = element.textContent;
        element.textContent = '...';
        element.style.pointerEvents = 'none';
        element.style.opacity = '0.5';

        try {
            console.log('PayClick: Sende AJAX Request', { mi_id, field, year, action: 'pay' });

            const response = await fetch('API/MIB_Payment_API.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    mi_id: parseInt(mi_id),
                    field: field,
                    year: parseInt(year),
                    action: 'pay',
                    changed_id: parseInt(changed_id)
                })
            });

            // Response verarbeiten
            let data;
            try {
                data = await response.json();
            } catch (parseError) {
                console.error('JSON Parse Fehler:', parseError);
                const rawText = await response.text();
                console.error('Raw Response:', rawText);
                throw new Error('API Response ist kein gültiges JSON');
            }

            if (!response.ok) {
                throw new Error(data.message || `HTTP ${response.status}`);
            }

            if (data.success) {
                    console.log('Payment erfolgreich verbucht', data);
                    if (data.updatedRow) {
                        console.log('updatedRow received:', data.updatedRow);
                        console.log('justUpdated flag:', data.updatedRow.justUpdated);
                    }
                    
                    // 1. Zeige Erfolg an (kurzer visueller Feedback im Link selbst)
                    element.textContent = '✓';
                    element.style.color = 'green';
                    element.style.fontWeight = 'bold';
                    element.style.pointerEvents = 'auto';

                    // 2. Toast/Notification
                    showNotification('success', 'Zahlung verbucht');

                    // 3. Reihenweise Aktualisierung: verwende die vom Server gelieferten Felder
                    try {
                        const tables = Tabulator.findTable(element.closest('.tabulator'));
                        if (tables && tables.length > 0) {
                            const tbl = tables[0];
                            const rowObj = tbl.getRow(mi_id);
                            if (rowObj) {
                                if (data.updatedRow && typeof data.updatedRow === 'object') {
                                    // komplette Zeile aktualisieren damit alle Spalten neu formatiert werden
                                    rowObj.update(data.updatedRow);
                                    console.log('Row updated, now recompiling all cells...');
                                    // recompile() triggert alle cell-formatters neu (nicht nur geänderte)
                                    try {
                                        rowObj.reformat();
                                    } catch (e) {
                                        console.warn('reformat() failed:', e);
                                        rowObj.normalizeHeight();
                                    }
                                } else {
                                    // Fallback wenn keine updatedRow geliefert wird
                                    let updateData = {};
                                    updateData[field] = 'paid';
                                    rowObj.update(updateData);
                                    refreshTableRow(tbl, mi_id);
                                }
                            } else {
                                console.warn('Tabulator rowObj für mi_id nicht gefunden, versuche Refresh');
                                refreshTableRow(tbl, mi_id, field, year);
                            }
                        }
                    } catch (e) {
                        console.warn('Fehler beim Aktualisieren der Tabulator-Zeile', e);
                        // Fallback: refreshe mit Tabelle-ID falls bekannt
                        try {
                            const tables = Tabulator.findTable(element.closest('.tabulator'));
                            if (tables && tables.length) refreshTableRow(tables[0], mi_id, field, year);
                        } catch (e2) {
                            console.warn('Fallback row refresh fehlgeschlagen', e2);
                        }
                }

            } else {
                throw new Error(data.message || 'Unbekannter Fehler beim Verbuchen');
            }

        } catch (error) {
            console.error('Payment Fehler:', error);
            alert('Fehler beim Verbuchen: ' + error.message);
            
            // Stelle Original-Zustand wieder her
            element.textContent = originalText;
            element.style.pointerEvents = 'auto';
            element.style.opacity = '1';
        }
    }

    /**
     * Cancel-Handler: AJAX-Request für Stornierung
     * @param {HTMLElement} element - Der geklickte Korrektur-Link
     */
    async function handleCancelClick(element) {
        // Daten aus HTML-Attributen extrahieren
        const mi_id = element.getAttribute('data-mi_id');
        const field = element.getAttribute('data-field');
        const year = element.getAttribute('data-year');
        const changed_id = element.getAttribute('data-user_id') || 1;

        if (!mi_id || !field || !year) {
            console.error('Ungültige Attribute auf Cancel-Link', { mi_id, field, year });
            alert('Fehler: Übergebensdaten unvollständig');
            return;
        }

        // Bestätigung vor Stornierung
        if (!confirm('Möchten Sie diese Zahlung wirklich stornieren?\nDie ursprünglichen Daten werden wiederhergestellt.')) {
            return;
        }

        const rowElement = element.closest('tr') || element.closest('[data-row-index]');

        // UI Feedback
        const originalText = element.textContent;
        element.textContent = '...';
        element.style.pointerEvents = 'none';
        element.style.opacity = '0.5';

        try {
            console.log('CancelClick: Sende AJAX Request', { mi_id, field, year, action: 'cancel' });

            const response = await fetch('API/MIB_Payment_API.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    mi_id: parseInt(mi_id),
                    field: field,
                    year: parseInt(year),
                    action: 'cancel',
                    changed_id: parseInt(changed_id)
                })
            });

            let data;
            try {
                data = await response.json();
            } catch (parseError) {
                console.error('JSON Parse Fehler:', parseError);
                throw new Error('API Response ist kein gültiges JSON');
            }

            if (!response.ok) {
                throw new Error(data.message || `HTTP ${response.status}`);
            }

            if (data.success) {
                    console.log('Payment storniert', data);
                    if (data.updatedRow) {
                        console.log('updatedRow received (cancel):', data.updatedRow);
                        console.log('justUpdated flag (cancel):', data.updatedRow.justUpdated);
                    }
                    
                    // Nutze die vom Server gelieferte aktualisierte Zeile, damit Formatierungen
                    // und evtl. needsCorrection-Flag korrekt erhalten bleiben.
                    try {
                        const tables = Tabulator.findTable(element.closest('.tabulator'));
                        if (tables && tables.length > 0) {
                            const tbl = tables[0];
                            const rowObj = tbl.getRow(mi_id);
                            if (rowObj) {
                                if (data.updatedRow && typeof data.updatedRow === 'object') {
                                    rowObj.update(data.updatedRow);
                                    console.log('Cancel: Row updated, now recompiling all cells...');
                                    try {
                                        rowObj.reformat();
                                    } catch (e) {
                                        console.warn('reformat() failed:', e);
                                        rowObj.normalizeHeight();
                                    }
                                } else {
                                    let updateData = {};
                                    updateData[field] = 'unpaid';
                                    rowObj.update(updateData);
                                    refreshTableRow(tbl, mi_id);
                                }
                            }
                        }
                    } catch (e) {
                        console.warn('Fehler beim Rücksetzen der Tabulator-Zelle', e);
                    }

                    showNotification('success', 'Zahlung storniert - ursprüngliche Daten wiederhergestellt');
                    
                    // kein zusätzlicher Refresh nötig, da normalizeHeight() bereits aufgerufen wurde


            } else {
                throw new Error(data.message || 'Unbekannter Fehler beim Stornieren');
            }

        } catch (error) {
            console.error('Cancel Fehler:', error);
            alert('Fehler beim Stornieren: ' + error.message);
            
            // Stelle Original-Zustand wieder her
            element.textContent = originalText;
            element.style.pointerEvents = 'auto';
            element.style.opacity = '1';
        }
    }

    /**
     * Aktiviert den Korrektur-Link nach erfolgreichem Payment
     * @param {HTMLElement} rowElement - Das TR Element der Zeile
     * @param {string} mi_id - Mitglieds-ID
     * @param {string} field - Feld-Name (M_0, A_0, etc.)
     * @param {string} year - Jahr
     */
    function activateCorrectLink(target, mi_id, field, year) {
        // 'target' kann entweder DOM-Element oder Tabulator Row-Objekt sein
        let rowElement = null;
        if (target && typeof target.getElement === 'function') {
            // Tabulator row object
            rowElement = target.getElement();
        } else {
            rowElement = target;
        }

        if (!rowElement) {
            // versuche via Tabulator API
            try {
                const table = Tabulator.findTable();
                if (table && table.length) {
                    const rowObj = table[0].getRow(mi_id);
                    if (rowObj) rowElement = rowObj.getElement();
                }
            } catch (e) {
                console.warn('Konnte rowElement nicht über API holen:', e);
            }
        }

        if (!rowElement) return;

        // Korrektur-Spalte finden (Tabulator verwendet DIVs statt TDs)
        const cells = rowElement.querySelectorAll('td, .tabulator-cell');
        let correctionCell = null;

        // Versuche zunächst gezielt anhand field-Attribute zu lokalisieren
        cells.forEach(cell => {
            if (cell.getAttribute('data-field') === 'Korrektur') {
                correctionCell = cell;
            }
        });

        // Falls noch nicht gefunden, heuristische Suche nach Text
        if (!correctionCell) {
            for (let i = cells.length - 1; i >= 0; i--) {
                const cell = cells[i];
                if (cell.textContent.includes('Korr')) {
                    correctionCell = cell;
                    break;
                }
            }
        }

        if (correctionCell) {
            // Erstelle Korrektur-Link
            const corrLink = document.createElement('a');
            corrLink.href = '#';
            corrLink.textContent = 'Korr';
            corrLink.className = 'cancel-link';
            corrLink.setAttribute('data-mi_id', mi_id);
            corrLink.setAttribute('data-field', field);
            corrLink.setAttribute('data-year', year);
            corrLink.style.color = 'orange';
            corrLink.title = 'Stornierung durchführen';

            corrLink.addEventListener('click', (e) => {
                e.preventDefault();
                handleCancelClick(corrLink);
            });

            correctionCell.innerHTML = '';
            correctionCell.appendChild(corrLink);
        }
    }

    /**
     * Stellt den Pay-Link (€) wieder her nach Stornierung
     * @param {HTMLElement} rowElement - Das TR Element
     * @param {string} field - Feld-Name
     */
    function restorePayLink(rowElement, field) {
        if (!rowElement) return;

        // Suche nach dem Spalten-Header mit diesem Field (Tabulator verwendet div.tabulator-cell)
        const cells = rowElement.querySelectorAll('td, .tabulator-cell');
        for (const cell of cells) {
            if (cell.getAttribute('data-field') === field || cell.querySelector('[data-field="' + field + '"]')) {
                // Falls der Link bereits vorhanden ist, skip
                if (cell.querySelector('.pay-link')) return;

                // Erstelle neuen Pay-Link
                const payLink = document.createElement('a');
                payLink.href = '#';
                payLink.textContent = '€';
                payLink.className = 'pay-link';
                payLink.style.color = 'blue';
                payLink.style.fontWeight = 'bold';
                payLink.title = 'Zahlung verbuchen';

                payLink.addEventListener('click', (e) => {
                    e.preventDefault();
                    handlePayClick(payLink);
                });

                cell.innerHTML = '';
                cell.appendChild(payLink);
                return;
            }
        }
    }

    /**
     * Aktualisiert eine Zeile in der Tabulator-Tabelle
     * @param {string|object} tableRef - ID der Tabulator-Tabelle oder das Tabulator-Instanzobjekt
     * @param {int} mi_id - Mitglieds-ID zum Suchen der Zeile
     */
    async function refreshTableRow(tableRef, mi_id, field = null, year = null) {
        try {
            let table;
            if (typeof tableRef === 'string') {
                const tables = Tabulator.findTable('#' + tableRef);
                table = Array.isArray(tables) ? tables[0] : tables;
            } else {
                // assume it's already a Tabulator instance
                table = tableRef;
            }

            if (!table) {
                console.warn('Tabulator Table nicht gefunden:', tableRef);
                return;
            }

            // If index is set to mi_id this will work directly
            const rowObj = table.getRow(mi_id);
            if (rowObj) {
                rowObj.normalizeHeight();
                const data = rowObj.getData();
                rowObj.update(data); // Trigger Re-render (cell formatters run)
                // setze Korrektur-Link falls action-Parameter übergeben wurde
                if (field) {
                    activateCorrectLink(rowObj, mi_id, field, year);
                }
            } else {
                console.warn('Kein RowObj gefunden für mi_id:', mi_id);
            }
        } catch (error) {
            console.error('Fehler beim Row Refresh:', error);
        }
    }

    /**
     * Zeige Benachrichtigung an
     * @param {string} type - 'success', 'error', 'warning'
     * @param {string} message - Nachricht
     */
    function showNotification(type, message) {
        // Einfache Variante: console.log
        console.log(`[${type.toUpperCase()}] ${message}`);

        // Optional: Fancy Toast-Notification
        // Wenn Bootstrap/Toastr verfügbar:
        if (window.toastr && window.toastr[type]) {
            window.toastr[type](message);
        }
    }

    /**
     * Event-Delegation Setup - Registriere Handler auf Parent Container
     */
    function setupEventDelegation() {
        // Finde die Tabelle
        const tableContainer = document.querySelector('.tabulator-holder') || 
                              document.querySelector('[data-tabulator-id]') ||
                              document.body;

        // Pay-Link Handler (Event Delegation)
        tableContainer.addEventListener('click', (e) => {
            const payLink = e.target.closest('a.pay-link');
            if (payLink) {
                e.preventDefault();
                handlePayClick(payLink);
            }

            const cancelLink = e.target.closest('a.cancel-link');
            if (cancelLink) {
                e.preventDefault();
                handleCancelClick(cancelLink);
            }
        });

        console.log('Event Delegation Setup abgeschlossen');
    }

    /**
     * Initialisierung beim DOM Ready
     */
    function init() {
        console.log('MIB_Payment.js initialisiert');

        // Warte auf Tabulator zu laden
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', setupEventDelegation);
        } else {
            setupEventDelegation();
        }
    }

    // Auto-Init beim Script-Load
    init();

    // Exportiere Funktionen für externe Nutzung (falls benötigt)
    window.MIB_Payment = {
        handlePayClick: handlePayClick,
        handleCancelClick: handleCancelClick,
        refreshTableRow: refreshTableRow
    };

})();
