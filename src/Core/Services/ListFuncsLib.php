<?php
/**
 * Listen- Anzeigen mit tabolator.js
 * 
 * Hier sid die Daten für Formular definiert
 */
# var_dump($lTitel);
echo "<input type='hidden' id='list_ID' name='list_ID' value='$list_ID'>";
?>
<div id="control-bar">
<select id="list-selector" title="Liste auswählen">
  <?php 
  foreach ($lTitel as $lId => $titel) {
      echo "<option value='$lId'>$titel</option>";
  }
  
  ?>
</select>

<!--
<input type="text" id="search-input" placeholder="Globale Suche..." title="Suche in der aktuellen Liste" style="flex-grow:1; min-width:200px; padding:5px;" />

<button id="add-new-btn" title="Neuen Datensatz hinzufügen">Neu</button>

<button id="mass-update-btn" title="Massenupdate für ausgewählte Zeilen">Massenupdate</button>
-->

<?php
if (isset($NeuRec) ) {
    echo "<span style='width: 50em;'>$NeuRec</span>";
} else {
    echo "<span style='width: 50em;'></span>";
}
?>
    
    <select id="settings-selector" title="Einstellungen">
        <option value="pagination_on">Pagination an</option>
        <option value="pagination_off">Pagination aus</option>
        <option value="debug_on">Debug an</option>
        <option value="debug_off">Debug aus</option>
        <option value="csv_export">CSV Export</option>
        <option value="print">Drucken</option>
    </select>
    
    
 <div class="hints" id="hints">
  <button class="hints__trigger"
          type="button"
          aria-haspopup="dialog"
          aria-expanded="false"
          aria-controls="hints-panel"
          title="Wähle hier die gewünschten Hinweise aus.">
    <span class="hints__label">Hinweise</span>
    <span class="hints__icon" aria-hidden="true">ℹ️</span>
  </button>

  <div class="hints__panel" id="hints-panel" role="dialog" aria-label="Hinweise zur Tabelle">
    <ul class="hints__list">
      <!-- PHP Block beibehalten -->

      <li class="hints__divider" aria-hidden="true"></li>

      <li class="tip">
        <div class="tip__title">Nach Spalteninhalt sortieren <small>(Tabulator.js)</small></div>
        <div class="tip__body">
          <p>So sortierst du die Tabelle nach dem Inhalt einer Spalte:</p>
          <ol>
            <li>Auf den <b>Spaltentitel</b> klicken.</li>
            <li>Erneut klicken, um zwischen <b>aufsteigend</b> und <b>absteigend</b> zu wechseln.</li>
          </ol>
          <p class="tip__note">
            Der Sortierstatus wird im Header durch ein Sortier-Icon/Pfeil dargestellt (je nach Tabulator-Theme/Config).
            Mehrfachsortierung ist möglich, wenn sie in Tabulator aktiviert ist (z. B. per <code>sortMode</code> bzw. Multi-Column Sorting).
          </p>
        </div>
      </li>

      <li class="tip">
        <div class="tip__title">Anzeige von Spalten unterdrücken <small>(Tabulator.js)</small></div>
        <div class="tip__body">
          <p>Spalten können über ein Spalten-Menü ein-/ausgeblendet werden:</p>
          <ol>
            <li>Das <b>Spaltenmenü</b> öffnen (je nach Umsetzung: Rechtsklick auf Header oder Menü-Icon im Header).</li>
            <li><b>Spalte ausblenden</b> auswählen.</li>
          </ol>
          <p class="tip__note">
            Zum Wiederanzeigen nutzt du das gleiche Menü oder eine separate UI (z. B. „Alle Spalten anzeigen“),
            die intern <code>column.show()</code> / <code>column.hide()</code> verwendet.
          </p>
        </div>
      </li>

      <li class="tip">
        <div class="tip__title">Pagination <small>(Tabulator.js)</small></div>
        <div class="tip__body">
          <p>Wenn Pagination aktiviert ist, wird die Tabelle in Seiten aufgeteilt:</p>
          <ul>
            <li>Zwischen Seiten über die <b>Paginierungs-Steuerung</b> wechseln (z. B. „Vor/Zurück“, Seitenzahlen).</li>
            <li>Optional kann die <b>Seitengröße</b> (Anzahl Zeilen pro Seite) über eine Auswahl geändert werden – abhängig von deiner Tabulator-Konfiguration.</li>
          </ul>
          
        </div>
      </li>
    </ul>
  </div>
