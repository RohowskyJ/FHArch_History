# Generierung der PSA-Listen mit Tabulator

Dieses Dokument beschreibt, wie die drei Listen auf der Feuerwehr-/Ortsseite
„Ärmel- und T-Shirt-Abzeichen“ aufgebaut und mit Inhalten gefüllt werden:

1. Gemeinde-Wappen (`OW`)
2. Wappen im Feuerwehrgebrauch (`FW`)
3. Ärmel- und T-Shirt-Abzeichen (`AWA`)

Die Listen teilen sich eine gemeinsame JavaScript-Initialisierung und einen
gemeinsamen API-Endpunkt. Spalten, Abfragen und die Aufbereitung der Datensätze
bleiben jedoch je Liste in einer eigenen PHP-Klasse.

## 1. Übersicht des Datenflusses

```text
OrtsAuswList.php
  -> OrtsEdit.php?ID=<fw_id>&proj=AERM
    -> OrtsEdit_ph0_inc.php
      -> OrtsWappenList_inc.php   (OW)
      -> FwWappenList_inc.php     (FW)
      -> AermAbzList_inc.php      (AWA)
      -> JSON-Konfiguration: lists + globalParams
      -> Tabulator + list-tables.js
        -> je Liste: API-Aufrufe für columns, filters und data
          -> ListHostAPI.php
            -> ListResolver.php
              -> OW:  OWAList
              -> FW:  FWAList
              -> AWA: AWAList
                -> Datenbankabfrage und Aufbereitung der Zeilen
        -> Tabulator rendert die Antwort im jeweiligen HTML-Container
```

## 2. Seite und Container vorbereiten

Die Auswahlliste der Orte wird in `OrtsAuswList.php` aufgebaut. Von dort führt
der Bearbeitungsaufruf zu `OrtsEdit.php`. Im Bearbeitungsmodus (`$phase == 0`)
wird `OrtsEdit_ph0_inc.php` eingebunden.

Die drei PSA-Listen werden dort nur für das Projekt `AERM` ausgegeben. Das
Partial initialisiert zunächst `$allLists` als leeres Array und bindet dann die
drei Listen-Partials in der Reihenfolge `OW`, `FW`, `AWA` ein. Jedes Partial
erzeugt seinen HTML-Container und hängt genau ein Konfigurationsarray an
`$allLists` an. Die Partials laden weder die Tabulator-Bibliothek noch
`list-tables.js` selbst.

Die drei Konfigurationsdateien sind:

- [`OrtsWappenList_inc.php`](../FWHist/login/Modules/Psa/OrtsWappenList_inc.php)
- [`FwWappenList_inc.php`](../FWHist/login/Modules/Psa/FwWappenList_inc.php)
- [`AermAbzList_inc.php`](../FWHist/login/Modules/Psa/AermAbzList_inc.php)

Jede Konfiguration enthält unter anderem:

| Schlüssel | Zweck |
| --- | --- |
| `id` | Listen-ID, die auch an die API übergeben und im Resolver verwendet wird |
| `container` | ID des HTML-Elements, in das Tabulator rendert |
| `apiUrl` | Gemeinsamer Endpunkt `ListHostAPI.php` |
| `editScript`, `actionScript`, `actionLabel` | Ziele und Beschriftung der Bearbeitungsaktionen |
| `showActionColumn` | Steuert, ob `list-tables.js` eine zusätzliche Spalte „Aktion“ anlegt |
| `filterSelectFields`, `filterTextFields` | Legen fest, welche vom Server gemeldeten Filter im UI erscheinen |
| `paginationSizes` | Auswahl der Seitengrößen |

Die IDs der HTML-Wurzeln lauten `root-OW`, `root-FW` und `root-AWA`; die
Tabellencontainer heißen `OW-table`, `FW-table` und `AWA-table`. Die Zuordnung
zwischen `root-<id>` und `container` wird beim Initialisieren benötigt.

Nach Einbindung aller Partials erzeugt `OrtsEdit_ph0_inc.php` einmalig das
JavaScript-Array `lists` aus `$allLists`. Außerdem setzt es `globalParams`.
Für diese Seite sind darin vor allem relevant:

