# `FormRendererFlex` und `FormRendererFlex_Ext`

Diese Dokumentation beschreibt die beiden PHP-Klassen im Namespace
`FWHist\Services`:

- [`FormRendererFlex.php`](../FWHist/login/Services/FormRendererFlex.php)
- [`FormRendererFlex_Ext.php`](../FWHist/login/Services/FormRendererFlex_Ext.php)

`FormRendererFlex` erzeugt HTML für Flex-basierte Formularfelder und stellt
einfache Validierungsfunktionen bereit. `FormRendererFlex_Ext` ergänzt diese
Funktionen um Autocomplete-HTML-Bausteine, die früher in
`CommFuncsLib.php` lagen.

Die Render-Methoden geben HTML als `string` zurück. Die Ausgabe erfolgt daher
üblicherweise mit `echo $forms->...`; die Klassen schreiben das HTML nicht
selbst in die Antwort.

## Zusammenspiel und Zuständigkeiten

`FormRendererFlex` ist als `final` deklariert. Seine internen Eigenschaften sind
privat. `FormRendererFlex_Ext` kann daher nicht von der Basisklasse erben und
setzt stattdessen auf **Komposition und Delegation**:

1. Bei übergebenen Spaltenmetadaten erstellt `FormRendererFlex_Ext` intern ein
   `FormRendererFlex`-Objekt.
2. Aufrufe von normalen Formularmethoden werden an dieses Objekt weitergereicht.
3. Aufrufe von `renderAutoComp...()` werden auf die entsprechenden statischen
   `autoComp...()`-HTML-Builder abgebildet.

Damit kann eine Formularmaske `FormRendererFlex_Ext` als gemeinsamen
Formular-Renderer verwenden, ohne die Autocomplete-Bausteine in die
Basis-Klasse aufzunehmen. Die Klasse kann auch ohne Metadaten erzeugt werden,
wenn nur statische Autocomplete-Bausteine benötigt werden, etwa auf einer
Listenseite.

## Voraussetzungen und Ausgabe

Für normale Formularfelder benötigt `FormRendererFlex` eine Instanz von
`TableColumnMetadata`. Die Klasse verwendet daraus insbesondere:

- `getCommentsMap()` für Feldbezeichnungen,
- `getMaxLengthsMap()` für Zeichenlimits und die Wahl zwischen Eingabefeld und
  Textarea.

Ein nicht leerer Kommentar dient als Label. Fehlt ein Kommentar, wird der
Feldname mit großem Anfangsbuchstaben als Ersatz verwendet. Bei mehreren
Tabellen sollten die Metadaten vor der Renderer-Erzeugung passend zu den
Formularfeldern geladen werden.

Das erzeugte Formular-Markup verwendet unter anderem CSS-Klassen wie
`.field-row`, `.field-label`, `.field-control`, `.input`,
`.readonly-block`, `.error`, `.hint` und `.danger-border`. Die Klassen selbst
liefern kein vollständiges Stylesheet; das Layout muss durch das jeweilige
Formular bzw. globale Styles gestaltet werden.

Autocomplete-Bausteine liefern ebenfalls nur HTML. Die passende
JavaScript-Datei muss von der aufrufenden Seite eingebunden werden. Die
PHP-Klasse lädt kein JavaScript automatisch und führt keine Suche durch.

## `FormRendererFlex`

### Konstruktor und Zustand

```php
new FormRendererFlex(
    TableColumnMetadata $meta,
    int $phase,
    array $data,
    array $errorMsgs = [],
    bool $editProtect = false,
    string $module = ''
);
```

| Parameter | Bedeutung |
|---|---|
| `$meta` | Spaltenmetadaten für Kommentare und maximale Feldlängen. |
| `$phase` | `0` bedeutet grundsätzlich bearbeitbar; jeder andere Wert stellt die Felder auf Anzeige. |
| `$data` | Werte, aus denen die Render-Methoden ihre Feldinhalte lesen, zum Beispiel die aktuelle Datenbankzeile oder Formularwerte. |
| `$errorMsgs` | Vorhandene Fehlermeldungen, indiziert nach Feldname. |
| `$editProtect` | Sperrt die Bearbeitung auch dann, wenn `$phase === 0` ist. |
| `$module` | Optionaler Modulname; wird gespeichert, aber von den hier beschriebenen Renderer- und Validierungsmethoden nicht ausgewertet. |

