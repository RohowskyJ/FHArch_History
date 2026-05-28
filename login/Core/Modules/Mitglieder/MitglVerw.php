<?php

/**
 * Menu Mitgliederverwaltung
 * 
 * @author Josef Rohowsky - neu 2023
 */
session_start();

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

$Zugr = "ADM-MI";

/**
 * Angleichung an den Root-Path
 *
 * @var string $path2ROOT
 */
$path2ROOT = "../../../../";

if (!isset($_SESSION['BS_Prim']['BE'])) {
    $bPath = $_SESSION['BS_Prim']['Env']['basePath'];
    header('location: $bPath/fharch-oop/VFH/');
}

$debug = False; // Debug output Ein/Aus Schalter

require $path2ROOT . 'login/Core/Services/FuncsLib.php';
require $path2ROOT . 'login/Core/Services/CommFuncsLib.php';
require $path2ROOT . 'login/Core/Services/ConstLib.php';

require $path2ROOT . 'login/common/VF_Comm_Funcs.lib.php';

require_once __DIR__ . '/../../../../vendor/autoload.php';

use League\Plates\Engine;

# var_dump($_SESSION);
$debug = false;

# $rootPfad = "/FHArch-oop"
use Fharch\Core\EnvSessionManager;
$envManager = new EnvSessionManager();
$basePath = $envManager->getEnv('basePath');
# echo __LINE__ . " basepath $basePath <bR>";
#var_dump($_SESSION);

$templates = new Engine(__DIR__ . '/../../templates'); // /src/core/templates

$data = [
    'title' => 'Mitglieder- Verwaltungs- Seite',
    'debug' => $debug,
    'path2ROOT' => $path2ROOT,
    'SI' => $SI ?? null,
    'cssBundles' => ['base', 'menu'],
];

echo $templates->render('pages/mitglVerw', $data);
exit;
