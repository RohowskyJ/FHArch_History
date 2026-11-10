<?php

declare(strict_types=1);

/**
 * Zeitungs- Index, Wartung
 *
 * @author Josef Rohowsky - neu 2020
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

# $_SESSION['BS_Prim'] ??= [];
$systemBasePath = $_SESSION['BS_Prim']['RoBase'] ?? dirname(__DIR__, 3) . DIRECTORY_SEPARATOR;

require_once $systemBasePath . 'vendor/autoload.php';

use FWHist\Core\EnvSessionManager;
new EnvSessionManager();

$module = 'OEF';
$sub_mod = 'ZE';

$debug = False; // Debug output Ein/Aus Schalter

// Shutdown-Funktion direkt am Anfang registrieren
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error !== null) {
        $message = "Shutdown error detected:\n" . print_r($error, true);
        error_log($message);
        // Optional: auch in eine separate Datei schreiben
        file_put_contents(__DIR__ . '/ZT_Edit_fatal_error.log', $message, FILE_APPEND);
    }
});

error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/ZT_Edit_php-error.log.txt');

require $systemBasePath . 'login/Services/FuncsLib.php';
require $systemBasePath . 'login/Services/CommFuncsLib.php';
require $systemBasePath . 'login/Services/ConstLib.php';

$accessGuard = \FWHist\Auth\AccessGuard::current();
$isPublicView = \FWHist\Auth\AccessGuard::isPublicView();
$canEdit = $accessGuard->canEditOeffentlichkeit();
if (!$accessGuard->canViewEditForm('ZT', $isPublicView)) {
    \FWHist\Auth\AccessGuard::deny('Keine Berechtigung.');
}

use FWHist\Database\DB_GenericLog;
use FWHist\Modules\Oeffentlichkeitsarbeit\API\DB_Oeffentlich;
use FWHist\Services\TableColumnMetadata;
use FWHist\Services\FormRendererFlex;
use FWHist\Modules\Mitglieder\API\MI_MitgliederModule;
use FWHist\Services\HTMLPage;

$envManager = new EnvSessionManager();
$basePath = $envManager->getEnv('basePath')  ;
$configFileS = $envManager->getEnv('configFileS');

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && (int) ($_POST['phase'] ?? 0) === 1) {
    if (!$canEdit) {
        \FWHist\Auth\AccessGuard::deny('Änderungen sind nicht erlaubt.');
    }
    \FWHist\Auth\AccessGuard::requireCsrf();
}

$header = "";

$page = new HTMLPage('Zeitungs- Index',
    $systemBasePath,
    $basePath,
    ['headExtras' => $header,
        'type' => 'Form',
        'width' => '60em',
        'jq' => true,
        'jqui' => true,
        'jq_accordion' => true,
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

$columnsByTables = $meta->getColumnsForTables(['oe_zeitungen' ]);
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
    $zt_id = intval($_GET['ID']);
} else {
    $zt_id = 0;
}
if (isset($_POST['zt_id'])) {
    $zt_id = intval($_POST['zt_id']);
}
if (!$canEdit && $zt_id === 0) {
    http_response_code(403);
    exit('Zum Anlegen einer Zeitung ist die Berechtigung ADM-OEF erforderlich.');
}

$Err_Msg = "";
# -------------------------------------------------------------------------------------------------------
# Überschreibe die Werte in array $neu - weitere Modifikationen in Edit_tn_check_v2.php !
# -------------------------------------------------------------------------------------------------------
if ($phase == 0) {
    if ($zt_id == 0) {
        $neu = array(
        'zt_id' => 0,
        'zt_name' => "",
        "zt_herausg" => "",
        'zt_internet' => "",
        'zt_email' => "",
        'zt_daten' => "0",
        "zt_erstausgdat" => NULL,
        'zt_letztausgabe' => NULL,
        'zt_changed_id'  => "",
        'zt_changed_at' => ""
        );
    } else {

        $neu_0 = $links->getZeitungById($zt_id);
        
           $neu = $neu_0[0];
           unset($neu_0[0]);
        # var_dump($neu);
        
        if ($debug) {
            echo '<pre class=debug>';
            echo '<hr>$neu: ';
            var_dump($neu);
            echo '</pre>';
        }
    }
    unset($_SESSION[$module]['Pct_Arr']);
}

if ($phase == 1) {
    foreach ($_POST as $name => $value)
    { $neu[$name] = trim($value);  }

    $neu['zt_id'] = intval($neu['zt_id']);
    
    $neu['zt_changed_id'] = $_SESSION['BS_Prim']['BE']['be_id'];
   
    $neu['zt_erstausgdat'] = convertInternationalDateToSql($neu['zt_erstausgdat']);
    $neu['zt_letztausgabe'] = convertInternationalDateToSql($neu['zt_letztausgabe']);
    
    foreach ($neu as $key => $val) {
        if (substr($key,0,3) != "zt_") {
            unset($neu[$key]);
        }
    }
    
    date_default_timezone_set('Europe/Berlin');
    
    if ($neu['zt_id'] == 0) { # neuengabe
        $neu['zt_changed_id'] = $_SESSION['BS_Prim']['BE']['be_id'];
        $neu['zt_changed_at'] = date("Y-m-d h:i");
        $ret = $links->createZeitung($neu);
    } else { # Update
        $neu['zt_changed_id'] = $_SESSION['BS_Prim']['BE']['be_id'];
        $neu['zt_changed_at'] = date("Y-m-d h:i");
        $ret = $links->updateZeitung($neu['zt_id'] , $neu);
    }

    header("Location: ZeitungList.php");
}

switch ($phase) {
    case 0:
        require ('ZeitungEdit_ph0_inc.php');
        break;
}
$page->renderFooter();
?>