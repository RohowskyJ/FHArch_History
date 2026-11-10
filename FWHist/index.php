<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$RoBase = rtrim(__DIR__, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
$RoBase = str_replace('\\', '/', $RoBase);
$_SESSION['BS_Prim']['RoBase'] = $RoBase;

// Composer Autoload + Plates
require_once $RoBase . 'vendor/autoload.php';

use FWHist\Core\EnvSessionManager;

$_SESSION['BS_Prim'] ??= [];
// Shutdown-Funktion direkt am Anfang registrieren
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error !== null) {
        $message = "Shutdown error detected:\n" . print_r($error, true);
        error_log($message);
        // Optional: auch in eine separate Datei schreiben
        file_put_contents(__DIR__ . '/INDEX_public_fatal_error.log', $message, FILE_APPEND);
    }
});
    
$module = "FWHist";
$sMod = 'Ext';

$debug = false ;

$module = 'FWHist';
$sub_module = 'index';
$_SESSION['BS_Prim']['Mod'] = ['module' => $module, 'smod' => $sub_module, 'caller' => $module];

// Standort-Indicator (wie im Original)
$host = $_SERVER['HTTP_HOST'] ?? '';
$SI = (stripos($host, 'localhost') !== false) ? 'l' : 'fwhist';

// $_SESSION['BS_Prim']['RoBase'] = $RoBase;

// Konfiguration für Seitentitel, Kontakt und Logo laden.
$configFile = $RoBase . "login/config/config_s_" . $SI . ".ini";
$parsedConfig = is_file($configFile) ? parse_ini_file($configFile, true) : false;
$config = is_array($parsedConfig) ? ($parsedConfig['Config'] ?? []) : [];
$_SESSION['BS_Prim']['titel'] = $config['inst'] ?? 'Verein für Feuerwehrhistorik';
$_SESSION['BS_Prim']['vema']  = $config['vema'] ?? '';
$_SESSION['BS_Prim']['logo']  = $config['sign'] ?? '';

// Deine Libs (wie im Original)
require $RoBase . 'login/Services/FuncsLib.php';
require $RoBase . 'login/Services/CommFuncsLib.php';
require $RoBase . 'login/Services/ConstLib.php';

# echo __FILE__ . " ".__LINE__ . ' Maximaler Speicherverbrauch: ' . memory_get_peak_usage() . ' Bytes<br>';

$envManager = new EnvSessionManager($RoBase);
#var_dump($envManager);
// Zugriff z.B. auf basePath:
$basePath = "";
if ($_SERVER['HTTP_HOST'] == 'localhost') {
    $basePath = $envManager->getEnv('basePath');
}
$systemBasePath = $envManager->getEnv('systemBasePath');
# var_dump($systemBasePath);
if ($debug) {
    var_dump($systemBasePath);
}

use League\Plates\Engine;

// Debug-Ausgabe (wie gehabt)
if ($debug) {
    initial_debug('SERV', 'PUT', 'GET');
}
# var_dump($path2ROOT . $basePath . "/FWHist/" . 'login/Modules/Oeffentlichkeitsarbeit/MuseenList.php');
$templates = new Engine(__DIR__ . '/login/templates');
#echo __FILE__ . " ".__LINE__ . ' Maximaler Speicherverbrauch: ' . memory_get_peak_usage() . ' Bytes<br>';
/** 
 * Daten / Flags fürs Template
 */
$data = [
    'title' => 'Start-Seite',
    'name'  => $_SESSION['BS_Prim']['titel'],
    'logo'  => $_SESSION['BS_Prim']['logo'],
    'debug' => $debug,
    'systemBasePath' => '', // $Robase,
    'SI' => $SI,
    'cssBundles'=>['base','menu'],
    // Links prüfen (wie im Original)
    /** 
     * Prüfen,  ob Module installiert, wenn JA, dann Anzeige Link oder Anzeige Sponsor
     */
    'has' => [
        'museen' => is_file($RoBase . 'login/Modules/Oeffentlichkeitsarbeit/MuseenList.php'),
        'archive' => is_file($RoBase . 'login/Modules/Oeffentlichkeitsarbeit/ArLinkList.php'),
        'termine' => is_file($RoBase . 'login/Modules/Oeffentlichkeitsarbeit/TerminList.php'),
        'presse' => is_file($RoBase . 'login/Modules/Oeffentlichkeitsarbeit/PresseList.php'),
        'buch' => is_file($RoBase . 'login/Modules/Oeffentlichkeitsarbeit/BuchList.php'),
        'markt' => is_file($RoBase . 'login/Modules/Oeffentlichkeitsarbeit/MarktplList.php'),
        'aerm' => is_file($RoBase . 'login/Modules/Psa/OrtsAuswList.php'),
        'sponsor_vb' => is_file(__DIR__ . '/imgs/logo_versand_blauer_hintergrund.jpeg'),
        'sponsor_jo' => is_file(__DIR__ . '/imgs/Logo_Joechlinger.jpg'),
    ],
];

if ($debug) {
    var_dump($data);
}
# echo __FILE__ . " ".__LINE__ . ' Maximaler Speicherverbrauch: ' . memory_get_peak_usage() . ' Bytes<br>';
echo $templates->render('pages/home', $data);
