# Korrektur der Sammlungs-MultiSelect-Auswahl

## Anlass

Bei der Verwendung von `renderMultiSelectFieldFlex()` mit
`configId: 'SAMMLUNG'` wurden nicht die erwarteten Sammlungsdaten aus
`hi_sammlung` angezeigt. Erwartet wurden die Gruppierung über `sa_grup` und
die Sammlungsnamen aus `sa_sammlg` und `sa_name`. Stattdessen erschien eine
Auswahlliste, die wie die Archivordnungswerte wirkte.

Die Untersuchung des Aufrufwegs ergab:

- Der Formularaufruf übergibt `SAMMLUNG`.
- `MultiSelectResolver` ordnet `SAMMLUNG` dem Provider
  `SammlungMultiSelect` zu. `ARCHIVORDNUNG` bleibt ein eigener Provider.
- Der API-Endpunkt leitet die `config_ID` unverändert an den Resolver weiter.
- Der Fehler lag in der Sammlungs-Abfragelogik: Ebene 1 wurde zuvor aus
  `sa_sammlg`-Werten gebildet, statt zuerst die vorhandenen Gruppen aus
  `sa_grup` zur Auswahl anzubieten.

Es gab in diesem Aufrufpfad keinen gefundenen Resolver-Fallback von
`SAMMLUNG` auf `ARCHIVORDNUNG`. Die Korrektur richtet daher den
Sammlungs-Provider an der fachlich vorgesehenen Struktur aus; die
Archivordnungs-Konfiguration und deren Provider werden nicht geändert.

## Geänderter Ablauf

Die Auswahl läuft über drei Teile:

1. `FormRendererFlex::renderMultiSelectFieldFlex()` gibt die
   `config-id="SAMMLUNG"` am `<fw-multi-select>`-Element aus.
2. `fw_multi_select.js` sendet für jede Ebene einen Request an
   `MultiSelectAPI.php`, unter anderem mit `config_ID`, `level` und – ab
   Ebene 2 – `parent`.
3. `MultiSelectAPI.php` löst den Provider anhand der `config_ID` auf und ruft
   dessen `getOptions()`-Methode auf. Für `SAMMLUNG` werden die Optionen aus
   `SammlungMultiSelect` ermittelt.

Der API-Endpunkt stellt dem Provider-Ergebnis zentral den Eintrag
„Bitte wählen“ voran. Der Provider selbst liefert nur die fachlichen
Optionen.

## Neue Abfragelogik in `SammlungMultiSelect`

### Ebene 1: tatsächlich vorhandene Gruppen

Ebene 1 liest eindeutige, nicht-leere Gruppenschlüssel aus `hi_sammlung`:

```sql
SELECT DISTINCT sa_grup
  FROM hi_sammlung
 WHERE sa_grup IS NOT NULL AND sa_grup <> ''
 ORDER BY sa_grup ASC
```

Der Schlüssel `sa_grup` wird sowohl als Option-Wert als auch zur Ermittlung
der Folgeebene verwendet. Für den sichtbaren Text wird – sofern vorhanden –
die Beschreibung aus `VF_Sel_SA_Such` verwendet. Für Gruppenschlüssel ohne
Eintrag in dieser Konstanten wird der Schlüssel selbst angezeigt. Damit
bleiben auch zusätzliche oder später ergänzte Gruppen auswählbar.

### Ebene 2: Sammlungen der gewählten Gruppe

Der gewählte Gruppenschlüssel wird als gebundener Parameter für die Abfrage
der Sammlungen verwendet:

```sql
SELECT sa_sammlg, sa_name
  FROM hi_sammlung
 WHERE sa_grup = :group
 ORDER BY sa_sammlg ASC
```

Jeder Ergebnisdatensatz wird zu einer Option:

- `value` = `sa_sammlg`
- `text` = `sa_name`

Damit erscheinen nach der Gruppenauswahl die zugehörigen tatsächlichen
Sammlungen und deren Namen, statt dass bereits Ebene 1 einzelne
Sammlungsschlüssel anbietet.

### Ebene 3 und folgende: Kinder anhand des Elternschlüssels

Für tiefere Ebenen wird die bestehende Adjazenzlisten-Logik beibehalten:
`sa_grup` der Kindzeile muss dem `sa_sammlg`-Wert der ausgewählten Elternzeile
entsprechen. Selbstverweise werden ausgeschlossen. Die Verknüpfung folgt
ausschließlich dem gespeicherten `sa_grup`-Wert und nicht einer Interpretation
von Bindestrichen oder Segmenten im Namen oder Schlüssel.

Die Abfragen verwenden weiterhin `DB_GenericLog::fetchAll()` und gebundene
Parameter. Die konfigurierte maximale Ebenenzahl ist eine Obergrenze; ein
leeres Ergebnis beendet den jeweiligen Ast der Kaskade.

## Gruppennamen laden

Die Gruppentexte werden aus der globalen Konstante `VF_Sel_SA_Such` gelesen.
Dazu bindet `MultiSelectAPI.php` nun `Services/ConstLib.php` explizit ein.
Die Konstante ist nicht Teil eines Namespaces und wird daher im Provider als
`\VF_Sel_SA_Such` angesprochen.

Die Änderung ergänzt lediglich die Verfügbarkeit dieser Bezeichnungen für den
API-Aufruf; sie verändert keine Archivordnungs-Abfrage.

## Ergebnis und gespeicherter Wert

Das Web Component speichert im versteckten Formularfeld den Wert der tiefsten
ausgewählten Ebene. Für die Sammlungs-Auswahl ist dies der Wert
`sa_sammlg` der gewählten Sammlung bzw. Unter-Sammlung. Die Sammlungswerte
werden nicht in einen Composite-String für die Archivordnung umgewandelt.

Bei einer Gruppe ohne passende Sammlungszeilen oder bei einem Blatt ohne
weitere Kinder liefert der Provider für die nächste Ebene keine Optionen;
damit endet die Kaskade an dieser Stelle.

## Betroffene Dateien

- [`SammlungMultiSelect.php`](../FWHist/login/Services/MultiSelect/SammlungMultiSelect.php):
  Auswahl der Ebenen-1-Gruppen, Zuordnung der Gruppennamen und Abfrage der
  Sammlungen in Ebene 2.
- [`MultiSelectAPI.php`](../FWHist/login/Services/API/MultiSelectAPI.php):
  explizites Laden von `ConstLib.php` für die bekannten Gruppentexte.
- [`FormRendererFlex.php`](../FWHist/login/Services/FormRendererFlex.php):
  PHPDoc-Beispiel um `SAMMLUNG` als gültige Konfiguration ergänzt.
- [`README_MultiSelect.md`](../FWHist/login/Services/API/README_MultiSelect.md):
  Beschreibung der Sammlungs-Ebenen an die implementierte Logik angepasst.

## Validierung

Die PHP-Syntaxprüfung war für `SammlungMultiSelect.php`,
`MultiSelectAPI.php`, `ConstLib.php` und `FormRendererFlex.php` erfolgreich.
Ein Datenbank- bzw. Browser-End-to-End-Test war in der Arbeitsumgebung nicht
möglich. Für einen Integrationstest ist zu prüfen, dass der erste Request
`config_ID=SAMMLUNG` und `level=1` enthält und die API in `data` die vorhandenen
Gruppenschlüssel mit Klartext (oder Rohcode-Fallback) zurückgibt. Nach Auswahl
einer Gruppe muss der nächste Request deren Schlüssel als `parent` senden und
Optionen mit `sa_sammlg` als Wert sowie `sa_name` als Text liefern.
