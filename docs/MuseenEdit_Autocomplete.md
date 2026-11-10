# Autocomplete-Aufrufe in `MuseenEdit_ph0_inc.php`

Diese Notiz beschreibt, was angepasst werden muss, damit die
Autocomplete-Bausteine im Museumsformular mit `FormRendererFlex_Ext`
funktionieren.

Betroffene Dateien:

- [MuseenEdit_ph0_inc.php](../FWHist/login/Modules/Oeffentlichkeitsarbeit/MuseenEdit_ph0_inc.php)
- [FormRendererFlex_Ext.php](../FWHist/login/Services/FormRendererFlex_Ext.php)
- [AutoComp_Staat.js](../FWHist/login/AllgVerw/js/AutoComp_Staat.js)
- [AutoComp_Bdld.js](../FWHist/login/AllgVerw/js/AutoComp_Bdld.js)
- [AutoComp_Mandant.js](../FWHist/login/Modules/Mandanten/js/AutoComp_Mandant.js)
- [MuseenEdit.php](../FWHist/login/Modules/Oeffentlichkeitsarbeit/MuseenEdit.php)

## Aktueller Stand

Das Museumsformular erzeugt derzeit ein `FormRendererFlex`-Objekt und ruft
zusätzlich die bisherigen globalen Funktionen auf:

```php
use FWHist\Services\FormRendererFlex;

$forms = new FormRendererFlex($meta, $phase, $neu, [], $editProtect, $module);

if (!$editProtect) {
    AutoCompForm_Staat();
}
```

Analog werden `AutoCompForm_Bdld()` und
`AutoCompForm_Mandant('toggle', 'mandant', 'mandant')` aufgerufen. Diese
Aufrufe geben ihr HTML direkt aus. Die aktuelle
`FormRendererFlex_Ext`-Klasse bietet dagegen Instanzmethoden mit Namen
`renderAutoComp...()` an, welche HTML als String zurückgeben. Dieser String
muss mit `echo` ausgegeben werden.

## Änderungen im PHP-Formular

### Erweiterungsklasse verwenden

Den Import der Basisklasse durch den Import der Erweiterung ersetzen:

```php
use FWHist\Services\FormRendererFlex_Ext;
```

Dann den Renderer mit denselben Formularparametern erzeugen:

```php
$forms = new FormRendererFlex_Ext(
    $meta,
    $phase,
    $neu,
    [],
    $editProtect,
    $module
);
```

Die normalen Feldaufrufe wie `renderTextLikeFieldFlex()` und
`renderSelectFieldFlex()` funktionieren über Delegation weiterhin wie zuvor.

### Alte Autocomplete-Funktionen ersetzen

Die bisherigen Aufrufe werden durch Methodenaufrufe auf `$forms` ersetzt.
Da diese Methoden einen String zurückgeben, muss das Ergebnis ausgegeben
werden:

```php
echo $forms->renderTextLikeFieldFlex('mu_staat', 50);
echo $forms->renderAutoCompStaat();

echo $forms->renderTextLikeFieldFlex('mu_bdland', 50);
echo $forms->renderAutoCompBdld();

echo $forms->renderTextLikeFieldFlex('mu_eigner', 50);
echo $forms->renderAutoCompMandant('toggle', 'mandant', 'mandant');
```

Die Instanzmethoden unterdrücken den Autocomplete-Baustein automatisch, wenn
`$editProtect` wahr oder `$phase !== 0` ist. Die bisherigen zusätzlichen
`if (!$editProtect)`-Blöcke können deshalb für diese Ausgabe entfallen. Dabei
gilt zu beachten, dass die Schutzbedingung der Erweiterung auch die Phase
berücksichtigt und somit strenger ist als ein Test nur auf `$editProtect`.

Die statischen Methoden, zum Beispiel
`FormRendererFlex_Ext::autoCompMandant(...)`, geben ebenfalls HTML zurück,
wenden aber keinen Schreibschutz an. Für das Museumsformular sind deshalb die
Instanzmethoden `renderAutoComp...()` die passende Variante.

## JavaScript-Dateien einbinden

