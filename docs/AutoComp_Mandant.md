# AutoComp_Mandant.js

## Zweck

`FWHist/login/Modules/Mandanten/js/AutoComp_Mandant.js` stellt die Auswahl eines Mandanten ueber eine native Autocomplete-Funktion bereit.

Die Komponente dient nur zur Auswahl einer Mandanten-ID. Sie veraendert das fachliche Tabellenfeld `mu_eigner` nicht. Der ausgewaehlte Wert wird in das versteckte Feld `mandant_id` geschrieben. Die Uebernahme nach `mu_eigner` erfolgt erst serverseitig beim Speichern des Formulars.

Die Datei wird derzeit unter anderem von der Museumsmaske eingebunden:

```php
echo "<script src='" . $basePath . "/FWHist/login/Modules/Mandanten/js/AutoComp_Mandant.js'></script>";
```

## Zugehoerige Dateien

| Datei | Aufgabe |
|---|---|
| `FWHist/login/Modules/Mandanten/js/AutoComp_Mandant.js` | Browserlogik, Anzeige und Auswahl |
| `FWHist/login/Modules/Mandanten/API/MandantenAutoComp_API.php` | JSON-Endpunkt fuer Suchanfragen |
| `FWHist/login/Modules/Mandanten/API/MandantenAutocompleteAPI.php` | Definition der Mandantenabfrage |
| `FWHist/login/Services/API/AutoCompleteAPI.php` | Gemeinsame Abfrage- und Antwortlogik |
| `FWHist/login/Services/CommFuncsLib.php` | HTML-Hilfsfunktion `AutoCompForm_Mandant()` |
| `FWHist/login/Modules/Oeffentlichkeitsarbeit/MuseenEdit.php` | Serverseitige Uebernahme von `mandant_id` nach `mu_eigner` |

## HTML-Struktur

Die PHP-Funktion `AutoCompForm_Mandant()` erzeugt diese wichtigen Elemente:

```html
<input type="text" id="mandant" name="mandant">
<input type="hidden" id="mandant_id" name="mandant_id">
```

Das sichtbare Feld `mandant` ist nur das Such- und Auswahlfeld. Es ist nicht das Datenbankfeld `mu_eigner`.

Das versteckte Feld `mandant_id` enthaelt nach einer erfolgreichen Auswahl die `ei_id` des gewaehlten Datensatzes. Dieses Feld wird beim Absenden des Formulars mitgesendet.

## Darstellungsvarianten

### Auswahlfeld allein

Der Standardaufruf zeigt das Auswahlfeld direkt an:

```php
AutoCompForm_Mandant();
```

### Auswahl hinter einem Umschalter

Mit dem Modus `toggle` wird das Auswahlfeld zusaetzlich hinter einem aufklappbaren Bereich angezeigt:

```php
AutoCompForm_Mandant('toggle', 'mandant', 'mandant');
```

Die Museumsmaske verwendet diese Variante. Das vorhandene Feld `mu_eigner` bleibt dabei unveraendert, bis das Formular gespeichert wird.

Die Umschaltung wird ueber folgende Elemente gesteuert:

```html
<button class="mandant-autocomplete-toggle">
<div id="mandant-autocomplete-panel">
```

## JavaScript-Ablauf

### 1. Initialisierung

Nach dem Laden des Dokuments fuehrt das Script `start()` aus. Dabei werden:

1. die CSS-Regeln fuer Liste und Umschalter eingefuegt,
2. der Umschalter gesucht und mit seinem Auf-/Zu-Verhalten verbunden,
3. das Feld `#mandant` initialisiert.

Ein Feld wird nur einmal initialisiert. Dazu wird das Attribut `data-autocomplete-ready="true"` verwendet.

### 2. Eingabe

Bei jeder Eingabe wird der Suchbegriff mit `trim()` bereinigt. Erst ab zwei Zeichen wird eine Anfrage gestartet.

Die Anfrage wird um 180 Millisekunden verzoegert. Dadurch werden nicht fuer jeden einzelnen Tastendruck sofort Datenbankanfragen erzeugt.

Wird der Suchbegriff wieder kuerzer als zwei Zeichen, wird die Ergebnisliste geschlossen und eine eventuell noch offene Anfrage logisch verworfen.

### 3. API-Aufruf

Der API-Pfad wird relativ zur geladenen Scriptdatei gebildet:

```javascript
new URL('../API/MandantenAutoComp_API.php', scriptUrl).href
```

