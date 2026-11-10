<?php

declare(strict_types=1);

/**
 * Zeitungs- inhalte- Index, Wartung
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
        file_put_contents(__DIR__ . 'ZtI_Edit_fatal_error.log', $message, FILE_APPEND);
    }
});
  
error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/ZtI_Edit_php-error.log.txt');

require $systemBasePath . 'login/Services/FuncsLib.php';
require $systemBasePath . 'login/Services/CommFuncsLib.php';
require $systemBasePath . 'login/Services/ConstLib.php';

$accessGuard = \FWHist\Auth\AccessGuard::current();
$isPublicView = \FWHist\Auth\AccessGuard::isPublicView();
$canEdit = $accessGuard->canEditOeffentlichkeit();
$canViewReadOnly = $accessGuard->canViewZeitungsContent() && !$canEdit;
if (!$canEdit && !$canViewReadOnly) {
    \FWHist\Auth\AccessGuard::deny('Keine Berechtigung zum Anzeigen dieses Zeitungsinhalts.');
}
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && (int) ($_POST['phase'] ?? 0) === 1) {
    if (!$canEdit) {
        \FWHist\Auth\AccessGuard::deny('Änderungen sind nicht erlaubt.');
    }
    \FWHist\Auth\AccessGuard::requireCsrf();
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

$header = "";

$page = new HTMLPage('Zeitungs- Inhalte Index',
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
// ============================================================================================================
// Eingabenerfassung und defauls Teil 1 - alle POST Werte werden später in array $neu gestelltt
// ============================================================================================================
if (isset($_POST['phase'])) {
    $phase = $_POST['phase'];
} else {
    $phase = 0;
}
if (isset($_GET['ID'])) {
    $ih_id = intval($_GET['ID']);
} else {
    $ih_id = 0;
}
if (isset($_GET['ztNr'])) {
    $zt_id = intval($_GET['ztNr']);
} else {
    $zt_id = 0;;
}
if (isset($_POST['ih_id'])) {
    $ih_id = intval($_POST['ih_id']);
}
if (isset($_POST['zt_id'])) {
    $zt_id = intval($_POST['zt_id']);
}
if (!$canEdit && $ih_id === 0) {
    http_response_code(403);
    exit('Zum Anlegen eines Zeitungsinhalts ist die Berechtigung ADM-OEF erforderlich.');
}

$DBD = new DB_GenericLog();
#var_dump($DBD);
$pdo = $DBD->getPDO();
#var_dump($pdo);

$meta = new TableColumnMetadata($pdo,'fharch_new',false);
#var_dump($meta);

$columnsByTables = $meta->getColumnsForTables(['oe_zeitung_'.$zt_id ]);
#var_dump($columnsByTables);
#var_dump($meta);
$links = new DB_Oeffentlich($DBD);
#var_dump($links);
#var_dump($_SERVER);

$Err_Msg = "";
# -------------------------------------------------------------------------------------------------------
# Überschreibe die Werte in array $neu - weitere Modifikationen in Edit_tn_check_v2.php !
# -------------------------------------------------------------------------------------------------------
if ($phase == 0) {
    if ($ih_id == 0) {
        $neu = array(
        'ih_id' => 0,
        'ih_zt_id' => $zt_id,
        'ih_jahrgang' => "",
        "ih_jahr" => "",
        'ih_nr' => "",
        'ih_kateg' => "",
        'ih_sg' => "",
        "ih_ssg" => "",
        'ih_gruppe' => "",
        'ih_titel' => "",
        'ih_titelerw' => "",
        'ih_autor'   => "",  
        'ih_email'  => "",
        'ih_tel' => "",
        'ih_fax' => "",
        'ih_seite' => "",
        'ih_spalte' => "",
        'ih_fwehr' => "",
        'ih_changed_id'  => "",
        'ih_changed_at' => ""
        );
    } else {

        $neu_0 = $links->getZInhalteById($ih_id, $zt_id );
        
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

    $neu['ih_id'] = intval($neu['ih_id']);
    $neu['ih_zt_id'] = intval($neu['ih_zt_id']);
    
    $neu['zt_changed_id'] = $_SESSION['BS_Prim']['BE']['be_id'];
    
    #$neu['zt_erstausgdat'] = convertInternationalDateToSql($neu['zt_erstausgdat']);
    #$neu['zt_letztausgabe'] = convertInternationalDateToSql($neu['zt_letztausgabe']);
    
    foreach ($neu as $key => $val) {
        if (substr($key,0,3) != "ih_") {
            unset($neu[$key]);
        }
    }
    
    date_default_timezone_set('Europe/Berlin');
    
    $neu['ih_changed_id'] = $_SESSION['BS_Prim']['BE']['be_id'];
    $neu['ih_changed_at'] = date("Y-m-d h:i:s");
    #var_dump($neu);
    if ($neu['ih_zt_id'] == 0) { # neuengabe
        $ret = $links->createZInhalte($neu, $neu['ih_zt_id'], $neu['ih_id']);
    } else { # Update
        $ret = $links->updateZInhalte($neu['ih_id'], $neu, $neu['ih_zt_id'] );
    }
#var_dump($ret);
    header("Location: Z_InhalteList.php?ID=".$neu['ih_zt_id']);
}

switch ($phase) {
    case 0:
        require ('Z_InhalteEdit_ph0_inc.php');
        break;
}
$page->renderFooter();
?>