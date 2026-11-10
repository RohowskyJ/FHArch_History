<?php
/**
 * Zentrale Verwaltung
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

// Composer-Autoloader, bevor eigentliche Klassen verwendet werden.
require dirname(__DIR__, 2) . '/vendor/autoload.php';  //  __DIR__ == C:\JProgs\htdocs\Feuerwehr_Archiv\FWHist\login\Controllers; dirname(__DIR__, 2) == C:\JProgs\htdocs\Feuerwehr_Archiv\FWHist\

use FWHist\Auth\AccessGuard;
use FWHist\Core\EnvSessionManager;
use FWHist\Database\DB_GenericLog;
use League\Plates\Engine;

// Projekt-/Session-Umgebung initialisieren.
$envManager = new EnvSessionManager();
$systemBasePath = $_SESSION['BS_Prim']['RoBase'];

// Rechte des angemeldeten Benutzers (frisch aus der Datenbank)
$guard = AccessGuard::fromSession(new DB_GenericLog());

// Sicherheits-Check: ohne gültigen Benutzer sofort zurück zur Startseite.
if (!$guard->isLoggedIn()) {
    $redirect = $systemBasePath !== '' ? $systemBasePath : '../../index.php';
    header("Location: $redirect");
    exit;
}

// Die Zentrale Verwaltung ist nur für Rollen mit Verwaltungsaufgaben sichtbar (Referat 1 - Organisation)
if (!$guard->canSeeAdminMenu()) {
    http_response_code(403);
    echo '<p>Für diesen Bereich besteht keine Berechtigung.</p>';
    echo '<p><a href="../Controllers/MainMenu.php">Zurück zum Hauptmenü</a></p>';
    exit;
}

// Beispiel-Moduleinstellungen.
$module = 'ADM';
$subModule = 'Beispiel';
$_SESSION['BS_Prim']['Mod'] = [
    'module' => $module,
    'smod' => $subModule,
    'caller' => $module,
];

$debug = false;
// Services für gemeinsame Funktionen und Rollenprüfung einbinden.
require $systemBasePath . 'login/Services/FuncsLib.php';
require $systemBasePath . 'login/Services/CommFuncsLib.php';
require $systemBasePath . 'login/Services/ConstLib.php';

require_once $systemBasePath . 'login/Services/AppLogger.php';
AppLogger::info('Zentrale Verwaltungs- Menu gestartet');

$templates = new Engine(__DIR__ . '/../templates'); 

$data = [
    'title' => 'Zentrale- Verwaltungs- Seite',
    'debug' => $debug,
    'systemBasePath' => $systemBasePath,
    'cssBundles' => ['base', 'menu'],
    'can' => $guard->menuFlags(),   // Sichtbarkeit der Menüpunkte nach Rolle
    'has' => [
        'mitgliedDsvgo' => is_file($systemBasePath . 'login/Modules/Mitglieder/DsvgoMiMail.php'),
    ],
];
echo $templates->render('pages/zentrVerw', $data);
exit;

?>