Gesendet wird der Parameter:

```text
term=<Suchbegriff>
```

Die Antwort wird als JSON erwartet.

### 4. Ergebnisliste

Die Antwort kann entweder direkt ein Array oder bei aktiviertem Debug-Modus ein Objekt mit `results` sein. Beide Formen werden verarbeitet:

```javascript
Array.isArray(data) ? data : (data.results ?? [])
```

Jedes Ergebnis wird als `li`-Element in einer `ul`-Liste dargestellt. Der sichtbare Text stammt aus `item.label`.

### 5. Auswahl

Bei Mausklick oder bestaetigter Tastaturauswahl wird nur Folgendes ausgefuehrt:

```javascript
input.value = item.value || item.label;
hiddenInput.value = item.id;
```

Dabei gilt:

- `#mandant` zeigt den Namen bzw. die Bezeichnung der Auswahl an.
- `#mandant_id` erhaelt die ID `item.id`, also die `ei_id` des Mandanten.
- `mu_eigner` wird vom JavaScript nicht gesucht, nicht gelesen und nicht beschrieben.

Nach der Auswahl werden `change`-Events fuer das Suchfeld und das versteckte ID-Feld ausgelost. Die Auswahlliste wird danach geschlossen.

## API und Datenbankfelder

`MandantenAutocompleteAPI` verwendet die Tabelle `ma_mandanten`:

```php
parent::__construct(
    'ma_mandanten',
    ['ei_org_name', 'ei_name'],
    'ei_id',
    ['ei_org_name', 'ei_name']
);
```

Die Suche beginnt mit dem eingegebenen Begriff (`LIKE '<term>%'`) in:

- `ei_org_name`
- `ei_name`

Die Antwort enthaelt maximal zehn Treffer. Ein Treffer hat grundsaetzlich diese Form:

```json
{
  "id": 12,
  "label": "Organisation (Name)",
  "value": "Organisation"
}
```

`id` ist die fachlich relevante Mandanten-ID und wird in `mandant_id` gespeichert.

## Serverseitige Uebernahme

Die Museumsmaske uebernimmt den Wert erst im PHP-Verarbeitungsschritt:

```php
if ($neu['mandant_id'] != '') {
    $neu['mu_eigner'] = $neu['mandant_id'];
}
```

Danach werden nur die Datenfelder des Museums (`mu_...`) weiterverarbeitet und gespeichert. Dadurch bleibt die Zustandsaenderung an `mu_eigner` an der serverseitigen Speicherlogik gebunden.

Der wichtige Datenfluss lautet somit:

```text
Benutzereingabe
    -> #mandant
    -> API-Suche
    -> Auswahl von ei_id
    -> #mandant_id
    -> POST
    -> PHP: mu_eigner = mandant_id
    -> Datenbank
```

## Tastaturbedienung

Wenn Treffer angezeigt werden, stehen folgende Tasten zur Verfuegung:

| Taste | Wirkung |
|---|---|
| `ArrowDown` | Naechsten Treffer markieren |
| `ArrowUp` | Vorherigen Treffer markieren |
| `Enter` | Markierten Treffer uebernehmen |
| `Escape` | Trefferliste schliessen |

Die Liste kann auch per Mausklick ausgewaehlt werden. Ein Klick ausserhalb des Eingabefelds oder der Liste schliesst die Liste.

## Fehlerverhalten

Bei einem HTTP-Fehler oder einer ungueltigen Anfrage wird die Liste geschlossen. Der Fehler wird in der Browserkonsole protokolliert:

```text
Mandanten-Autocomplete fehlgeschlagen: ...
```

Die PHP-API liefert bei einer leeren Suche ein leeres Array. Datenbank- oder Autoload-Fehler werden serverseitig behandelt und als leere Ergebnisliste beziehungsweise als JSON-Fehlerantwort ausgegeben.

## Wichtige Wartungsregel

Im JavaScript darf keine direkte Zuweisung an `mu_eigner` ergaenzt werden. Die korrekte Verantwortlichkeit ist:

- JavaScript: Mandanten suchen und `mandant_id` setzen.
- PHP: `mandant_id` pruefen und vor dem Speichern nach `mu_eigner` uebernehmen.
- Datenbank: finalen Datensatz speichern.

Damit wird verhindert, dass die reine Anzeige- oder Auswahlaktion bereits das Tabellenfeld veraendert.