Der Renderer kopiert die übergebenen Arrays in seinen Zustand. Er besitzt keine
Setter-Methode für `$data`; nachträgliche Änderungen an der ursprünglichen
Variable ändern daher den Zustand des bereits erzeugten Renderers nicht.

Die Bearbeitung ist gesperrt, wenn
`$phase !== 0 || $editProtect` gilt. In diesem Fall werden die meisten
Eingabeelemente durch eine reine Textdarstellung ersetzt oder deaktiviert
(Details siehe jeweilige Methode).

### Öffentliche Render-Methoden

Alle folgenden Methoden liefern einen HTML-String zurück.

| Methode | Zweck und wichtige Parameter |
|---|---|
| `renderHeader(string $text)` | Gibt einen hervorgehobenen Titelblock aus. |
| `renderTrenner(string $text, string $link = '')` | Gibt einen Abschnittsbalken mit Text aus. `$link` wird unverändert an das Markup angehängt und sollte deshalb nur kontrolliertes HTML enthalten. |
| `renderTitleDataBlockFlex(string $fieldName, int $fieldLength = 0, string $infoText = '', string $fieldAttr = '', string $readOnly = '')` | Zeigt ein betontes Label und je nach Zustand einen Wertblock oder ein Texteingabefeld. Mit `$readOnly === 'readonly'` wird die reine Textdarstellung angefordert; `$fieldAttr` ergänzt Attribute des Eingabefelds. |
| `renderTextLikeFieldFlex(string $fieldName, int $fieldLength = 0, string $infoText = '', string $fieldAttr = '', string $readOnly = '', int $switchAt = 70, int $maxRows = 6, int $minRows = 3, int $colsEstimate = 46, float $visibleRatio = 0.67)` | Wählt anhand der Metadaten zwischen `<input type="text">` und `<textarea>`. Bei einer maximalen Länge größer als `$switchAt` wird eine Textarea verwendet. `$fieldLength` steuert bei einem Input das `size`-Attribut. Die Zeilenhöhe der Textarea wird aus Metadatenlänge und aktuellem Inhalt geschätzt und durch `$minRows`/`$maxRows` begrenzt. |
| `renderTextFieldFlex(string $fieldName, int $fieldLength = 0, string $infoText = '', string $fieldAttr = '')` | Kompatibilitäts-Wrapper um `renderTextLikeFieldFlex()`. Er verwendet weiterhin die Umschaltgrenze 70; lange Metadatenfelder können deshalb als Textarea erscheinen. |
| `renderTextareaFieldFlex(string $fieldName, string $infoText = '', string $fieldAttr = '')` | Wrapper, der durch die Umschaltgrenze `0` eine Textarea erzwingt, sofern die Metadatenlänge größer als 0 ist. |
| `renderSelectFieldFlex(string $fieldName, array $options, string $infoText = '', string $fieldAttr = '', string $readOnly = '', string $labelOverride = '')` | Erstellt ein Select aus einem Array `Wert => Beschriftung`. `$labelOverride` ersetzt das Metadaten-Label. Im gesperrten Zustand wird die Beschriftung zum aktuellen Wert angezeigt. |
| `renderMultiSelectFieldFlex(string $fieldName, string $configId, string $apiUrl = 'MultiSelectAPI.php', string $project = '', string $infoText = '', string $labelOverride = '')` | Erstellt ein `<fw-multi-select>`-Element für die kaskadierende Mehrfachauswahl. `$configId`, `$apiUrl` und optional `$project` konfigurieren die Web Component. Das zugehörige Frontend muss separat verfügbar sein. |
| `renderRadioFieldFlex(string $fieldName, array $buttons, string $infoText = '', string $readOnly = '', string $labelOverride = '')` | Erstellt Radio-Optionen aus `Wert => Text`; im gesperrten Zustand oder bei `$readOnly === 'readonly'` erscheint die ausgewählte Beschriftung als Text. |
| `renderCheckboxFieldFlex(string $fieldName, string $text, string $infoText = '', string $readOnly = '', string $labelOverride = '')` | Erstellt eine Checkbox mit dem Formularwert `Y`. Als aktiv gelten `Y`, `true`, `1` und `'1'`. Im Anzeigezustand wird „Ja“ oder „Nein“ mit dem Erklärungstext ausgegeben. |
| `renderDatePickerFieldFlex(string $fieldName, string $infoText = '', string $fieldAttr = '', string $readOnly = '', string $labelOverride = '')` | Erstellt ein `<input type="date">`; im gesperrten Zustand wird der Wert als Text angezeigt. |
| `renderFileUploadFieldFlex(string $fieldName, string $infoText = '', string $fieldAttr = '', string $readOnly = '')` | Erstellt ein Datei-Input. Im gesperrten Zustand wird „Dateiupload deaktiviert“ ausgegeben. |

