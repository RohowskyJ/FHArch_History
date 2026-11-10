# Pfadlogik und CSS-URLs im Projekt

## 1) Unterschied zwischen Localhost und ISP

Im Projekt gibt es zwei verschiedene Deploy-Szenarien:

- Localhost: Das Web-DocumentRoot liegt in der Regel auf `C:/JProgs/htdocs/Feuerwehr_Archiv`.
- ISP / Produktivsystem: Das DocumentRoot ist meist das Hauptverzeichnis des Webservers, und das Projekt liegt darin als Unterordner `FWHist`.

Das bedeutet:

- Lokal: `http://localhost/Feuerwehr_Archiv/FWHist/index.php`
- ISP: `https://example.com/FWHist/index.php`

Der wichtige Punkt ist: Der PHP-Code arbeitet mit einem virtuellen Web-Pfad, nicht mit dem realen Dateisystempfad auf der Platte.

Das Projekt selbst liegt auf der Platte in:

- `C:/JProgs/htdocs/Feuerwehr_Archiv/FWHist`

Die CSS-Dateien liegen dort also nicht in `VFH/css`, sondern in:

- `FWHist/css/`

---

## 2) Wo die CSS-Bundles definiert werden

Der Startpunkt ist [FWHist/index.php](../FWHist/index.php):

```php
$data = [
    'title' => 'Start-Seite',
    'debug' => $debug,
    'path2ROOT' => $basePath . "/FWHist/" ,
    'configOk' => $configOk,
    'SI' => $SI,
    'cssBundles' => ['base', 'menu'],
];
```

Die Bundles werden hier als Liste übergeben, z. B. `['base', 'menu']`.

---

## 3) Wo der HTML-Link tatsächlich erzeugt wird

Die Ausgabe erfolgt im Layout-Template [FWHist/login/templates/layout.php](../FWHist/login/templates/layout.php):

```php
<?php if (!empty($cssBundles)): ?>
    <?php foreach ($cssBundles as $bundle): ?>
        <link rel="stylesheet" href="<?= $basePath ?>/VFH/css/<?= $bundle ?>.css" />
    <?php endforeach; ?>
<?php endif; ?>
```

Das ist der entscheidende Fehler:

- `basePath` ist lokal oft leer
- dann entsteht der Link `/VFH/css/base.css`
- in der Realität liegen die Dateien aber unter `/FWHist/css/base.css`

Es wird also ein historischer Pfad `VFH` verwendet, obwohl das Verzeichnis heute `FWHist` heißt.

---

## 4) Warum der Fehler genau so aussieht

Im Browser erscheint dann z. B.:

```text
GET http://localhost/feuerwehr_archiv/VFH/css/base.css -> 404
GET http://localhost/feuerwehr_archiv/VFH/css/menu.css -> 404
```

Der Grund ist:

- der Code erwartet `/VFH/css/...`
- aber das echte Verzeichnis ist `/FWHist/css/...`
- und der Relative-/Absolute-Pfad muss immer zum realen Einstiegspunkt passen

Außerdem gilt auf vielen Servern:

- Linux/Apache: Pfade sind case-sensitive
- `VFH` und `FWHist` sind nicht identisch
- `feuerwehr_archiv` und `Feuerwehr_Archiv` können unterschiedlich sein

---

## 5) Richtige Logik

Die URL sollte nicht hart auf `VFH` festgelegt sein, sondern auf den realen öffentlichen Stamm des Projekts.

Wenn das Projekt unter `/FWHist` läuft, dann muss der Pfad wie folgt sein:

```php
$publicRoot = rtrim($basePath . '/FWHist', '/');
<link rel="stylesheet" href="<?= $publicRoot ?>/css/<?= $bundle ?>.css" />
```

Damit ergibt sich lokal:

- `http://localhost/Feuerwehr_Archiv/FWHist/css/base.css`

und auf dem ISP:

- `https://example.com/FWHist/css/base.css`

Falls das Projekt einmal direkt im DocumentRoot stehen sollte, dann wäre der Pfad statt `/FWHist` nur `/css/...`.

---

## 6) Kurz gesagt

Das Problem ist kein CSS-Problem, sondern ein Pfad-Problem:

- in [FWHist/index.php](../FWHist/index.php) wird `cssBundles` gesetzt
- in [FWHist/login/templates/layout.php](../FWHist/login/templates/layout.php) wird daraus der Link erzeugt
- der Link verwendet aber hartkodiert `/VFH/css/...`
- die Dateien liegen aber in `/FWHist/css/...`

Das ist die Ursache für die 404-Fehler in der Console.

---

## 7) Empfohlene Korrektur

Die Sauberste Lösung ist, den öffentlichen Basis-Pfad aus der Umgebung bzw. aus der aktuellen Request-Struktur zu berechnen und nur einmal zentral zu verwenden.

Beispiel:

```php
$publicRoot = trim(($basePath ?? '') . '/FWHist', '/');
$publicRoot = $publicRoot !== '' ? '/' . $publicRoot : '';
```

Dann:

```php
<link rel="stylesheet" href="<?= $publicRoot ?>/css/<?= $bundle ?>.css" />
```

Damit ist das System unabhängig davon, ob das Projekt:

- unter `/FWHist` läuft,
- direkt im Root liegt,
- oder lokal als Unterverzeichnis von einem größeren DocumentRoot startet.


## Vergleich css und Bundles:

Ich vergleiche jetzt die CSS-Referenzen im Page-Template mit der tatsächlichen Base-Stylesheet-Struktur, damit wir den Unterschied zwischen “gewünschter Layout-Logik” und “realem Dateisystem-Pfad” sauber sehen.




Vergleich: CSS-Auswahl in main.php vs. base.css
Der Wichtige Punkt zuerst:

In main.php gibt es keine eigenen <style>-Blöcke.
Die Seite verwendet nur Klassennamen wie .Menu-Header, .Menu-Separator, .Menu-Line.
Diese Klassen werden aus externen CSS-Dateien geladen, insbesondere aus menu.css und base.css.
1) Was in main.php verwendet wird
Die Seite nutzt vor allem diese Klassen:

.Menu-Header
.Menu-Separator
.Menu-Line
Beispiel:


Das bedeutet: Das Design für diese Blöcke kommt aus dem Stylesheet mit den Menu-Klassen, also aus menu.css.

2) Was in base.css definiert ist
HWHist/css/base.css enthält allgemeine, wiederverwendbare UI-Styles, nicht die Menüstruktur:

.panel
.panel .hd
.panel .bd
.btn
.btn.primary
.reveal
Das sind eher generische Komponenten, z. B.:


Das ist also eher “Basis-Layout / allgemeine Oberfläche”, nicht der konkrete Menü-Stil.

3) Was in menu.css definiert ist
HWHist/css/menu.css ist der relevante Teil für die Seite aus main.php:


Das passt exakt zu den HTML-Klassen aus der Template-Seite.

4) Fazit
menu.css = spezielle Formatierung für die Menü-/Startseiten-Ansicht
base.css = allgemeine Basis-Styles
main.php nutzt überwiegend Menü-Klassen, nicht .panel oder .btn
Der eigentliche Fehler liegt also nicht an einer falschen CSS-Klasse im Template, sondern an dem fehlerhaften URL-Pfad, mit dem die CSS-Dateien eingebunden werden. Die Klassen sind korrekt; die Datei wird nur nicht gefunden.

Wenn du willst, mache ich dir als Nächstes noch einen sauberen “Pfadvergleich” zwischen:

layout.php
index.php
base.css
und zeige genau, wie der Link href="/VFH/css/base.css" in den korrekten Pfad umgebaut werden muss.