</div>
</div>


<?php 
# var_dump($_SESSION);

if (isset($_SESSION['BS_Prim']['BE'])) {
    echo "<input type='hidden' id='current_user_id' value=".$_SESSION['BS_Prim']['BE']['be_id']." />";
}

echo "<div id='" .$list_ID. "-table'></div>  ";    

echo "<script type='text/javascript' src='" . $path2ROOT . "public/js/tabulator/tabulator.min.js' ></script>";
echo "<script type='text/javascript' src='" . $path2ROOT . "public/js//flatpickr/flatpickr.min.js' ></script>";

?>
<script>
document.addEventListener("DOMContentLoaded", function() {
    console.log("Script gestartet");
    
    // helper to decide whether a correction link should be shown for a row
    function rowNeedsCorrection(rowData) {
        // only show correction link immediately after a server update
        return rowData && !!rowData.justUpdated;
    }

    // 4 Formatter zusammen registrieren
    Tabulator.extendModule("format", "formatters", {
        yearFormatter: function(cell) {
            const value = cell.getValue();
            if (!value) return "";
            const currentYear = new Date().getFullYear();
            let year = parseInt(value, 10);
            if (isNaN(year)) {
                const date = new Date(value);
                if (!isNaN(date)) {
                    year = date.getFullYear();
                } else {
                    return value;
                }
            }
            const color = (year >= currentYear) ? "green" : "red";
            return `<span style="color:${color}; font-weight:bold;">${value}</span>`;
        },
        korrekturFormatter: function(cell) {
            const rowData = cell.getRow().getData();
            console.log('korrekturFormatter called for row:', rowData.mi_id, 'justUpdated:', rowData.justUpdated);
            if (rowNeedsCorrection(rowData)) {
                const mi_id = rowData.mi_id || '';
                
                // Use the field from API if available, otherwise infer it
                let field = rowData.lastPaymentField || 'MA_p';  // default to MA_p if not set
                
                let year = new Date().getFullYear();
                if (field.endsWith('_p')) {
                    year = year + 1;
                }
                
                const attrs = `data-mi_id="${mi_id}" data-field="${field}" data-year="${year}"`;
                // once we've shown a link for a justUpdated row, clear the marker so it doesn't persist
                if (rowData.justUpdated) {
                    rowData.justUpdated = false;
                }
                console.log('Rendering Korrektur link for row:', mi_id, 'field:', field);
                return `<a href="#" ${attrs} class="cancel-link" style="color:blue; text-decoration:underline;">Korrigieren</a>`;
            }
            return "";
        },



        paymentFormatter: function(cell) {
            const value = cell.getValue();
            const rowData = cell.getRow().getData();
            const field = cell.getField();
            // determine year from field suffix (_0 = current, _p = next)
            let year = new Date().getFullYear();
            if (field.endsWith('_p')) {
                year = year + 1;
            }
            const mi_id = rowData.mi_id || '';
            const attrs = `data-mi_id="${mi_id}" data-field="${field}" data-year="${year}"`;
            if (value === "paid") {
                return "<b style='color:green; font-size:130%;'>&checkmark;</b>";
            }
            if (value === "unpaid") {
                return `<a href="#" ${attrs} style="color:blue; font-weight:bold;" title="Bezahlen" class="pay-link">&euro;</a>`;
            }
            if (value === "notpaid") {
                return `<a href="#" ${attrs} style="color:red; font-weight:bold;" title="Unbezahlt" class="pay-link">&#10060;</a>`;
            }
            return value || "";
        }
    });
    
    const currentUserId = document.getElementById('current_user_id')?.value || null;
    const listName = document.getElementById('list_ID')?.value || null;
    const search = document.getElementById('srch_Id')?.value || "";
    const selectElement = document.getElementById('list-selector');

    if (!listName) {
        console.error("Element #list_ID nicht gefunden");
        return;
    }

    const apiUrl = "API/" + listName + "_Liste_API.php";
    console.log('apiUrl ', apiUrl);
    
    if (!selectElement) {
        console.error("Element #list-selector nicht gefunden");
        return;
    }
    let selectedList = selectElement.value;
    console.log("Ausgewählte Liste:", selectedList);

    // Tabulator initialisieren
    const table = new Tabulator("#" + listName + "-table", {
        // use mi_id as row index when present; this allows getRow(mi_id) to work reliably
        index: "mi_id",
        ajaxRequestFunc: function(url, config, params) {
            const query = new URLSearchParams(params).toString();
            const fullUrl = url + "?" + query;

            return fetch(fullUrl, {
                method: "GET",
                headers: { "Accept": "application/json" }
            })
            .then(response => response.text())
            .then(text => {
                console.log("Raw response text:", text);
                try {
                    const json = JSON.parse(text);
                    console.log("Parsed JSON:", json);                
                    return json;
                } catch (e) {
                    alert("Fehler beim Parsen der Serverantwort. Siehe Konsole.");
                    console.error("JSON parse error:", e);
                    return Promise.reject(e);
                }
            });
        },
        ajaxParams: {
            T_List: selectedList,
            search: search
        },
        ajaxConfig: "GET",
        ajaxResponse: function(url, params, response) {
            if (response.columns && response.columns.length > 0) {
                // Spalten modifizieren: für 'korrektur' Feld Link-Formatter und Klick-Handler setzen
                const modifiedColumns = response.columns.map(col => {
                    if (col.field === "korrektur") {
                        col.formatter = "korrekturFormatter";
                        col.cellClick = function(e, cell) {
                            e.preventDefault();
                            const rowData = cell.getRow().getData();
                            console.log("Korrektur-Link geklickt für mi_id:", rowData.mi_id);
                            // Hier eigene Korrektur-Logik einfügen, z.B. API-Aufruf
                            alert("Korrektur für mi_id: " + rowData.mi_id);
                        };
                    }
                    if (col.field === "jahr" || col.field === "year") {
                        col.formatter = "yearFormatter";
                    }
                    if (col.field === "payment" || col.field === "status") {
                        col.formatter = "paymentFormatter";
                    }
                    return col;
                });
                table.setColumns(modifiedColumns);
            } else {
                console.warn("Keine Spalten im Response enthalten!");
            }
            return response.data || [];
        },
        layout: "fitColumns",
        responsiveLayout: "collapse",
        rowHeader: {
            formatter: "responsiveCollapse",
            width: 30,
            minWidth: 10,
            hozAlign: "center",
            resizable: false
        },
        pagination: "local",
        paginationSize: 20,
        paginationSizeSelector: [10, 15, 20, 50, 100],
        printAsHtml:true, //enable html table printing
        printStyled:true, //copy Tabulator styling to HTML table
        printRowRange:"all", //change default selector to alle
        placeholder: "Keine Daten gefunden",
        columns: [],
    });

    // Daten laden nach Tabellenaufbau
    table.on("tableBuilt", function() {
        table.setData(apiUrl, { T_List: selectedList, search: search })
            .then(() => console.log("Initiale Daten geladen"))
            .catch(err => console.error("Fehler beim initialen Laden:", err));
    });

    // Event Listener für Listenauswahl
    selectElement.addEventListener("change", function() {
        selectedList = this.value;
        console.log('event List selected:', selectedList);
        table.setData(apiUrl, { T_List: selectedList, search: search })
            .then(() => console.log("Daten nach Listenwechsel geladen"))
            .catch(err => console.error("Fehler beim Laden nach Listenwechsel:", err));
    });
    
    /*
    // Globale Suche
    document.getElementById("search-input").addEventListener("keyup", function() {
        var query = this.value.trim();
        console.log('query ',query);
        if(query === "") {
            table.clearFilter(true);
        } else {
            var filters = columns.map(col => ({
                field: col.field,
                type: "like",
                value: query
            }));
            table.setFilter(filters, 'or');
        }
    });
*/
/*
    // Neue Daten hinzufügen (leere Zeile oben)
    document.getElementById("add-new-btn").addEventListener("click", function() {
        table.addRow({}, true);
    });
*/
    // Einstellungen
    document.getElementById("settings-selector").addEventListener("change", function() {
        var val = this.value;
        switch(val) {
            case "pagination_on":
                table.setPageSize(10);
                break;
            case "pagination_off":
                table.setPageSize(1000000);
                break;
            case "debug_on":
                console.log("Debug an");
                break;
            case "debug_off":
                console.log("Debug aus");
                break;
            case "csv_export":
                table.download("csv", "export.csv");
                break;
            case "print":
                table.print(false, true);
                break;
        }
    });

    
table.on("cellEdited", function(cell){
    var rowData = cell.getRow().getData();
    var field = cell.getField();
    var value = cell.getValue();

    fetch("common/API/" + listName + "_Payment_API.php", {
        method: "POST",
        headers: {"Content-Type": "application/json"},
        body: JSON.stringify({
            mi_id: rowData.mi_id,
            field: field,
            value: value
        })
    })
    .then(response => response.json())
    .then(data => {
        if(data.success){
            console.log("Update successful");
            // Optionally refresh the row data from server to reflect any changes
            cell.getRow().update(data.updatedRow);
        } else {
            alert("Error updating: " + data.message);
            cell.restoreOldValue();
        }
    })
    .catch(() => {
        alert("Network error");
        cell.restoreOldValue();
    });
});
   
    
/*
    // Massenupdate-Button Event-Handler
    document.getElementById("mass-update-btn").addEventListener("click", function(){
        var selectedRows = table.getSelectedData();
        if(selectedRows.length === 0){
            alert("Bitte mindestens eine Zeile auswählen.");
            return;
        }

        var ids = selectedRows.map(row => row.mi_id);

        var field = prompt("Welches Feld soll geändert werden? (z.B. mi_mtyp)");
        if(!field) return;

        var value = prompt("Neuer Wert für " + field + ":");
        if(value === null) return;

        fetch("mass_update.php", {
            method: "POST",
            headers: {"Content-Type": "application/json"},
            body: JSON.stringify({
                ids: ids,
                field: field,
                value: value
            })
        })
        .then(res => res.json())
        .then(data => {
            if(data.success){
                alert("Massenupdate erfolgreich");
                table.replaceData(); // Tabelle neu laden
            } else {
                alert("Fehler: " + data.message);
            }
        })
        .catch(() => {
            alert("Netzwerkfehler beim Massenupdate");
        });
    });
    */

    // end of DOMContentLoaded listener
    });

</script>

<script>
  // Optional: Klick-zu-öffnen (besser auf Touch-Geräten). Hover bleibt zusätzlich aktiv.
  (function(){
    const root = document.getElementById('hints');
    const btn  = root.querySelector('.hints__trigger');
    const panelId = btn.getAttribute('aria-controls');

    function setOpen(v){
      root.dataset.open = String(v);
      btn.setAttribute('aria-expanded', String(v));
    }

    btn.addEventListener('click', (e) => {
      e.preventDefault();
      const isOpen = root.dataset.open === "true";
      setOpen(!isOpen);
    });

    document.addEventListener('click', (e) => {
      if (!root.contains(e.target)) setOpen(false);
    });

    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape') setOpen(false);
    });
  })();
</script>