Bei Feld-Renderern werden vorhandene Fehlermeldungen neben dem Feld angezeigt.
Ein Feld mit Fehler erhält üblicherweise zusätzlich die CSS-Klasse
`danger-border`; optionale Hinweisstexte erscheinen mit `.hint`.

Die meisten Feldwerte, Labels, Optionswerte und Optionsbeschriftungen werden
HTML-escaped. Die Parameter `$fieldAttr` und `$readOnly` sind dagegen
vorgesehene HTML-Attributfragmente und werden als Markup eingefügt. Dort dürfen
keine ungeprüften Benutzereingaben eingesetzt werden.

### Validierung

Die Klasse enthält einzelne Validatoren und eine Regelset-Methode:

| Methode | Prüfung |
|---|---|
| `validateTextField(string $fieldName, bool $required = false, ?string $pattern = null)` | Pflichtfeld, maximale Länge aus den Metadaten und optional ein regulärer Ausdruck. |
| `validateNumberField(string $fieldName, bool $required = false, $min = null, $max = null)` | Pflichtfeld, numerischer Wert sowie optionale Unter- und Obergrenze. |
| `validateSelectField(string $fieldName, array $allowedValues, bool $required = false)` | Pflichtauswahl und Zugehörigkeit zu einer streng verglichenen Werteliste. |
| `validateCheckboxField(string $fieldName)` | Akzeptiert leeren Wert, `Y`, `1` oder `true` (jeweils als String bzw. geeigneter Typ); andere Werte werden abgelehnt. |
| `validateDateField(string $fieldName, bool $required = false, ?string $format = 'Y-m-d')` | Prüft Pflichtfeld und Datum durch Formatierung und Rückvergleich mit dem Eingabewert. |
| `validateFileUploadField(string $fieldName, ?array $allowedMimeTypes = null, ?int $maxFileSize = null, bool $required = false)` | Prüft `$_FILES`, Upload-Fehler, optionale Dateigröße und optionalen MIME-Typ. |
| `validateAll(array $validationRules)` | Leert zuerst die bisherigen Fehlermeldungen und wendet die Regeln der angegebenen Felder an. Liefert `true`, wenn alle unterstützten Regeln gültig sind. |
| `getErrorMessages(): array` | Gibt die aktuell gespeicherten Meldungen nach Feldnamen zurück. |
| `getErrorCount(): int` | Gibt den Fehlerzähler zurück, den `validateAll()` zuletzt gesetzt hat. |

Ein Regelset kann beispielsweise so aufgebaut sein:

```php
$valid = $forms->validateAll([
    'name' => [
        'type' => 'text',
        'required' => true,
        'pattern' => '/^[\p{L}\s-]+$/u',
    ],
    'age' => [
        'type' => 'number',
        'min' => 0,
        'max' => 120,
    ],
    'typ' => [
        'type' => 'select',
        'allowedValues' => ['A', 'B'],
    ],
]);
```

Unterstützte `type`-Werte sind `text`, `number`, `select`, `checkbox`, `date`
und `file`. Ein nicht unterstützter Typ wird derzeit ignoriert. Für
`validateAll()` werden Fehlerzähler und Fehlermeldungen zu Beginn
zurückgesetzt. Werden einzelne Validatoren direkt aufgerufen, können sie
Fehlermeldungen setzen; der Zähler wird dadurch jedoch nicht aktualisiert.

Diese Prüfungen ersetzen keine serverseitige Fachlogik oder
Berechtigungsprüfung. Insbesondere Upload-Validierung und zulässige
Auswahlwerte müssen in den jeweiligen Verarbeitungsabläufen angemessen
abgesichert werden.

### Hinweis zu `renderTitleDataBlockFlex()`

Die aktuelle Implementierung dieser Methode verwendet im Label-Zweig die
Variable `$label`, obwohl sie an dieser Stelle nicht definiert wird. Der
Fehler ist in der Methode selbst vermerkt und kann bei Aufrufen Notice-Ausgaben
oder ein fehlerhaftes Label verursachen. Die übrigen Render-Methoden verwenden
für die Label-Erzeugung `renderLabel()`.

