<?php

declare(strict_types=1);

/** 
 * Pesrsönliche Schutzausrüstung
 * 
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

require dirname(__DIR__, 3) . '/vendor/autoload.php';  //  __DIR__ == C:\JProgs\htdocs\Feuerwehr_Archiv\FWHist\login\Controllers; dirname(__DIR__, 2) == C:\JProgs\htdocs\Feuerwehr_Archiv\FWHist\

use FWHist\Core\EnvSessionManager;
$envManager = new EnvSessionManager();

$systemBasePath = $_SESSION['BS_Prim']['RoBase']; 
$basePath = $envManager->getEnv('basePath') ?? '';

$module = 'OEF';
$sub_module = 'Menu';

$Zugr = "ADM-PSA";

// Shutdown-Funktion direkt am Anfang registrieren
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error !== null) {
        $message = "Shutdown error detected:\n" . print_r($error, true);
        error_log($message);
        // Optional: auch in eine separate Datei schreiben
        file_put_contents(__DIR__ . '/PsaVerw_fatal_error.log', $message, FILE_APPEND);
    }
});
    
$_SESSION['BS_Prim']['Mod'] = ['module' => $module, 'smod' => $sub_module, 'caller' => $module];

if (!isset($_SESSION['BS_Prim']['BE'])) {
    $bPath = $_SESSION['BS_Prim']['Env']['basePath'] ?? '';
    $redirect = $bPath !== '' ? $bPath . '/FWHist/' : '../../index.php';
    header("Location: $redirect");
    exit;
}

require $systemBasePath . 'login/Services/FuncsLib.php';
require $systemBasePath . 'login/Services/CommFuncsLib.php';
require $systemBasePath . 'login/Services/ConstLib.php';
/*
 $ber = userBerechtigtOK($Zugr);
 if (!ber) {
 header("Location $systemBasePath/public/");
 }
 */

use League\Plates\Engine;

# var_dump($_SESSION);
$debug = false;
# $rootPfad = "/FHArch-oop"


$templates = new Engine(__DIR__ . '/../../templates'); 

$data = [
    'title' => 'Persönliche Schutzausrüstungs- Arbeits- Seite',
    'debug' => $debug,
    'systemBasePath' => $systemBasePath,
    'configOk' => $configOk ?? false,
    'SI' => $SI ?? null,
    'cssBundles' => ['base', 'menu'],
    /**
     * Prüfen,  ob Module installiert, wenn JA, dann Anzeige
     */
    'has' => [
        'Ehrung' => is_file($systemBasePath . 'login/Modules/Psa/OrtsList.php'),
        'AermAb' => is_file($systemBasePath . 'login/Modules/Psa/OrtsList.php'),
    ],
];

echo $templates->render('pages/psaVerw', $data);
exit;