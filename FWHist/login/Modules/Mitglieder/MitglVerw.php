<?php

/**
 * Menu Mitgliederverwaltung
 * 
 * @author Josef Rohowsky - neu 2026
 */

declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Prüfen, ob Session gültig ist
if (empty($_SESSION['BS_Prim'])) {
    // Session ungültig → Weiterleitung auf externe URL
    if ($_SERVER['HTTP_HOST'] == 'localhost') {
        header('Location: /localhost/Feuerwehr_Archiv/FWHist/');
        exit;
    }
    header('Location: https://www.feuerwehrhistoriker.at/FWHist/');
    exit;
}

// Projekt-/Session-Umgebung initialisieren.
$systemBasePath = $_SESSION['BS_Prim']['RoBase'];
// Composer-Autoloader, bevor eigentliche Klassen verwendet werden.
require $systemBasePath . 'vendor/autoload.php';  //  __DIR__ == C:\JProgs\htdocs\Feuerwehr_Archiv\FWHist\login\Controllers; dirname(__DIR__, 2) == C:\JProgs\htdocs\Feuerwehr_Archiv\FWHist\

use FWHist\Auth\AccessGuard;
use FWHist\Core\EnvSessionManager;
use FWHist\Database\DB_GenericLog;
use League\Plates\Engine;

// Rechte des angemeldeten Benutzers (frisch aus der Datenbank)
$guard = AccessGuard::fromSession(new DB_GenericLog());

// Sicherheits-Check: ohne gültigen Benutzer sofort zurück zur Startseite.
if (!$guard->isLoggedIn()) {
    $redirect = $systemBasePath !== '' ? $systemBasePath : '../../index.php';
    header("Location: $redirect");
    exit;
}

// Mitgliederverwaltung: nur ADM-MI und ADM-MB (ADM-ALLE immer)
if (!$guard->canSeeMemberMenu()) {
    http_response_code(403);
    echo '<p>Für diesen Bereich besteht keine Berechtigung.</p>';
    echo '<p><a href="../../Controllers/MainMenu.php">Zurück zum Hauptmenü</a></p>';
    exit;
}


// Shutdown-Funktion direkt am Anfang registrieren
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error !== null) {
        $message = "Shutdown error detected:\n" . print_r($error, true);
        error_log($message);
        // Optional: auch in eine separate Datei schreiben
        file_put_contents(__DIR__ . '/MiVerw_fatal_error.log', $message, FILE_APPEND);
    }
});
    
$module = 'MVW';
$sub_mod = 'all';

/**
 * Angleichung an den Root-Path
 *
 * @var string $systemBasePath
 */
$systemBasePath = $_SESSION['BS_Prim']['RoBase'] ?? "../../../../";



$debug = False; // Debug output Ein/Aus Schalter

require $systemBasePath . 'login/Services/FuncsLib.php';
require $systemBasePath . 'login/Services/CommFuncsLib.php';
require $systemBasePath . 'login/Services/ConstLib.php';

# var_dump($_SESSION);
$debug = false;

# $rootPfad = "/FHArch-oop"

$envManager = new EnvSessionManager();

$templates = new Engine(__DIR__ . '/../../templates'); // /src/core/templates

$data = [
    'title' => 'Mitglieder- Verwaltungs- Seite',
    'debug' => $debug,
    'systemBasePath' => $systemBasePath,
    'SI' => $SI ?? null,
    'cssBundles' => ['base', 'menu'],
    'can' => $guard->menuFlags(),   // Sichtbarkeit der Menüpunkte nach Rolle
];

echo $templates->render('pages/mitglVerw', $data);
exit;