- `project`: das aktuelle Projekt, hier normalerweise `AERM`
- `sel_id`: die `fw_id` des gerade bearbeiteten Orts
- `edit_Protect`: der vom Formular berechnete Bearbeitungsschutz

Danach bindet die Seite einmal `tabulator.min.js` und `list-tables.js` ein.

## 3. Browserinitialisierung und API-Aufrufe

Nach `DOMContentLoaded` liest `list-tables.js` die globale Listen-Konfiguration
ein und initialisiert die Listen nacheinander. Für jede Konfiguration prüft das
Script, ob Wurzel- und Tabellencontainer vorhanden sind.

Anschließend stellt es an `apiUrl` drei GET-Anfragen:

```text
?list_ID=<id>&project=<project>&sel_id=<fw_id>&edit_Protect=<wert>&action=columns
?list_ID=<id>&project=<project>&sel_id=<fw_id>&edit_Protect=<wert>&action=filters
?list_ID=<id>&project=<project>&sel_id=<fw_id>&edit_Protect=<wert>&action=data
```

Optionale Parameter werden nur ergänzt, wenn ein Wert vorhanden ist. Die drei
Anfragen liefern getrennt:

1. Spaltendefinitionen für Tabulator,
2. Filter-Metadaten und mögliche Filterwerte,
3. die aufbereiteten Datensätze.

Die Antworten werden parallel abgerufen. Schlägt eine Anfrage fehl, wird die
Initialisierung dieser Liste abgebrochen und ein Fehler im zugehörigen
Container angezeigt. Ein fehlender Datenbanktisch kann dabei als
`TABLE_NOT_FOUND` erkannt und mit einem passenden Hinweis dargestellt werden.

## 4. API-Routing zur passenden Listenklasse

`ListHostAPI.php` liest `list_ID`, `action`, `project`, `sel_id` und
`edit_Protect` aus der Anfrage. `sel_id` wird in eine Ganzzahl umgewandelt. Für
die drei hier beschriebenen IDs ist eine Auswahl-ID erforderlich; sie stammt
aus `globalParams.sel_id`.

Der API-Endpunkt öffnet die Datenbankverbindung und übergibt die Listen-ID an
`ListResolver::resolve()`. Der Resolver verwendet eine explizite Zuordnung:

| `list_ID` | PHP-Klasse | Datenbanktabelle |
| --- | --- | --- |
| `OW` | `FWHist\Modules\Psa\Lists\OWAList` | `psw_ort_wappen` |
| `FW` | `FWHist\Modules\Psa\Lists\FWAList` | `psw_ff_wappen` |
| `AWA` | `FWHist\Modules\Psa\Lists\AWAList` | `psw_aermel_abz` |

Abhängig von `action` ruft der Endpunkt `getColumns()`, `getFilters()` oder
`getData()` der aufgelösten Klasse auf und gibt das Ergebnis als JSON zurück.
Die Klassen implementieren somit die fachliche, listenabhängige Datenlogik;
API und JavaScript übernehmen Routing und generische Anzeige.

## 5. Erzeugen der Datensätze je Liste

Alle drei Listen filtern die jeweilige Tabelle nach `fo_fw_id = :fo_fw_id`.
Der Parameterwert ist die `fw_id` des Orts, dessen Bearbeitungsseite gerade
angezeigt wird. Damit gehören nur Datensätze dieses Orts in die jeweilige
Tabelle. Die Abfrage verwendet einen vorbereiteten Parameter.

### 5.1 Gemeinde-Wappen (`OW`)

`OWAList::getData()` liest aus `psw_ort_wappen` und sortiert nach
`fo_gde_w_sort`.

Die Spalten werden in `getColumns()` explizit zusammengestellt:

- `fo_id`
- `fo_gde_w_komment`
- `fo_gde_wappen`

Die Feldbezeichnungen werden, soweit vorhanden, aus den Datenbankkommentaren
abgeleitet. In `modifyRow()` wird ein vorhandener Wappen-Dateiname in einen
Link umgewandelt. Bei bekannten Grafik-Endungen wird das Bild zusätzlich
direkt in der Tabellenzelle angezeigt. Änderungsmetadaten wie
`fo_changed_id` und `fo_changed_at` werden vor der Ausgabe entfernt.

