# Tabulator.js im Projekt

## Zweck

Das Projekt verwendet [Tabulator.js](https://tabulator.info/) zur Darstellung mehrerer dynamischer Listen. Die Tabellen werden vollständig im Browser aufgebaut und lokal paginiert und gefiltert.

Die verwendete Version ist Tabulator `6.3.1`.

## Einbindung

Die Tabulator-Dateien werden in [index.php](../index.php) eingebunden:

```html
<link href="https://unpkg.com/tabulator-tables@6.3.1/dist/css/tabulator.min.css" rel="stylesheet" />
<link href="tabulator-custom.css" rel="stylesheet" />
<script src="https://unpkg.com/tabulator-tables@6.3.1/dist/js/tabulator.min.js"></script>
<script src="list-tables.js"></script>
```

Die projektspezifischen Tabulator-Regeln befinden sich in [tabulator-custom.css](../tabulator-custom.css). Die Standard-CSS-Datei von Tabulator wird zuerst geladen, damit die lokalen Regeln sie überschreiben können.

## Listen-Konfiguration

Die verfügbaren Tabellen werden in [index.php](../index.php) im Array `$lists` definiert. Beispiel für die Ortsliste:

```php
[
    'id' => 'ORT',
    'container' => 'ORTS-table',
    'title' => 'Orts-Liste zur Auswahl',
    'editScript' => 'editOrt.php',
    'newLabel' => 'Neuen Ort anlegen',
    'filterSelectFields' => ['st_abkzg'],
    'filterTextFields' => ['name', 'description'],
    'paginationSizes' => [10, 25, 50, 100],
]
```

Bedeutung der wichtigsten Werte:

| Wert | Bedeutung |
| --- | --- |
| `id` | Eindeutige Kennung der Liste und API-Parameter `list_ID` |
| `container` | ID des HTML-Containers für die Tabelle |
| `title` | Überschrift der Liste |
| `editScript` | Ziel der Bearbeiten-Links |
| `filterSelectFields` | Felder mit Auswahlfiltern |
| `filterTextFields` | Felder mit Textfiltern |
| `paginationSizes` | Auswahl der möglichen Seitengrößen |

## Initialisierung

Nach `DOMContentLoaded` prüft [list-tables.js](../list-tables.js), ob `lists` als Array vorhanden ist. Danach wird jede konfigurierte Liste nacheinander initialisiert.

Für jede Liste werden parallel drei JSON-Endpunkte abgefragt:

```text
ListHost.php?list_ID=ORT&action=columns
ListHost.php?list_ID=ORT&action=filters
ListHost.php?list_ID=ORT&action=data
```

Diese Anfragen werden durch [ListHost.php](../ListHost.php) an die passende Listenklasse weitergeleitet:

- `columns`: Spaltendefinitionen der Listenklasse
- `filters`: verfügbare Auswahl- und Textfilter
- `data`: Datensätze der Liste

Anschließend wird die Tabelle mit den Spalten, den Daten und einer zusätzlichen Aktionsspalte erzeugt:

```javascript
const table = new Tabulator(`#${list.container}`, {
    layout: "fitColumns",
    placeholder: "Keine Daten vorhanden",
    columns: fullColumns,
    data: data,
    pagination: "local",
    paginationSize: initialPageSize,
    paginationSizeSelector: list.paginationSizes || [10, 25, 50],
    movableColumns: false
});
```

## Aktionsspalte

`createActionColumn()` fügt jeder Tabelle links beziehungsweise an erster Stelle eine Spalte `Aktion` hinzu. Der Link verweist auf das in `editScript` konfigurierte PHP-Skript und übergibt die ID des Datensatzes:

```text
editOrt.php?id=...
```

## Filterung

Die Filterfelder werden anhand der Antwort von `action=filters` dynamisch erzeugt.

- Auswahlfelder verwenden den Vergleich `=`.
- Texteingaben verwenden den Tabulator-Filtertyp `like`.
- Leere Filterwerte werden ignoriert.
- Der Button `Filter zurücksetzen` leert alle Eingaben und setzt die Filter zurück.

Die Filter werden lokal angewendet:

```javascript
table.setFilter(filters);
```

Nach jeder Filteränderung wird die Anzeige der Datensatzanzahl aktualisiert.

## Pagination

Die Pagination wird beim Erzeugen der Tabelle mit `pagination: "local"` aktiviert. Die anfängliche Seitengröße ist jeweils der erste Wert aus `paginationSizes`, bei der Ortsliste also `10`.

Die Selectbox für die Seitengröße verwendet:

```javascript
table.setPageSize(size);
```

### Pagination ein- und ausschalten

Tabulator 6.3.1 stellt im verwendeten Tabellenobjekt keine funktionierende Methode `setPagination()`, `setOptions()` oder `setOption()` für diesen Anwendungsfall bereit. Deshalb wird die Umschaltung in `setPaginationEnabled()` so umgesetzt:

- Aktiv: gewünschte Seitengröße setzen und den Tabulator-Footer anzeigen.
- Deaktiviert: Seitengröße auf die Anzahl der aktiven Datensätze setzen und den Footer ausblenden.

Dadurch werden bei deaktivierter Pagination alle aktuell sichtbaren Datensätze angezeigt, ohne eine nicht unterstützte Tabulator-Methode aufzurufen.

## Datensatzanzeige

`updateRecordInfo()` ermittelt die Anzahl der aktiven Datensätze mit:

```javascript
table.getDataCount("active");
```

Angezeigt werden die Gesamtzahl und bei einem aktiven Filter zusätzlich die gefilterte Anzahl.

## Druckverhalten

Vor dem Drucken wird die Pagination für alle Tabellen deaktiviert, damit alle aktiven Datensätze in die Druckansicht gelangen. Nach dem Drucken wird sie wieder aktiviert, wenn die Checkbox vorher aktiv war.

Die Drucklogik verwendet ebenfalls `setPaginationEnabled()` und damit ausschließlich die unterstützte Seitengrößen-API.

## Fehlerbehandlung

Fehler bei der Initialisierung einer einzelnen Liste werden in der Browserkonsole protokolliert. Im betroffenen Tabellencontainer wird zusätzlich eine Fehlermeldung angezeigt.

Typische Prüfungen bei Problemen:

1. Browser-Cache mit `Strg+F5` leeren.
2. Prüfen, ob `Tabulator` vor `list-tables.js` geladen wird.
3. Die drei `ListHost.php`-Anfragen im Netzwerk-Tab prüfen.
4. Kontrollieren, ob `list_ID` in [ListHost.php](../ListHost.php) registriert ist.
5. Bei Pagination-Problemen nur `setPageSize()` und `setPaginationEnabled()` verwenden.

## Neue Liste hinzufügen

1. Die Listenklasse mit `getColumns()`, `getData()` und optional `getFilters()` bereitstellen.
2. Die Klasse in [ListHost.php](../ListHost.php) im Array `$listClasses` registrieren.
3. Einen Eintrag in `$lists` in [index.php](../index.php) ergänzen.
4. Für den Container ein passendes `<div>` im Listen-Template vorsehen.
5. Die Seite laden und die drei JSON-Endpunkte einzeln prüfen.
