<?php
declare(strict_types=1);

use Fharch\Core\EnvSessionManager;

session_start();
$_SESSION = [];
echo "<!DOCTYPE html>";

// Shutdown-Funktion direkt am Anfang registrieren
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error !== null) {
        $message = "Shutdown error detected:\n" . print_r($error, true);
        error_log($message);
        // Optional: auch in eine separate Datei schreiben
        file_put_contents(__DIR__ . '/public_fatal_error.log', $message, FILE_APPEND);
    }
});
    
$module = "VFH";
$sMod = 'Ext';

$path2ROOT = "../";
$debug = true;

$module = 'VFH';
$sub_module = 'index';
$_SESSION['BS_Prim']['Mod'] = ['module' => $module, 'smod' => $sub_module, 'caller' => $module];

var_dump($_SESSION);

// Standort-Indicator (wie im Original)
$SI = ($_SERVER['HTTP_HOST'] ?? '') === 'localhost' ? 'l' : 'vfh';

// Installer-Hinweis (wie im Original, aber nicht redirecten)
$configOk =
    is_file($path2ROOT . "src/config/ConfigLib_d_$SI.php") &&
    is_file($path2ROOT . "src/config/config_s_" . $SI . ".ini");
# echo __LINE__ . "  ".$path2ROOT . "config/config_s_" . $SI . ".ini <br>";

// Deine Libs (wie im Original)
require $path2ROOT . 'login/Basis/common/BS_FuncsLib.php';
require $path2ROOT . 'login/Basis/common/FS_CommFuncsLib.php';
require $path2ROOT . 'login/Basis/common/FS_ConstLib.php';

require $path2ROOT . 'login/common/VF_Comm_Funcs.lib.php';
echo __FILE__ . " ".__LINE__ . ' Maximaler Speicherverbrauch: ' . memory_get_peak_usage() . ' Bytes<br>';


// Composer Autoload + Plates
require_once __DIR__ . '/../vendor/autoload.php';

#use envSessionManager;

$envManager = new EnvSessionManager();
// Zugriff z.B. auf basePath:
$basePath = "";
if ($_SERVER['HTTP_HOST'] == 'localhost') {
    $basePath = $envManager->getEnv('basePath');
}

var_dump($_SESSION);

use League\Plates\Engine;

// Debug-Ausgabe (wie gehabt)
if ($debug) {
    initial_debug('SERV', 'PUT', 'GET');
}

$templates = new Engine(__DIR__ . '/../src/Core/templates');
echo __FILE__ . " ".__LINE__ . ' Maximaler Speicherverbrauch: ' . memory_get_peak_usage() . ' Bytes<br>';
/** 
 * Daten / Flags fürs Template
 */
$data = [
    'title' => 'Start-Seite',
    'debug' => $debug,
    'path2ROOT' => $path2ROOT,
    'configOk' => $configOk,
    'SI' => $SI,
    'cssBundles'=>['base','menu'],
    // Links prüfen (wie im Original)
    /** 
     * Prüfen,  ob Module installiert, wenn JA, dann Anzeige Link oder Anzeige Sponsor
     */
    'has' => [
        'museen' => is_file($path2ROOT . 'src/Core/Modules/Oeffentlichkeitsarbeit/MuseenList.php'),
        'archive' => is_file($path2ROOT . 'src/Core/Modules/Oeffentlichkeitsarbeit/ArLinkList.php'),
        'termine' => is_file($path2ROOT . 'src/Core/Modules/Oeffentlichkeitsarbeit/TerminList.php'),
        'presse' => is_file($path2ROOT . 'src/Core/Modules/Oeffentlichkeitsarbeit/PresseList.php'),
        'buch' => is_file($path2ROOT . 'src/Core/Modules/Oeffentlichkeitsarbeit/BuchList.php'),
        'markt' => is_file($path2ROOT . 'src/Core/Modules/Oeffentlichkeitsarbeit/AnzeigerList.php'),
        'sponsor_vb' => is_file(__DIR__ . '/imgs/logo_versand_blauer_hintergrund.jpeg'),
        'sponsor_jo' => is_file(__DIR__ . '/imgs/Logo_Joechlinger.jpg'),
    ],
];
echo __FILE__ . " ".__LINE__ . ' Maximaler Speicherverbrauch: ' . memory_get_peak_usage() . ' Bytes<br>';
echo $templates->render('pages/home', $data);