### 5.2 Wappen im Feuerwehrgebrauch (`FW`)

`FWAList::getData()` liest aus `psw_ff_wappen` und sortiert nach
`fo_ff_w_sort`.

Die sichtbaren Spalten sind:

- `fo_id`
- `fo_ff_w_komm`
- `fo_ff_wappen`

Auch hier werden Bilddateien als anklickbare Vorschau dargestellt; andere
Dateien werden als Link ausgegeben. Die Änderungsmetadaten werden aus der
Zeile entfernt. Die Tabellenüberschriften stammen, sofern vorhanden, aus den
Datenbankkommentaren.

### 5.3 Ärmel- und T-Shirt-Abzeichen (`AWA`)

`AWAList::getData()` liest aus `psw_aermel_abz` und sortiert nach
`fo_ff_a_sort`.

Die sichtbaren Spalten sind:

- `fo_ff_abz_typ`
- `fo_ff_abzeich` (in der Überschrift als „Bild“ bezeichnet)

Ist `fo_ff_abz_typ` leer, versucht `modifyRow()` den Klartext anhand von
`fo_ff_a_typ_a` aus der internen Zuordnungstabelle der Klasse zu ermitteln.
Bilddateien werden als anklickbare Vorschau ausgegeben, andere Dateitypen als
Link. Interne Sortier- und Änderungsfelder werden aus der Ausgabe entfernt.

## 6. Tabulator-Darstellung und clientseitige Bedienung

Nach erfolgreichem Abruf legt `list-tables.js` den Statusbereich mit
Datensatzanzahl, Filter- und Paginierungsbedienelementen an. Die Filter werden
nur dann im UI erzeugt, wenn das Feld sowohl von der PHP-Klasse in
`filtersMeta` gemeldet als auch in `filterSelectFields` beziehungsweise
`filterTextFields` der Listen-Konfiguration zugelassen ist.

Anschließend werden die Spaltenformatierer aufgelöst und die Tabulator-Tabelle
mit Spalten und Daten erstellt. Die Paginierung arbeitet lokal im Browser;
auch die Filter werden clientseitig auf die bereits geladenen Datensätze
angewendet. Die „Datensätze gesamt“-Anzeige wird bei Filteränderungen
aktualisiert.

### Aktionsspalte und Bearbeitungslinks

`list-tables.js` ergänzt standardmäßig eine Spalte „Aktion“. Nur wenn die
Konfiguration ausdrücklich
`'showActionColumn' => false` enthält, wird diese zusätzliche Spalte
weggelassen. Das ist eine UI-Entscheidung; sie verändert weder die API-Antwort
noch die Zugriffsberechtigung des Bearbeitungsscripts.

Die PHP-Listen können zusätzlich in jedem Datensatz ein Feld `action` mit
einem HTML-Bearbeitungslink zurückgeben. Ist dieses Feld vorhanden, zeigt der
Formatter der Spalte „Aktion“ diesen Link an. Fehlt es, erzeugt das JavaScript
den Link aus `actionScript` beziehungsweise `editScript` und einer ID aus der
Zeile.

Die neue-Datensatz-Schaltfläche wird separat aus `editScript` erzeugt. Ist
`editScript` leer, wird keine solche Schaltfläche eingeblendet.

**Wichtig:** Das Ausblenden der Aktionsspalte ist keine Zugriffskontrolle.
Berechtigungen müssen weiterhin in den jeweiligen Bearbeitungsscripts
serverseitig geprüft werden. Ebenso ist `edit_Protect` nicht gleichbedeutend
mit `showActionColumn`: `edit_Protect` wird als globaler Parameter an die API
weitergereicht, während `showActionColumn` direkt in der jeweiligen
Listen-Konfiguration das Tabulator-Layout steuert.

## 7. Login- und Berechtigungseinstellungen

Die sichtbaren Aktionen und der Formularschutz sind getrennte Mechanismen:

