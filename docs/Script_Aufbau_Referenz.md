# Referenz: Aufbau einer FWHist-Script-Datei

Dieses Dokument beschreibt den funktionierenden Aufbau, den wir in den Login-/Controller-Dateien konsistent verwenden sollten.

## Grundprinzip

Jede Script-Datei soll in dieser Reihenfolge aufgebaut sein:

1. Session starten
2. Session-Struktur absichern
3. Root-Pfad bestimmen
4. Composer-Autoload laden
5. Klassen importieren
6. Zugriff prüfen
7. Services laden
8. Daten für Template vorbereiten
9. Template rendern oder HTML ausgeben

## Muster

```php
<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$_SESSION['BS_Prim'] ??= [];

$roBase = $_SESSION['BS_Prim']['RoBase'] ?? dirname(__DIR__, 2) . DIRECTORY_SEPARATOR;
$_SESSION['BS_Prim']['RoBase'] = $roBase;

if (!isset($_SESSION['BS_Prim']['BE'])) {
    $basePath = $_SESSION['BS_Prim']['Env']['basePath'] ?? '';
    $redirect = $basePath !== '' ? $basePath . '/FWHist/' : '../index.php';
    header("Location: $redirect");
    exit;
}

require_once $roBase . 'vendor/autoload.php';

use FWHist\Core\EnvSessionManager;
use League\Plates\Engine;

$envManager = new EnvSessionManager();
$basePath = $envManager->getEnv('basePath') ?? '';
$path2ROOT = $roBase;

require $roBase . 'login/Services/FuncsLib.php';
require $roBase . 'login/Services/CommFuncsLib.php';
require $roBase . 'login/Services/ConstLib.php';

$module = 'ADM';
$subModule = 'Beispiel';
$_SESSION['BS_Prim']['Mod'] = [
    'module' => $module,
    'smod' => $subModule,
    'caller' => $module,
];

if (!userHasRole('ADM-MI')) {
    echo '<p>Keine Berechtigung.</p>';
    exit;
}

$templates = new Engine(__DIR__ . '/../templates');
$data = [
    'title' => 'Beispiel-Seite',
    'debug' => false,
    'path2ROOT' => $path2ROOT,
    'cssBundles' => ['base', 'menu'],
];

echo $templates->render('pages/main', $data);
```

## Wichtige Regeln

### 1. Keine Ausgabe vor `header()`

Dateien dürfen vor einem Redirect keine HTML-Ausgabe erzeugen. Das gilt vor allem für:

- `echo "<!Doctype html>";`
- `var_dump($_SESSION);`
- `var_dump($data);`

Wenn `header()` gebraucht wird, muss diese Ausgabe vorher erfolgen.

### 2. Kein `header('location: $bPath/...')` mit einfachen Anführungszeichen

Dieses Muster ist fehlerhaft, weil in einfachen Anführungszeichen Variablen nicht interpoliert werden:

```php
header('location: $bPath/fharch-oop/VFH/');
```

Richtig ist:

```php
$redirect = $bPath !== '' ? $bPath . '/FWHist/' : '../index.php';
header("Location: $redirect");
```

### 3. `RoBase` immer aus der Session sichern

Verhindert fehlerhafte Pfade wie:

- `/vendor/autoload.php` statt `/FWHist/vendor/autoload.php`
- `login/Core/Services/...` statt `login/Services/...`

### 4. Composer-Autoload vor Klassen-Nutzung laden

Wenn eine Klasse wie `FWHist\Core\EnvSessionManager` benutzt wird, muss vorher `vendor/autoload.php` eingebunden sein.

### 5. Routen-/Module-Informationen sauber setzen

```php
$_SESSION['BS_Prim']['Mod'] = [
    'module' => $module,
    'smod' => $subModule,
    'caller' => $module,
];
```

## Verwendete Basisstruktur im Projekt

- Session: `$_SESSION['BS_Prim']`
- Root: `$_SESSION['BS_Prim']['RoBase']`
- Benutzer: `$_SESSION['BS_Prim']['BE']`
- Rollen: `$_SESSION['BS_Prim']['BE']['roles']`
- Env: `$_SESSION['BS_Prim']['Env']`

## Referenzdatei

Das funktionierende Beispiel findet sich in:

- [FWHist/login/Core/Beispiel.php](../FWHist/login/Core/Beispiel.php)

## Empfehlung

Beim Aufbau neuer Script-Dateien immer diesen Ablauf übernehmen. Dadurch bleiben Pfade, Rollenchecks und Template-Renderings konsistent und die Menüs funktionieren zuverlässig.
