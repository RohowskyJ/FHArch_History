<?php

declare(strict_types=1);

/**
 *Termine-, Wartung
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
        file_put_contents(__DIr__ . '/../../AOrd_Verz/Logging/MU_Edit_fatal_error.log', $message, FILE_APPEND);
    }
});

error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');
ini_set('error_log', __DIr__ . '/../..//AOrd_Verz/Logging/MU_Edit_php-error.log.txt');

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
use FWHist\Modules\Oeffentlichkeitsarbeit\API\DB_Oeffentlich;
use FWHist\Services\TableColumnMetadata;
use FWHist\Services\FormRendererFlex;
use FWHist\Modules\Mitglieder\API\MI_MitgliederModule;
use FWHist\Services\HTMLPage;

$module = 'OEF-TE';
$sub_mod = 'TE';

$debug = False; // Debug output Ein/Aus Schalter

require $systemBasePath . 'login/Services/FuncsLib.php';
require $systemBasePath . 'login/Services/CommFuncsLib.php';
require $systemBasePath . 'login/Services/ConstLib.php';
require $systemBasePath . 'login/Services/UploadUnifiedLib.php';

$accessGuard = \FWHist\Auth\AccessGuard::current();
$isPublicView = \FWHist\Auth\AccessGuard::isPublicView();
$canEdit = $accessGuard->canEditOeffentlichkeit();
if (!$accessGuard->canViewEditForm('TE', $isPublicView)) {
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

$header = "";

$page = new HTMLPage('Termine - Veranstaltungen',
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

$columnsByTables = $meta->getColumnsForTables(['oe_va_termine' ]);
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
    $va_id = intval($_GET['ID']);
} else {
    $va_id = 0;
}
if (isset($_POST['va_id'])) {
    $va_id = intval($_POST['va_id']);
}
if (!$canEdit && $va_id === 0) {
    http_response_code(403);
    exit('Zum Anlegen eines Termins ist die Berechtigung ADM-OEF erforderlich.');
}

$Err_Msg = "";
# -------------------------------------------------------------------------------------------------------
# Überschreibe die Werte in array $neu - weitere Modifikationen in Edit_tn_check_v2.php !
# -------------------------------------------------------------------------------------------------------
if ($phase == 0) {
    if ($va_id == 0) {
        $neu['va_id'] = $va_id;
        $neu['va_datum'] = $neu['va_end_dat'] = $neu['va_anmeld_end'] = "" ; //"00-00-0000";
        $neu['va_begzt'] = $neu['va_endzt'] = "00:00";
        $neu['va_dauer'] = $neu['va_titel'] = $neu['va_beschr'] = "";
        $neu['va_kateg'] = "";
        $neu['va_anm_erf'] = "N";
        $neu['va_umfang']  = "9";
        $neu['va_inst'] = $neu['va_adresse'] = $neu['va_ort'] = $neu['va_staat'] = $neu['va_bdld'] =  "";
        $neu['va_beitrag_m'] = $neu['va_beitrag_g'] = 0;
        $neu['va_admin_email'] = $neu['va_kontakt'] = $neu['va_link_einladung'] = "";
        $neu['va_bild_1'] = $neu['va_bild_2'] = $neu['va_bild_3'] = $neu['va_bild_4'] = "";
        $neu['va_internet'] = $neu['va_url_chkd'] = $neu['va_url_obsolete'] = $neu['va_anm_text'] =  "";
        $neu['va_plaetze'] = $neu['va_warte'] = $neu['va_akt_pl'] = $neu['va_wl_pl'] = $neu['va_anz_anmeld'] =  0;
        $neu['va_freigabe_id'] = $neu['va_freigabe_at'] = $neu['va_abschluss_id'] = $neu['va_abschluss_at'] = $neu['va_storno_id'] = $neu['va_storno_at'] =  "";
        $neu['va_created_id'] = $neu['va_created_at'] = $neu['va_changed_id'] = $neu['va_changed_at'] = "";
    } else {

        $neu_0 = $links->getTermineById($va_id);
        
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

    $neu['va_id'] = intval($neu['va_id']);
    
    $neu['va_changed_id'] = $_SESSION['BS_Prim']['BE']['be_id'];
    unset($neu['phase']);

    if ( isset($neu['bild_datei_1']) && $neu['bild_datei_1'] != '') {  // 
        $neu['va_bild_1'] =  $neu['bild_datei_1'];
    }
    if ( isset($neu['bild_datei_2']) && $neu['bild_datei_2']  != '') {
        $neu['va_bild_2'] =  $neu['bild_datei_2'];
    }
    if ( isset($neu['bild_datei_3']) && $neu['bild_datei_3']!= '') {
        $neu['va_bild_4'] =  $neu['bild_datei_3'];
    }
    if ( isset($neu['bild_datei_1']) && $neu['bild_datei_1']!= '') {
        $neu['va_bild_4'] =  $neu['bild_datei_4'];
    }
    
    $neu['va_datum'] = convertInternationalDateToSql($neu['va_datum']);
    $neu['va_end_dat'] = convertInternationalDateToSql($neu['va_end_dat']);
    $neu['va_anmeld_end'] = convertInternationalDateToSql($neu['va_anmeld_end']);

    foreach ($neu as $key => $data ) {
        if (substr($key ,0 ,3) != 'va_' ){
            unset($neu[$key]);
        }
    }

    $neu['va_changed_id'] = $_SESSION['BS_Prim']['BE']['be_id'];
    $neu['va_changed_at'] = date("Y-m-d h:i");
    
    if ($neu['va_id'] == 0) { # neuengabe
        $neu['va_created_id'] = $_SESSION['BS_Prim']['BE']['be_id'];
        $neu['va_created_at'] = date("Y-m-d h:i");
        $ret = $links->createTermine($neu);
    } else { # Update
        $ret = $links->updateTermine($neu['va_id'] , $neu);
    }

    echo "Die Daten wurden gespeichert.<br>";
    echo "Sie werden gleich Zur Liste umgeleitet.";
    
    echo '<script>
    setTimeout(function() {
        window.location.href = "TerminList.php";
    }, 2000); // 2 Sekunden warten
</script>';
    exit;
}

switch ($phase) {
    case 0:
        require ('TerminEdit_ph0_inc.php');
        break;
}
HTML_trailer();
?>