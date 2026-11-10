## Umstellung der Autocomplete-Funktionen auf neue API-Klassen

Die bestehenden Autocomplete Funktionen wurden erfolgreich auf die neue Klassen-Struktur umgestellt:

### Durchgeführte Änderungen:

1. **StaatenAutocompleteAPI.php** 
   - Implementierung als Erweiterung von `AutocompleteAPI`
   - Konfiguriert für `fv_staaten` Tabelle
   - Sucht in Feldern: `st_name` und `st_abkzg`
   - Verwendet `st_id` als ID-Feld

2. **StaatenAutoComp_API.php** (Handler)
   - Refaktoriert, um die neue `StaatenAutocompleteAPI` Klasse zu nutzen
   - Ruft `handleRequest()` auf die Klasse auf
   - Returniert JSON mit: `id`, `label`, `value`

3. **AutoComp_Staat.js** 
   - Aktualisiert für neue API-Antwort-Format
   - Nutzt die neue Klasse per AJAX über `StaatenAutoComp_API.php`
   - Speichert die `st_id` in verstecktes Feld `staat_id`

4. **AutocompleteAPI.php** (Bug-Fix)
   - Behoben: Fehler beim Parameter-Binding mit mehreren Suchfeldern
   - Verwendet nun eindeutige Parameter-Namen `:term0`, `:term1`, etc.

### Aufruf in Formularen:
- `MuseenEdit.php` / `MuseenEdit_ph0_inc.php`
- `TerminEdit_ph0_inc.php`
- `MitglEdit_ph0.inc.php`
- `BenEdit_ph0_inc.php`

Alle rufen die Funktion `AutoCompForm_Staat()` auf (definiert in `CommFuncsLib.php`), die die neuen APIs nutzt.