- `$isLoggedIn = isset($_SESSION['BS_Prim']['BE'])` wird in den OW- und
  AWA-Partials für die UI-Konfiguration verwendet. Bei diesen Listen steuert
  `showActionColumn` die zusätzliche Aktionsspalte. `editScript` wird nur bei
  vorhandener Sitzung gesetzt.
- Die FW-Konfiguration setzt `editScript` und `actionScript` ebenfalls nur
  bei vorhandener Sitzung. In der aktuell geprüften Datei fehlt jedoch
  `showActionColumn`. Da JavaScript die Aktionsspalte standardmäßig ergänzt,
  wird sie für FW auch ohne expliziten Schalter angelegt. Soll sie für FW
  denselben Sichtbarkeitsregeln wie für OW und AWA folgen, muss dort
  `showActionColumn` ebenfalls auf `$isLoggedIn` gesetzt werden.
- `$editProtect` in `OrtsEdit_ph0_inc.php` wird aus dem Zugriffsrecht der
  Seite abgeleitet und an `FormRendererFlex` übergeben. Dieses Flag wird
  außerdem als `globalParams.edit_Protect` an die Listen-API weitergegeben.
  Die Ausblendung einer Tabulator-Spalte ersetzt diese serverseitige
  Berechtigungsprüfung nicht.

Die Listenklassen erhalten das Schutzflag als Argument, verwenden es bei der
derzeitigen Aufbereitung der hier beschriebenen Tabelleninhalte aber nicht,
um die Zeilen oder Aktionslinks wegzulassen.

## 8. Neue PSA-Liste ergänzen

Für eine weitere Liste dieses Musters sind typischerweise folgende Schritte
erforderlich:

1. Listen-Partials konfigurieren und einen eindeutigen HTML-Root sowie
   Tabellencontainer ausgeben.
2. Das Listen-Konfigurationsarray an `$allLists` anhängen. `id` muss mit der
   späteren `list_ID` übereinstimmen.
3. Eine Klasse mit `getListId()`, `getColumns()`, `getFilters()` und
   `getData()` erstellen.
4. Die ID mit der Klasse in `ListResolver::REGISTRY` eintragen.
5. In `getData()` die richtigen Daten anhand einer vorbereiteten
   `fo_fw_id`-Abfrage lesen, sortieren und für die Anzeige aufbereiten.
6. Sichtbare Felder, Filterfelder, Zeilengröße und Login-UI in der
   Listen-Konfiguration aufeinander abstimmen.
7. Prüfen, dass das Editierscript Berechtigungen serverseitig kontrolliert;
   UI-Schalter wie `showActionColumn` sind dafür nicht ausreichend.

## 9. Relevante Dateien

- [`OrtsAuswList.php`](../FWHist/login/Modules/Psa/OrtsAuswList.php): Ausgangs-
  beziehungsweise Auswahlseite für Orte.
- [`OrtsEdit.php`](../FWHist/login/Modules/Psa/OrtsEdit.php): lädt das
  Bearbeitungsformular für einen Ort.
- [`OrtsEdit_ph0_inc.php`](../FWHist/login/Modules/Psa/OrtsEdit_ph0_inc.php):
  setzt gemeinsame Parameter zusammen, bindet die drei Listen ein und lädt
  Tabulator und `list-tables.js`.
- [`ListHostAPI.php`](../FWHist/login/Services/API/ListHostAPI.php):
  verarbeitet API-Anfragen und delegiert an die Listenklasse.
- [`ListResolver.php`](../FWHist/login/Services/ListResolver.php): ordnet
  `list_ID` einer Listenklasse zu.
- [`list-tables.js`](../FWHist/js/list-tables.js): lädt die Daten und baut
  Tabulator, Statusbereich, Filter und Aktionsspalte auf.
- [`OWAList.php`](../FWHist/login/Modules/Psa/Lists/OWAList.php),
  [`FWAList.php`](../FWHist/login/Modules/Psa/Lists/FWAList.php) und
  [`AWAList.php`](../FWHist/login/Modules/Psa/Lists/AWAList.php): definieren
  Spalten, Filter und Datenaufbereitung je Tabelle.
