<?php
/** 
 * Fahrzeug und Geräte- Verwaltung
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

$systemBasePath = $_SESSION['BS_Prim']['RoBase'];
require $systemBasePath . '/vendor/autoload.php';


use FWHist\Core\EnvSessionManager;
$envManager = new EnvSessionManager();

#$systemBasePath = $envManager->getEnv('systemBasePath');

$_SESSION['BS_Prim'] ??= [];

// Shutdown-Funktion direkt am Anfang registrieren
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error !== null) {
        $message = "Shutdown error detected:\n" . print_r($error, true);
        error_log($message);
        // Optional: auch in eine separate Datei schreiben
        file_put_contents(__DIR__ . '/FZG_fatal_error.log', $message, FILE_APPEND);
    }
});
    
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
    ini_set('log_errors', '1');
    ini_set('error_log', __DIR__ . '/FZG_php-error.log.txt');
    
    $module = 'FZG-GER';
    $sub_module = 'Fahrzeuge/Geräte';
    
    # var_dump($_SERVER);
    $Zugr = "ADM-FZG";
    
    $_SESSION['BS_Prim']['Mod'] = ['module' => $module, 'smod' => $sub_module, 'caller' => $module];
    
    if (!isset($_SESSION['BS_Prim']['BE'])) {
        
        $bPath = $_SESSION['BS_Prim']['Env']['basePath'] ?? '';
        $redirect = $bPath !== '' ? $bPath . '/FWHist/' : '../../../index.php';
        header("Location: $redirect");
        exit;
    }
    
    use League\Plates\Engine;
    # var_dump($_SESSION);
    $debug = false;
    
    require_once $systemBasePath . 'login/Services/AppLogger.php';
    
    AppLogger::info('FZG- Menu gestartet');
    
    // Services für gemeinsame Funktionen und Rollenprüfung einbinden.
    require $systemBasePath . 'login/Services/FuncsLib.php';
    require $systemBasePath . 'login/Services/CommFuncsLib.php';
    require $systemBasePath . 'login/Services/ConstLib.php';
    
    
    $templates = new Engine(__DIR__ . '/../../templates');
    
    $data = [
        'title' => 'Fahrzeuge- und Geräte- Verwaltungs Seite',
        'debug' => $debug,
        'systemBasePath' =>  $systemBasePath,
        'configOk' => $configOk ?? false,
        'SI' => $SI ?? null,
        'cssBundles' => ['base', 'menu'],
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
            'Foto' => is_file($systemBasePath. 'login/Modules/Fotografie/MediaVerw.php'),
            'Inventar' => is_file($systemBasePath. 'login/Modules/Inventar/InventarVerw.php'),
        ],
    ];
    
    echo $templates->render('pages/fzg', $data);
    exit;