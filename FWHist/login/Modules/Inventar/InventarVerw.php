<?php
/** 
 * Menu Inventar- Verwaltung
 * 
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


// Shutdown-Funktion direkt am Anfang registrieren
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error !== null) {
        $message = "Shutdown error detected:\n" . print_r($error, true);
        error_log($message);
        // Optional: auch in eine separate Datei schreiben
        file_put_contents(__DIR__ . '/../../AOrd_Verz/Logging/InvVerw_fatal_error.log', $message, FILE_APPEND);
    }
});
    
$module = 'INV';
$sub_module = 'Menu';

$Zugr = "ADM-IN";

$_SESSION['BS_Prim']['Mod'] = ['module' => $module, 'smod' => $sub_module, 'caller' => $module];

if (!isset($_SESSION['BS_Prim']['BE'])) {
    $bPath = $_SESSION['BS_Prim']['Env']['basePath'] ?? '';
    $redirect = $bPath !== '' ? $bPath . '/FWHist/' : '../../index.php';
    header("Location: $redirect");
    exit;
}

require $_SESSION['BS_Prim']['RoBase'] . '/login/Services/FuncsLib.php';
require $_SESSION['BS_Prim']['RoBase'] . '/login/Services/CommFuncsLib.php';
require $_SESSION['BS_Prim']['RoBase'] . '/login/Services/ConstLib.php';


require $_SESSION['BS_Prim']['RoBase'] . '/vendor/autoload.php';

use League\Plates\Engine;
use FWHist\Core\EnvSessionManager;

# var_dump($_SESSION);
$debug = false;
# $rootPfad = "/FHArch-oop"

$envManager = new EnvSessionManager();
$basePath = $envManager->getEnv('basePath') ?? '';
$roBase = $_SESSION['BS_Prim']['RoBase'] ?? dirname(__DIR__, 3) . DIRECTORY_SEPARATOR;
$_SESSION['BS_Prim']['RoBase'] = $roBase;
$path2ROOT = $roBase;
# echo __LINE__ . " basepath $basePath <bR>";
# var_dump($_SESSION);

$templates = new Engine(__DIR__ . '/../../templates'); // /src/core/templates

$data = [
    'title' => 'Inventar- Verwaltung',
    'debug' => $debug,
    'path2ROOT' => $path2ROOT,
    'configOk' => $configOk ?? false,
    'SI' => $SI ?? null,
    'cssBundles' => ['base', 'menu'],
    /**
     * Prüfen,  ob Module installiert, wenn JA, dann Anzeige
     */
    'has' => [
    ],
];

echo $templates->render('pages/inventarVerw', $data);
exit;