Die JavaScript-Dateien werden nicht automatisch durch
`FormRendererFlex_Ext` geladen. Sie müssen weiterhin vom Formular eingebunden
werden. Für diese drei Bausteine sind nötig:

```php
echo "<script src='" . $basePath . "/FWHist/login/AllgVerw/js/AutoComp_Staat.js'></script>";
echo "<script src='" . $basePath . "/FWHist/login/AllgVerw/js/AutoComp_Bdld.js'></script>";
echo "<script src='" . $basePath . "/FWHist/login/Modules/Mandanten/js/AutoComp_Mandant.js'></script>";
```

Im bestehenden Formular hat der `AutoComp_Bdld.js`-Tag ein fehlerhaftes
schließendes Tag (`</script>` fehlt das `>` an der richtigen Stelle). Außerdem
wird momentan `AutoComp_Urheber.js` geladen, obwohl hier der Baustein
`AutoComp_Mandant` verwendet wird. Die Script-Quelle muss deshalb auf
`AutoComp_Mandant.js` geändert werden.

## Feldnamen und Datenfluss

Das Museumsformular zeigt die Datenfelder `mu_staat` und `mu_bdland` an. Die
Autocomplete-Bausteine erzeugen standardmäßig separate Suchfelder mit den IDs
`staat` und `bdld`, sowie Hidden-Felder `staat_id` und `bdld_id`.

Die JavaScript-Dateien schreiben bei einer Auswahl die ausgewählte ID in das
jeweilige Hidden-Feld. Die serverseitige Verarbeitung in `MuseenEdit.php`
übernimmt diese IDs in die fachlichen Museumsfelder:

```php
if ($neu['staat_id'] != '') {
    $neu['mu_staat'] = $neu['staat_id'];
}
if ($neu['bdld_id'] != '') {
    $neu['mu_bdland'] = $neu['bdld_id'];
}
```

Damit die Auswahl gespeichert werden kann, müssen die Hidden-Felder ihre
Namen `staat_id` und `bdld_id` behalten. Das aktuelle JavaScript sucht für eine
zusätzliche Aktualisierung der sichtbaren Felder jedoch nach `#ei_staat` bzw.
`#ei_bdld`. Solche Elemente gibt es im Museumsformular nicht. Für die
Museumsmaske sollte daher geprüft werden, ob die sichtbaren Werte von
`mu_staat` und `mu_bdland` direkt aktualisiert werden sollen. Falls ja, muss
die JavaScript-Zielauswahl auf die tatsächlichen Museumsfelder angepasst oder
als konfigurierbares Ziel umgesetzt werden. Die Auswahl wird durch das
Hidden-Feld zwar serverseitig übernommen, aber ohne diese Anpassung wird das
sichtbare Museumsfeld nach der Auswahl nicht unmittelbar aktualisiert.

Die Mandantenauswahl verwendet ein sichtbares Feld `mandant` und das Hidden-Feld
`mandant_id`. Das Script `AutoComp_Mandant.js` setzt die ausgewählte
Mandanten-ID in dieses Hidden-Feld. Die Verarbeitung in `MuseenEdit.php`
übernimmt `mandant_id` anschließend nach `mu_eigner`:

```php
if ($neu['mandant_id'] != '') {
    $neu['mu_eigner'] = intval($neu['mandant_id']);
}
```

Diese Zuordnung ist bereits vorhanden und sollte beim Umbau beibehalten
werden.

## Zusammenfassung

Für funktionierende Aufrufe im Museumsformular müssen somit:

1. `FormRendererFlex` durch `FormRendererFlex_Ext` ersetzt werden.
2. `AutoCompForm_*()` durch `echo $forms->renderAutoComp*()` ersetzt werden.
3. Die Skripte für Staat, Bundesland und Mandant mit gültigen Script-Tags
   eingebunden werden.
4. Die JavaScript-Ziel-IDs für die Aktualisierung sichtbarer Staaten- und
   Bundeslandfelder mit den Museumsfeldern abgeglichen werden.
5. Die Hidden-Feldnamen `staat_id`, `bdld_id` und `mandant_id` sowie deren
   bestehende serverseitige Übernahme erhalten bleiben.
