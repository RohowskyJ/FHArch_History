# Plates-Template-Parameter und ihre Nutzung

Dieses Dokument beschreibt, wie Daten aus Einstiegsscripts und Controllern an Plates-Templates übergeben und dort weitergereicht werden. Als Beispiele dienen die Startseite und das zentrale Verwaltungsmenü.

## 1. Der Datenfluss

Ein typischer Seitenaufruf besteht aus diesen Schritten:

1. Ein Einstiegsscript oder Controller bereitet Werte in einem assoziativen Array `$data` vor.
2. `Engine::render()` rendert ein Seitentemplate und stellt die Einträge daraus als PHP-Variablen bereit.
3. Das Seitentemplate kann Werte mit `$this->layout()` an das gemeinsame Layout weitergeben.
4. Das Layout kann wiederverwendbare Teilbereiche mit `$this->insert()` aufrufen und diesen eigene Parameter geben.
5. Das Partial erzeugt daraus die konkrete HTML-Ausgabe.

Als Übersicht:

```text
Controller: $data
    -> render('pages/home', $data)
        -> pages/home.php
            -> layout('layout', [...])
                -> layout.php
                    -> insert('partials/header', [...])
                        -> partials/header.php
```

Jede Stufe sollte nur die Werte erhalten, die sie benötigt. Insbesondere sollte nicht angenommen werden, dass ein Wert automatisch in jedem Layout oder Partial verfügbar ist.

## 2. Parameter beim Rendern festlegen

Die Engine wird auf ein Template-Verzeichnis gesetzt. In `FWHist/index.php` ist das Verzeichnis `FWHist/login/templates`:

```php
$templates = new Engine(__DIR__ . '/login/templates');
```

Danach wird ein Seitentemplate mit seinem Namen und den Daten gerendert:

```php
$data = [
    'title' => 'Start-Seite',
    'name' => $_SESSION['BS_Prim']['titel'],
    'logo' => $_SESSION['BS_Prim']['logo'],
    'debug' => $debug,
    'cssBundles' => ['base', 'menu'],
    'has' => [
        'museen' => is_file($RoBase . 'login/Modules/Oeffentlichkeitsarbeit/MuseenList.php'),
    ],
];

echo $templates->render('pages/home', $data);
```

Der Name `pages/home` verweist auf `login/templates/pages/home.php`. Jeder Schlüssel im Array wird dort als Variable mit demselben Namen verfügbar:

| Schlüssel in `$data` | Variable im Template | Bedeutung im Beispiel |
| --- | --- | --- |
| `title` | `$title` | Seitentitel |
| `name` | `$name` | Vereinsname aus der Konfiguration |
| `logo` | `$logo` | Bilddateiname aus `Config.sign` |
| `debug` | `$debug` | Debug-Schalter |
| `cssBundles` | `$cssBundles` | Liste der einzubindenden CSS-Dateien |
| `has` | `$has` | Array mit Verfügbarkeitsprüfungen |

Die Zuordnung verwendet die Schlüsselnamen exakt. Zum Beispiel ist `logo` nicht dasselbe wie `Logo`.

## 3. Einzelwerte und verschachtelte Arrays

Einfache Werte werden direkt verwendet:

```php
<h1><?= $this->e($title) ?></h1>
```

Bei verschachtelten Arrays wird der passende Schlüssel ausgewählt. Für `$has` könnte ein Template beispielsweise so prüfen, ob ein Modul existiert:

```php
<?php if (!empty($has['museen'])): ?>
    <a href="...">Museen</a>
<?php endif; ?>
```

`$has['museen']` ist hier ein boolescher Wert aus `is_file()`. Der Dateisystem-Check gehört in den Controller beziehungsweise das Einstiegsscript; das Template entscheidet nur, was abhängig vom Ergebnis angezeigt wird.

## 4. Parameter an das Layout weitergeben

Das Seitentemplate `pages/home.php` bekommt beim `render()` unter anderem `$title`, `$name`, `$logo` und `$cssBundles`. Es gibt einen Teil dieser Werte explizit an das Layout weiter:

```php
$this->layout('layout', [
    'title' => $title,
    'name' => $name,
    'logo' => $logo,
    'cssBundles' => $cssBundles,
    'systemBasePath' => $systemBasePath,
]);
```

Das zweite Argument von `layout()` ist wieder ein assoziatives Parameter-Array. Die darin enthaltenen Werte stehen im Layout unter denselben Variablennamen zur Verfügung. Diese explizite Übergabe macht die Abhängigkeiten sichtbar und ist besonders wichtig, wenn Layout oder Partial einen Wert benötigt.

Ein Parameter, der nur im Seitentemplate verwendet wird, muss nicht ans Layout weitergereicht werden. So ist `$has` für die Navigation und Sponsoren relevant, aber nicht zwingend für den Header.

## 5. Session-Fallback im Layout

Nicht alle Seiten übergeben jeden Parameter einzeln. Verwaltungsseiten wie `pages/main.php` oder `pages/zentrVerw.php` übergeben zum Beispiel üblicherweise Titel und CSS-Bundles. Das gemeinsame `layout.php` verwendet deshalb für Vereinsname und Logo einen Session-Fallback:

```php
$name = $name ?? ($_SESSION['BS_Prim']['titel'] ?? 'Feuerwehr - Archiv');
$logo = $logo ?? ($_SESSION['BS_Prim']['logo'] ?? '');
```

Die Reihenfolge ist:

1. Verwende den Parameter `$name` beziehungsweise `$logo`, wenn er übergeben wurde.
2. Andernfalls verwende den Wert aus der Session.
3. Wenn auch dieser fehlt, verwende den angegebenen Standardwert.

