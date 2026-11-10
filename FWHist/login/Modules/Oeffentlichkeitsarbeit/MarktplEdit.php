<?php

/**
 * Marktplatz-, Wartung
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
        file_put_contents( __DIR__ . '/../../login/AOrd_Verz/Logging/MP_fatal_error.log', $message, FILE_APPEND);
    }
});

error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/../../AOrd_Verz/LoggingMP_Edit_php-error.log.txt');


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

# $_SESSION['BS_Prim'] ??= [];
$systemBasePath = $_SESSION['BS_Prim']['RoBase'] ?? dirname(__DIR__, 3) . DIRECTORY_SEPARATOR;

require_once $systemBasePath . 'vendor/autoload.php';

use FWHist\Core\EnvSessionManager;
use FWHist\Database\DB_GenericLog;
use FWHist\Services\TableColumnMetadata;
# use FWHist\Services\FormRendererFlex;
use FWHist\Modules\Oeffentlichkeitsarbeit\API\DB_Oeffentlich;
use FWHist\Services\HTMLPage;

$envManager = new EnvSessionManager();
$basePath = $envManager->getEnv('basePath')  ;
$configFileS = $envManager->getEnv('configFileS');

echo "<!DOCTYPE html>";
    
$module = 'OEF-MP';
$sub_mod = 'MP';

require $systemBasePath . 'login/Services/FuncsLib.php';
require $systemBasePath . 'login/Services/CommFuncsLib.php';
require $systemBasePath . 'login/Services/ConstLib.php';
require $systemBasePath . 'login/Services/UploadUnifiedLib.php';

$accessGuard = \FWHist\Auth\AccessGuard::current();
$isPublicView = \FWHist\Auth\AccessGuard::isPublicView();
$canEdit = $accessGuard->canEditOeffentlichkeit();
if (!$accessGuard->canViewEditForm('MP', $isPublicView)) {
    \FWHist\Auth\AccessGuard::deny('Keine Berechtigung.');
}
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && (int) ($_POST['phase'] ?? 0) === 1) {
    if (!$canEdit) {
        \FWHist\Auth\AccessGuard::deny('Änderungen sind nicht erlaubt.');
    }
    \FWHist\Auth\AccessGuard::requireCsrf();
}
$debug = False; // Debug output Ein/Aus Schalter

$header = "";

$page = new HTMLPage('Marktplatz - Biete/Suche',
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

$columnsByTables = $meta->getColumnsForTables(['oe_marktplatz' ]);
#var_dump($columnsByTables);
#var_dump($meta);
$links = new DB_Oeffentlich($DBD);
#var_dump($links);
#var_dump($_SERVER);
# var_dump($_GET);
// ============================================================================================================
// Eingabenerfassung und defauls Teil 1 - alle POST Werte werden später in array $neu gestelltt
// ============================================================================================================
if (isset($_POST['phase'])) {
    $phase = $_POST['phase'];
} else {
    $phase = 0;
}
$bs_id = 0;
if (isset($_GET['ID'])) {
    $bs_id = $_GET['ID'];
} else {
    $bs_id = "";
}
if (isset($_POST['bs_id'])) {
    $bs_id = $_POST['bs_id'];
}

$bs_id = intval($bs_id);
if (!$canEdit && $bs_id === 0) {
    http_response_code(403);
    exit('Zum Anlegen eines Marktplatz-Eintrags ist die Berechtigung ADM-OEF erforderlich.');
}

# -------------------------------------------------------------------------------------------------------
# Überschreibe die Werte in array $neu - weitere Modifikationen in Edit_tn_check_v2.php !
# -------------------------------------------------------------------------------------------------------
if ($phase == 0) {
    if ($bs_id == 0) {
        $neu['bs_id'] = $bs_id;
        $neu['bs_startdatum'] = $neu['bs_enddatum'] = $neu['bs_kurztext'] = $neu['bs_typ'] = $neu['bs_text'] = "";
        $neu['bs_emai_1'] = $neu['bs_email_2'] = $neu['bs_bild_1'] = $neu['bs_bild_2'] = $neu['bs_bild_3'] = $neu['bs_bild_4'] = "";
        $neu['bs_changed_id'] = $neu['bs_changed_at'] = "";
    } else {

        $neu_0 = $links->getMarktplById($bs_id);

        $neu = $neu_0[0];
       # var_dump($neu);

    }
}

if ($phase == 1) {
    foreach ($_POST as $name => $value)
    { $neu[$name] = trim($value);  }

    date_default_timezone_set('Europe/Berlin');
    
    $neu['bs_startdatum'] = convertInternationalDateToSql($neu['bs_startdatum'], false);
    
    $neu['bs_enddatum'] = convertInternationalDateToSql($neu['bs_enddatum'], false);
    
    $neu['bs_changed_id'] = $_SESSION['BS_Prim']['BE']['be_id'];

    $neu['bs_changed_at'] =  date('Y-m-d H:i');
    
    foreach ( $neu as $key => $val) {
        if (substr($key,0,3) != 'bs_') {
            unset($neu[$key]);
        }
    }
    
    if ($neu['bs_id'] == 0) { # neuengabe
        $ret = $links->createMarktpl($neu);
    } else { # Update
        $ret = $links->updateMarktpl($neu['bs_id'] , $neu);
    }
    
    echo "Die Daten wurden gespeichert.<br>";
    echo "Sie werden gleich Zur Liste umgeleitet.";
    
    echo '<script>
    setTimeout(function() {
        window.location.href = "MarktplList.php";
    }, 2000); // 2 Sekunden warten
</script>';
    exit;
}

switch ($phase) {
    case 0:
        require ('MarktplEdit_ph0_inc.php');
        break;
}

$page->renderFooter();
?>