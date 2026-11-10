<?php

declare(strict_types=1);

/**
 * Presse- Spiegel- Wartung
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
use FWHist\Database\DB_GenericLog;
use FWHist\Modules\Oeffentlichkeitsarbeit\API\DB_Oeffentlich;
use FWHist\Services\TableColumnMetadata;
use FWHist\Services\FormRendererFlex;
use FWHist\Modules\Mitglieder\API\MI_MitgliederModule;
use FWHist\Services\HTMLPage;

# new EnvSessionManager();

$module = 'OEF-PR';
$sub_mod = 'PR';

$debug = False; // Debug output Ein/Aus Schalter

// Shutdown-Funktion direkt am Anfang registrieren
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error !== null) {
        $message = "Shutdown error detected:\n" . print_r($error, true);
        error_log($message);
        // Optional: auch in eine separate Datei schreiben
        file_put_contents(__DIR__ . '/PR_Edit_fatal_error.log', $message, FILE_APPEND);
    }
});
    
error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/PR_Edit_php-error.log.txt');

require $systemBasePath . 'login/Services/FuncsLib.php';
require $systemBasePath . 'login/Services/CommFuncsLib.php';
require $systemBasePath . 'login/Services/ConstLib.php';
require $systemBasePath . 'login/Services/UploadUnifiedLib.php';

$accessGuard = \FWHist\Auth\AccessGuard::current();
$isPublicView = \FWHist\Auth\AccessGuard::isPublicView();
$canEdit = $accessGuard->canEditOeffentlichkeit();
if (!$accessGuard->canViewEditForm('PR', $isPublicView)) {
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

$page = new HTMLPage('Presse- Spiegel',
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

$columnsByTables = $meta->getColumnsForTables(['oe_presse' ]);
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
    $pr_id = intval($_GET['ID']);
} else {
    $pr_id = 0;
}
if (isset($_POST['pr_id'])) {
    $pr_id = intval($_POST['pr_id']);
}
if (!$canEdit && $pr_id === 0) {
    http_response_code(403);
    exit('Zum Anlegen einer Presseinformation ist die Berechtigung ADM-OEF erforderlich.');
}

# -------------------------------------------------------------------------------------------------------
# Überschreibe die Werte in array $neu - weitere Modifikationen in Edit_tn_check_v2.php !
# -------------------------------------------------------------------------------------------------------
if ($phase == 0) {
    if ($pr_id == 0) {
        $neu['pr_id'] = $pr_id;
        $neu['pr_datum'] = $neu['pr_name'] = $neu['pr_ausg'] = $neu['pr_medium'] = "";
        $neu['pr_seite'] = $neu['pr_teaser'] = $neu['pr_text'] = $neu['pr_web_seite'] = $neu['pr_url_chkd'] = $neu['pr_url_obsolete'] = $neu['pr_web_text'] = "";
        $neu['pr_bild_1'] = $neu['pr_bild_2'] = $neu['pr_bild_3'] = $neu['pr_bild_4'] = $neu['pr_bild_5'] = $neu['pr_bild_6'] =  $neu['pr_inet'] = "";
        $neu['pr_changed_id'] = $neu['pr_changed_at'] ="";
    } else {

        $neu_0 = $links->getPresseById($pr_id);
        
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
}

if ($phase == 1) {
    foreach ($_POST as $name => $value)
    { $neu[$name] = trim($value);  }
    
    $neu['pr_id'] = intval($neu['pr_id']);
    
    date_default_timezone_set('Europe/Berlin');
    $neu['pr_changed_id'] = $_SESSION['BS_Prim']['BE']['be_id'];
    $neu['pr_changed_at'] = date('Y-m-d H:i',time());
/*
    if ( isset($neu['bild_datei_1']) && $neu['bild_datei_1'] != '') {  // 
        $neu['pr_bild_1'] =  $neu['bild_datei_1'];
    }
    if ( isset($neu['bild_datei_2']) && $neu['bild_datei_2']  != '') {
        $neu['pr_bild_2'] =  $neu['bild_datei_2'];
    }
    if ( isset($neu['bild_datei_3']) && $neu['bild_datei_3']!= '') {
        $neu['pr_bild_3'] =  $neu['bild_datei_3'];
    }
    if ( isset($neu['bild_datei_4']) && $neu['bild_datei_4']!= '') {
        $neu['pr_bild_4'] =  $neu['bild_datei_4'];
    }
    if ( isset($neu['bild_datei_3']) && $neu['bild_datei_5']!= '') {
        $neu['pr_bild_5'] =  $neu['bild_datei_5'];
    }
    if ( isset($neu['bild_datei_6']) && $neu['bild_datei_6']!= '') {
        $neu['pr_bild_6'] =  $neu['bild_datei_6'];
    }
 */   
    foreach ( $neu as $key => $value) {
        if (substr($key,0,3) != "pr_") {
            unset($neu[$key]);
        }
    }

    if ($neu['pr_id'] == 0) { # neuengabe
        $ret = $links->createPresse($neu);
    } else { # Update
        $ret = $links->updatePresse($neu['pr_id'] , $neu);
    }

    echo "Die Daten wurden gespeichert.<br>";
    echo "Sie werden gleich Zur Liste umgeleitet.";
    
    echo '<script>
    setTimeout(function() {
        window.location.href = "PresseList.php";
    }, 2000); // 2 Sekunden warten
</script>';
    exit;
}

switch ($phase) {
    case 0:
        require ('PresseEdit_ph0_inc.php');
        break;
}
$page->renderFooter();
?>