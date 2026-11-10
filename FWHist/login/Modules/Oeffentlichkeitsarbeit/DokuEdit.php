<?php

declare(strict_types=1);

/**
 * Dokumentationen-, Wartung
 * 
 * @author Josef Rohowsky - neu 2020
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Shutdown-Funktion direkt am Anfang registrieren
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error !== null) {
        $message = "Shutdown error detected:\n" . print_r($error, true);
        error_log($message);
        // Optional: auch in eine separate Datei schreiben
        file_put_contents( __DIR__ . '/../../AOrd_Verz/Logging//Doku_fatal_error.log', $message, FILE_APPEND);
    }
});

error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors', '1');
ini_set('error_log',  __DIR__ . '/../../AOrd_Verz/Logging/Doku_php-error.log.txt');
# var_dump($_SERVER);
    
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

echo "<!Doctype html>´";

use FWHist\Core\EnvSessionManager;
use FWHist\Services\FuncsClass;
use FWHist\Services\HTMLPage;
use FWHist\Services\TableColumnMetadata;
use FWHist\Modules\Mitglieder\API;
use FWHist\Database\DB_GenericLog;
use FWHist\Modules\Oeffentlichkeitsarbeit\API\DB_Oeffentlich;
use FWHist\Auth\Auth;

$module = 'OEF-DO';
$sub_mod = 'DO';

$tabelle = 'oe_museen';

require $systemBasePath . 'login/Services/FuncsLib.php';
require $systemBasePath . 'login/Services/CommFuncsLib.php';
require $systemBasePath . 'login/Services/ConstLib.php';
require $systemBasePath . 'login/Services/UploadUnifiedLib.php';

$accessGuard = \FWHist\Auth\AccessGuard::current();
$isPublicView = \FWHist\Auth\AccessGuard::isPublicView();
$canEdit = $accessGuard->canEditOeffentlichkeit();
if (!$accessGuard->canViewEditForm('DO', $isPublicView)) {
    \FWHist\Auth\AccessGuard::deny('Keine Berechtigung.');
}
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && (int) ($_POST['phase'] ?? 0) === 1) {
    if (!$canEdit) {
        \FWHist\Auth\AccessGuard::deny('Änderungen sind nicht erlaubt.');
    }
    \FWHist\Auth\AccessGuard::requireCsrf();
}

$envManager = new EnvSessionManager();
$basePath = $envManager->getEnv('basePath')  ;
$configFileS = $envManager->getEnv('configFileS');

$header =   "";
# ===========================================================================================================
# Haeder ausgeben
# ===========================================================================================================

$page = new HTMLPage('Dokumentationen',
    $systemBasePath,
    $basePath,
    ['headExtras' => $header,
        'type' => 'Form',
        'width' => '60em',
    ]
    );

$page->loadConfig($configFileS);
$page->renderHeader();
    
initial_debug('POST','GET'); # Wenn $debug=true - Ausgabe von Debug Informationen: $_POST, $_GET, $_FILES

// ============================================================================================================
// Eingabenerfassung und defauls
// ============================================================================================================

$DBD = new DB_GenericLog();
#var_dump($DBD);
$pdo = $DBD->getPDO();
#var_dump($pdo);

$meta = new TableColumnMetadata($pdo,'fharch_new',false);
#var_dump($meta);

$columnsByTables = $meta->getColumnsForTables(['oe_dokumente' ]);
#var_dump($columnsByTables);
#var_dump($meta);
$links = new DB_Oeffentlich($DBD);
#var_dump($links);
#var_dump($_SERVER);
// ============================================================================================================
// Eingabenerfassung und defauls Teil 1 - alle POST Werte werden später in array $neu gestelltt
// ============================================================================================================
if (isset($_POST['phase'])) {
    $phase = $_POST['phase'];
} else {
    $phase = 0;
}
if (isset($_GET['ID'])) {
    $dk_id = intval($_GET['ID']);
} else {
    $dk_id = 0;
}
if (isset($_POST['dk_id'])) {
    $dk_id = intval($_POST['dk_id']);
}
if (!$canEdit && $dk_id === 0) {
    http_response_code(403);
    exit('Zum Anlegen einer Dokumentation ist die Berechtigung ADM-OEF erforderlich.');
}

# -------------------------------------------------------------------------------------------------------
# Überschreibe die Werte in array $neu - weitere Modifikationen in Edit_tn_check_v2.php !
# -------------------------------------------------------------------------------------------------------
if ($phase == 0) {
    if ($dk_id == 0) {
        $neu['dk_id'] = $dk_id;
        $neu['dk_thema'] = $neu['dk_titel'] = $neu['dk_author'] = $neu['dk_urspr'] = "";
        $neu['dk_dsn_1'] = $neu['dk_dsn_2'] = $neu['dk_dsn_3'] = $neu['dk_dsn_4'] = $neu['dk_path2dsn'] = $neu['dk_url'] = $neu['dk_sg'] = "";
        $neu['dk_changed_id'] = $neu['dk_changed_at'] = "";
    } else {

        $neu_0 = $links->getDokusById($dk_id);
        
           $neu = $neu_0[0];
           unset($neu_0[0]);
           $neu['ei_id'] = 9;
        # var_dump($neu);
        
        if ($debug) {
            echo '<pre class=debug>';
            echo '<hr>$neu: ';
            var_dump($neu);
            echo '</pre>';
        }
    }
}

if ($phase == 1) {
    foreach ($_POST as $name => $value)
    { $neu[$name] = trim($value);  }
    
    date_default_timezone_set('Europe/Berlin');
    
    $neu['dk_id'] = intval($neu['dk_id']);
   
    foreach($neu as $inx => $val) {
        if (substr($inx, 0, 3) == 'dk_') {
            continue;
        }
        unset($neu[$inx]) ;   
    }
    
    $neu['dk_changed_id'] = $_SESSION['BS_Prim']['BE']['be_id'];
    unset($neu['phase']);
    
    if ($neu['dk_changed_at'] == '') {
        $neu['dk_changed_at'] = date("Y-m-d H:m:s");
    }
   
    if ($neu['dk_id'] == 0) { # neuengabe
        $ret = $links->createDokus($neu);
    } else { # Update
        $ret = $links->updateDokus($neu['dk_id'] , $neu);
    }

    echo "Die Daten wurden gespeichert.<br>";
    echo "Sie werden gleich Zur Liste umgeleitet.";
    
    echo '<script>
    setTimeout(function() {
        window.location.href = "DokuList.php";
    }, 2000); // 2 Sekunden warten
</script>';
    exit;
}

switch ($phase) {
    case 0:
        require ('DokuEdit_ph0_inc.php');
        break;
}
$page->renderFooter();
?>