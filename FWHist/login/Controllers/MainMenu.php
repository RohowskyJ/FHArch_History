<?php
declare(strict_types=1);
/**
 * Internes Hauptmenu
 */

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

$systemBasePath = $_SESSION['BS_Prim']['RoBase'];
require $systemBasePath . '/vendor/autoload.php';

use FWHist\Auth\AccessGuard;
use FWHist\Core\EnvSessionManager;
use FWHist\Database\DB_GenericLog;
$envManager = new EnvSessionManager();

$_SESSION['BS_Prim'] ??= [];

// Shutdown-Funktion direkt am Anfang registrieren
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error !== null) {
        $message = "Shutdown error detected:\n" . print_r($error, true);
        error_log($message);
        // Optional: auch in eine separate Datei schreiben
        file_put_contents(__DIR__ . '/Menu_fatal_error.log', $message, FILE_APPEND);
    }
});
    
error_reporting(E_ALL);
ini_set('display_errors', '0');   // im Betrieb keine Fehlermeldungen an den Browser
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/Menu_php-error.log.txt');
    
$module = 'VFH';
$sub_module = 'IntStart';

# var_dump($_SERVER);

$_SESSION['BS_Prim']['Mod'] = ['module' => $module, 'smod' => $sub_module, 'caller' => $module];

// Rechte des angemeldeten Benutzers (frisch aus der Datenbank); ohne gültigen Benutzer zurück zur Startseite
$guard = AccessGuard::fromSession(new DB_GenericLog());

if (!isset($_SESSION['BS_Prim']['BE']) || !$guard->isLoggedIn()) {
    
    $bPath = $_SESSION['BS_Prim']['Env']['basePath'] ?? '';
    $redirect = $bPath !== '' ? $bPath . '/FWHist/' : '../../index.php';
    header("Location: $redirect");
    exit;
}

use League\Plates\Engine;
# var_dump($_SESSION);
$debug = false;


# echo __LINE__ . " basepath $basePath <bR>";
# var_dump($_SESSION);// Pfad anpassen je nach Projektstruktur
require_once dirname(__DIR__, 2) . '/login/Services/AppLogger.php';

AppLogger::info('Main- Menu gestartet');

$templates = new Engine(__DIR__ . '/../templates'); 

$data = [
    'title' => 'Haupt- Auswahl- Seite',
    'debug' => $debug,
    'systemBasePath' =>  $systemBasePath,
    'configOk' => $configOk ?? false,
    'SI' => $SI ?? null,
    'cssBundles' => ['base', 'menu'],
    /** Sichtbarkeit von Menüpunkten nach Rolle (AccessGuard::menuFlags); die Menüs der Module bleiben für alle sichtbar */
    'can' => $guard->menuFlags(),
    /**
     * Prüfen,  ob Module installiert, wenn JA, dann Anzeige 
     */
    'has' => [
        'Suchen' => is_file($systemBasePath. 'login/Modules/Suchen/Suchen.php'),
        'FzgGer' => is_file($systemBasePath. 'login/Modules/FahrzeugeGeraete/FzgGerVerw.php'),
        'Oeffi' => is_file($systemBasePath. 'login/Modules/Oeffentlichkeitsarbeit/OeffiVerw.php'),
        'PSA' => is_file($systemBasePath. 'login/Modules/Psa/PsaVerw.php'),
        'Archiv' => is_file($systemBasePath. 'login/Modules/Archivalien/ArchivVerw.php'),
        'Doku' => is_file($systemBasePath. 'login/Modules/Oeffentlichkeitsarbeit/DokuVerw.php'),
        'Media' => is_file($systemBasePath. 'login/Modules/Medien/MedienVerw.php'),
        'Inventar' => is_file($systemBasePath. 'login/Modules/Inventar/InventarVerw.php'),
        'Aerm' => is_file($systemBasePath. 'login/Modules/Psa/OrtsAuswList.php'),
        'mitgliedDsvgo' => is_file($systemBasePath . 'login/Modules/Mitglieder/DsvgoMiMail.php'),
    ],
];

echo $templates->render('pages/main', $data);
exit;