## `FormRendererFlex_Ext`

### Konstruktor und Schreibschutz

```php
new FormRendererFlex_Ext(
    ?TableColumnMetadata $meta = null,
    int $phase = 0,
    array $data = [],
    array $errorMsgs = [],
    bool $editProtect = false,
    string $module = ''
);
```

Die Parameter entsprechen denen von `FormRendererFlex`, sind in der
Erweiterungsklasse aber optional. Wird `$meta` übergeben, wird ein
`FormRendererFlex` als interne Basis erzeugt. Ohne `$meta` sind nur
Autocomplete-Bausteine nutzbar.

Autocomplete-Instanzmethoden werden unterdrückt, sobald
`$editProtect === true` oder `$phase !== 0` gilt: `renderAutoComp...()` liefert
dann den leeren String. Diese Schutzlogik blendet lediglich die
Autocomplete-Markups aus. Die normalen Feldmethoden werden an
`FormRendererFlex` delegiert, dessen Anzeigeverhalten ebenfalls von Phase und
Schreibschutz abhängt.

### Delegation und Zugriff auf die Basisklasse

Die Klasse implementiert `__call()`:

- Namen mit Präfix `renderAutoComp` werden auf den passenden statischen
  `autoComp...()`-Builder abgebildet. Beispielsweise wird
  `renderAutoCompStaat()` zu `autoCompStaat()`.
- Andere Methoden werden an das interne `FormRendererFlex`-Objekt
  weitergereicht.
- Ein unbekannter Autocomplete-Name löst eine `BadMethodCallException` mit
  Hinweis auf den unbekannten Baustein aus.
- Ein unbekannter delegierter Methodenname löst eine
  `BadMethodCallException` aus.

`base(): FormRendererFlex` liefert das umhüllte Basisobjekt, etwa wenn eine
Funktion ausdrücklich einen `FormRendererFlex`-Typ verlangt. Wurde die
Erweiterung ohne Metadaten konstruiert, wirft `base()` eine
`LogicException`.

### Autocomplete-Bausteine

Die statischen `autoComp...()`-Methoden geben HTML zurück und besitzen keinen
Schreibschutz. Sie können daher auch ohne Objekt verwendet werden. Die
Instanzvarianten `renderAutoComp...()` wenden zusätzlich den oben
beschriebenen Schreibschutz an.

| Baustein | Erzeugte Felder und Parameter |
|---|---|
| `autoCompArchOrd()` / `renderAutoCompArchOrd()` | Archivordnung: sichtbares `aord`, verborgenes `aord_id`. |
| `autoCompAbk(string $group = '')` / `renderAutoCompAbk(...)` | Abkürzung: `abk` und `abk_id`; `$group` wird als `data-group` ausgegeben. |
| `autoCompBenutzer()` / `renderAutoCompBenutzer()` | Benutzer-Kurzzeichen: `benutzer` und `ben_id`. |
| `autoCompBdld(string $fieldId = 'bdld', string $fieldName = 'bdld', string $value = '')` / `renderAutoCompBdld(...)` | Bundesland mit konfigurierbarer sichtbarer ID, Name und Vorbelegung; versteckte ID bleibt `bdld_id`. Das Element verweist per `data-state-id` auf `staat_id`. |
| `autoCompEigent($t, bool $cl = false, $j = '1')` / `renderAutoCompEigent(...)` | Älterer Eigentümer-/Urheber-Baustein. `$t === 'E'` beschriftet das Feld als Eigentümer, sonst als Urheber. `$j` wird auf Buchstaben, Ziffern, `_` und `-` begrenzt und dient als Suffix der Feld-IDs. `$cl` fügt einen Weiter-Button hinzu. |
| `autoCompFirma(string $funkt = 'F')` / `renderAutoCompFirma(...)` | Firma: `firm` und `firm_id`. Für `$funkt` werden nur `A` und `F` übernommen; andere Werte werden als leerer Wert ausgegeben. |
| `autoCompAufbau(string $funkt = 'A')` / `renderAutoCompAufbau(...)` | Aufbauhersteller: `aufb` und `aufb_id`; `$funkt` wird wie bei der Firmen-Auswahl auf `A` oder `F` begrenzt. |
| `autoCompMandant(string $mode = 'selection', string $fieldId = 'mandant', string $fieldName = 'mandant', string $value = '', string $hiddenFieldName = 'mandant_id')` / `renderAutoCompMandant(...)` | Mandantensuche mit konfigurierbaren sichtbaren Feldnamen, Wert und Hidden-Feldname. Die ID des Hidden-Felds wird aus `$fieldId` plus `_id` gebildet. Bei `$mode === 'toggle'` wird das Suchfeld hinter einem Umschalter verborgen; sonst ist es direkt sichtbar. |
| `autoCompMitgl($em)` / `renderAutoCompMitgl(...)` | Mitgliedsauswahl mit `mitgl`, `mitgl_id` und `email_id`; `$em` wird als Wert des sichtbaren E-Mail-Felds ausgegeben. |
| `autoCompOrt()` / `renderAutoCompOrt()` | Ortsauswahl: `ort` und `ort_id`. |
| `autoCompSammlg()` / `renderAutoCompSammlg()` | Sammlungsauswahl: `sammlg` und `sammlg_id`. |
| `autoCompStaat(string $fieldId = 'staat', string $fieldName = 'staat', string $value = '')` / `renderAutoCompStaat(...)` | Staatsauswahl mit konfigurierbarem sichtbarem Feld und Vorbelegung; Hidden-Feld bleibt `staat_id`. |
| `autoCompUrheber(string $mode = 'selection', string $fieldId = 'urheber', string $fieldName = 'urheber', string $value = '', string $hiddenFieldName = 'urheber_id', string $hiddenNameField = 'urheber_name')` / `renderAutoCompUrheber(...)` | Urheberauswahl mit konfigurierbaren sichtbaren und versteckten Feldern. Bei `$mode === 'toggle'` wird ein Umschalter ausgegeben, sonst bleibt das Suchfeld sichtbar. |

