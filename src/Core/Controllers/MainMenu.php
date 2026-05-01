<?php
declare(strict_types=1);

session_start();

// Shutdown-Funktion direkt am Anfang registrieren
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error !== null) {
        $message = "Shutdown error detected:\n" . print_r($error, true);
        error_log($message);
        // Optional: auch in eine separate Datei schreiben
        file_put_contents(__DIR__ . '/MaMe_fatal_error.log', $message, FILE_APPEND);
    }
});
    
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
    ini_set('log_errors', '1');
    ini_set('error_log', __DIR__ . '/MAMe_php-error.log.txt');
    
$module = 'VFH';
$sub_module = 'IntStart';

$Zugr = "ADM-MI";

$_SESSION['BS_Prim']['Mod'] = ['module' => $module, 'smod' => $sub_module, 'caller' => $module];

$path2ROOT = "../../../";

require $path2ROOT . 'login/Basis/common/BS_FuncsLib.php';
require $path2ROOT . 'login/Basis/common/FS_CommFuncsLib.php';
require $path2ROOT . 'login/Basis/common/FS_ConstLib.php';
/*
$ber = userBerechtigtOK($Zugr);
if (!ber) {
    header("Location $path2ROOT/public/");
}
*/
require_once __DIR__ . '/../../../vendor/autoload.php';

use League\Plates\Engine;
var_dump($_SESSION);
$debug = false;
# $rootPfad = "/FHArch-oop"

use Fharch\Core\EnvSessionManager;
$envManager = new EnvSessionManager();
$basePath = $envManager->getEnv('basePath');
echo __LINE__ . " basepath $basePath <bR>";
var_dump($_SESSION);

$templates = new Engine(__DIR__ . '/../templates'); // /src/core/templates

$data = [
    'title' => 'Haupt- Auswahl- Seite',
    'debug' => $debug,
    'path2ROOT' => $path2ROOT,
    'configOk' => $configOk ?? false,
    'SI' => $SI ?? null,
    'cssBundles' => ['base', 'menu'],
    /**
     * Prüfen,  ob Module installiert, wenn JA, dann Anzeige 
     */
    'has' => [
        'Suchen' => is_file($path2ROOT . 'src/Core/Modules/Suchen/Suchen.php'),
        'FzgGer' => is_file($path2ROOT . 'src/Core/Modules/FahrzeugeGeraete/FzgGerVerw.php'),
        'Oeffi' => is_file($path2ROOT . 'src/Core/Modules/Oeffentlichkeitsarbeit/OeffiVerw.php'),
        'PSA' => is_file($path2ROOT . 'src/Core/Modules/Psa/PsaVerw.php'),
        'Archiv' => is_file($path2ROOT . 'src/Core/Modules/Archivalien/ArchivVerw.php'),
        'Doku' => is_file($path2ROOT . 'src/Core/Modules/Oeffentlichkeitsarbeit/DokuVerw.php'),
        'Foto' => is_file($path2ROOT . 'src/Core/Modules/Fotografie/MediaVerw.php'),
        'Inventar' => is_file($path2ROOT . 'src/Core/Modules/Inventar/InventarVerw.php'),
    ],
];

echo $templates->render('pages/main', $data);
exit;