Der `??`-Operator prüft auf `null` beziehungsweise nicht gesetzte Werte. Ein leerer String gilt dabei als vorhanden. Soll auch ein leerer String als „nicht gesetzt“ gelten, ist `!empty()` eine mögliche Alternative.

## 6. Logo vom Dateinamen zur Browser-URL

`Config.sign` enthält in der Konfiguration nur den Bilddateinamen, zum Beispiel `Lehner_Dauber_Signet_s_72.jpg`. Das Layout formt daraus eine URL innerhalb von `FWHist/imgs`:

```php
$logoFile = basename(str_replace('\\', '/', (string) ($logo ?? '')));
$logoUrl = $logoFile !== ''
    ? $publicRoot . '/imgs/' . rawurlencode($logoFile)
    : '';
```

Dabei gilt:

- `$logo` ist der konfigurierte Dateiname, kein Dateisystempfad und keine vollständige URL.
- `basename()` begrenzt den Wert auf einen Dateinamen.
- `rawurlencode()` kodiert den Dateinamen für die URL.
- `$publicRoot` berücksichtigt den öffentlichen Projektpfad.
- Ist kein Logo konfiguriert, bleibt `$logoUrl` leer.

Das Layout reicht die berechnete URL an den Header weiter:

```php
$this->insert('partials/header', [
    'title' => $title,
    'name' => $name,
    'logoUrl' => $logoUrl,
]);
```

Im Header-Partial ist damit `$logoUrl` verfügbar. Die Ausgabe wird nur erzeugt, wenn eine URL vorhanden ist:

```php
<?php if ($logoUrl !== ''): ?>
    <img src="<?= $this->e($logoUrl) ?>" alt="<?= $this->e($name) ?>">
<?php endif; ?>
```

`$this->e()` escaped Werte für die HTML-Ausgabe. Dynamische Texte und Attribute sollten grundsätzlich escaped ausgegeben werden.

## 7. Parameter an Partials weitergeben

Ein Partial wird mit `$this->insert()` eingebunden. Die Variablen darin sollten im Parameter-Array ausdrücklich übergeben werden:

```php
$this->insert('partials/nav', [
    'has' => $has,
    'systemBasePath' => $systemBasePath,
    'debug' => $debug,
]);
```

Das Partial erhält hier `$has`, `$systemBasePath` und `$debug`. Wird ein benötigter Parameter nicht mitgegeben, sollte das Partial nicht davon ausgehen, dass er trotzdem vorhanden ist. Für optionale Werte kann es einen Standardwert setzen, zum Beispiel:

```php
$debug = $debug ?? false;
```

## 8. Beispiel: Script-Verfügbarkeit im Verwaltungsmenü

`Controllers/ZentralVerw.php` prüft mit `is_file()`, ob das Mitglieder-Script installiert ist, und legt das Ergebnis in `$data['has']` ab:

```php
$data = [
    'title' => 'Zentrale- Verwaltungs- Seite',
    'systemBasePath' => $systemBasePath,
    'cssBundles' => ['base', 'menu'],
    'has' => [
        'mitgliedDsvgo' => is_file(
            $systemBasePath . 'login/Modules/Mitglieder/DsvgoMiMail.php'
        ),
    ],
];

echo $templates->render('pages/zentrVerw', $data);
```

In `pages/zentrVerw.php` wird abhängig von diesem booleschen Parameter ein Link gewählt:

```php
<a href="<?= !empty($has['mitgliedDsvgo'])
    ? '../Modules/Mitglieder/DsvgoMiMail.php'
    : '../AllgVerw/DsvgoBeMail.php' ?>">
    DSVGO Information abrufen
</a>
```

Das Template enthält damit keine Dateisystemlogik. Es erhält nur die Information, ob das Script verfügbar ist, und entscheidet über die passende Darstellung beziehungsweise Zieladresse.

## 9. Gute Konventionen

- Schlüssel in `$data` und die zugehörigen Templatevariablen gleich benennen.
- Für Template-Namen eine klare Struktur wie `pages/home` verwenden.
- Werte an Layouts und Partials ausdrücklich mit `layout()` beziehungsweise `insert()` weiterreichen.
- Dateisystempfade und Browser-URLs getrennt behandeln: `is_file()` prüft einen Serverpfad; `href` und `src` benötigen URLs.
- Optionale Werte im Template mit `??` oder `!empty()` absichern.
- Dynamische HTML-Texte und Attribute mit `$this->e()` escapen.
- Controller bereiten Daten und Verfügbarkeitsflags vor; Templates entscheiden über Darstellung und einfache Bedingungen.
- Parameternamen sind case-sensitive und müssen auf allen Übergabestufen übereinstimmen.

## 10. Projektbeispiele

- Einstiegsscript und Datenarray: [FWHist/index.php](../FWHist/index.php)
- Startseiten-Template: [FWHist/login/templates/pages/home.php](../FWHist/login/templates/pages/home.php)
- Gemeinsames Layout: [FWHist/login/templates/layout.php](../FWHist/login/templates/layout.php)
- Header-Partial: [FWHist/login/templates/partials/header.php](../FWHist/login/templates/partials/header.php)
- Zentraler Verwaltungscontroller: [FWHist/login/Controllers/ZentralVerw.php](../FWHist/login/Controllers/ZentralVerw.php)
- Zentrales Verwaltungsmenü: [FWHist/login/templates/pages/zentrVerw.php](../FWHist/login/templates/pages/zentrVerw.php)