Die sichtbaren Textfelder und zugehörigen Hidden-Felder sind aufeinander
abgestimmt, aber die Klasse validiert nicht, ob die eingegebene Bezeichnung
tatsächlich ausgewählt wurde. Die jeweilige JavaScript-/API-Logik muss die
Hidden-Felder setzen; serverseitiger Code muss eingehende IDs prüfen.

### Verwendung

#### Normale Formularfelder mit Autocomplete-Erweiterung

```php
use FWHist\Services\FormRendererFlex_Ext;

$forms = new FormRendererFlex_Ext(
    $meta,
    $phase,
    $data,
    $errorMessages,
    $editProtect,
    $module
);

echo $forms->renderTextLikeFieldFlex('ei_name', 60);
echo $forms->renderAutoCompStaat();
```

Die Parameter für `$meta`, `$phase`, `$data` und den Schreibschutz entsprechen
denen der Basisklasse. Das Formular muss die benötigten Autocomplete-Skripte
selbst einbinden.

#### Nur ein Autocomplete-Baustein, ohne Formular-Metadaten

```php
echo FormRendererFlex_Ext::autoCompMandant(
    'toggle',
    'mandant',
    'mandant',
    '',
    'mandant_id'
);
```

In dieser Variante wird keine Renderer-Instanz und kein `TableColumnMetadata`
benötigt. Sie ist für einzelne Auswahlbausteine außerhalb eines
`FormRendererFlex`-Formulars gedacht.

#### Basisobjekt an eine Funktion weitergeben

```php
$baseRenderer = $forms->base();
```

Das funktioniert nur, wenn die Erweiterung mit `TableColumnMetadata` erzeugt
wurde.

## Wartungshinweise

- Die Basis-Klasse und die Erweiterung sollten getrennte Verantwortlichkeiten
  behalten: allgemeine Felder und Validierung in `FormRendererFlex`,
  anwendungsspezifische Autocomplete-Bausteine in `FormRendererFlex_Ext`.
- Änderungen an IDs, `name`-Attributen oder `data-*`-Attributen der
  Autocomplete-Markups müssen mit den zugehörigen JavaScript-Dateien und der
  serverseitigen Verarbeitung abgestimmt werden.
- Statische Autocomplete-Builder ignorieren den Instanz-Schreibschutz.
  In geschützten Formularen daher die Instanzmethode
  `renderAutoComp...()` verwenden oder die statische Ausgabe selbst
  kontrollieren.
- HTML-Attribute, die als Fragmente wie `$fieldAttr` übergeben werden, müssen
  aus vertrauenswürdigen bzw. sicher erzeugten Werten bestehen.
