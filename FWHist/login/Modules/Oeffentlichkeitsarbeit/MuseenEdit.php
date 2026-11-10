<?php

declare(strict_types=1);

/**
 * Museen-, Wartung
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

$module = 'OEF-MU';
$sub_mod = 'Edit';

$debug = False; // Debug output Ein/Aus Schalter

// Shutdown-Funktion direkt am Anfang registrieren
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error !== null) {
        $message = "Shutdown error detected:\n" . print_r($error, true);
        error_log($message);
        // Optional: auch in eine separate Datei schreiben
        file_put_contents(__DIR__ . '/MU_Edit_fatal_error.log', $message, FILE_APPEND);
    }
});

error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/MU_Edit_php-error.log.txt');

require $systemBasePath . 'login/Services/FuncsLib.php';
require $systemBasePath . 'login/Services/CommFuncsLib.php';
require $systemBasePath . 'login/Services/ConstLib.php';
require $systemBasePath . 'login/Services/UploadUnifiedLib.php';

$accessGuard = \FWHist\Auth\AccessGuard::current();
$isPublicView = \FWHist\Auth\AccessGuard::isPublicView();
$canEdit = $accessGuard->canEditOeffentlichkeit();
if (!$accessGuard->canViewEditForm('MU', $isPublicView)) {
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

$page = new HTMLPage('Museen',
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

$columnsByTables = $meta->getColumnsForTables(['oe_museen' ]);
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
$mu_id = (int) ($_POST['mu_id'] ?? $_GET['ID'] ?? 0);
if (!$canEdit && $mu_id === 0) {
    http_response_code(403);
    exit('Zum Anlegen eines Museums ist die Berechtigung ADM-OEF erforderlich.');
}

# -------------------------------------------------------------------------------------------------------
# Überschreibe die Werte in array $neu - weitere Modifikationen in Edit_tn_check_v2.php !
# -------------------------------------------------------------------------------------------------------
if ($phase == 0) {
    if ($mu_id == 0) {
        $neu['mu_id'] = $mu_id;
        $neu['mu_staat'] = "AT";
        $neu['mu_bdland'] = "NOE";
        $neu['mu_bez'] = $neu['mu_name'] = $neu['mu_bezeichng'] = $neu['mu_adresse_a'] = $neu['mu_plz_a'] = $neu['mu_ort_a'] = "";
        $neu['mu_adresse_p'] = $neu['mu_plz_p'] = $neu['mu_ort_p'] = $neu['mu_eigner'] = "";
        $neu['mu_kustos_titel'] = $neu['mu_kustos_vname'] = $neu['mu_kustos_name'] = $neu['mu_kustos_dgr'] = $neu['mu_kustos_tel'] = "";
        $neu['mu_kustos_intern'] = $neu['mu_kustos_email'] = $neu['mu_sammelbeg'] = "";
        $neu['mu_bild_1'] = $neu['mu_bild_2'] = $neu['mu_bildbeschr_1']  = $neu['mu_bildbeschr_2']  = $neu['mu_mustyp'] = $neu['mu_museigtyp'] = $neu['mu_sammlgschw'] = "";
        $neu['mu_besobj_1'] = $neu['mu_besobj_2'] = $neu['mu_besobj_3'] = $neu['mu_anz_obj'] = $neu['mu_archiv'] = "";
        $neu['mu_protbuch'] = $neu['mu_abzeich'] = $neu['mu_ausruest'] = $neu['mu_kleinger'] = $neu['mu_grossger'] = "";
        $neu['mu_toilett'] = $neu['mu_garderobe'] = $neu['mu_cafe'] = $neu['mu_sonst_anb'] = $neu['mu_rollst'] = "";
        $neu['mu_beheinr'] = $neu['mu_oeffnung'] = $neu['mu_saison'] = $neu['mu_oez_mo'] = $neu['mu_oez_di'] = "";
        $neu['mu_oez_mi'] = $neu['mu_oez_do'] = $neu['mu_oez_fr'] = $neu['mu_oez_sa'] = $neu['mu_oez_so'] = "";
        $neu['mu_oez_fei'] = $neu['mu_f1_titel'] = $neu['mu_f1_vname'] = $neu['mu_f1_name'] = $neu['mu_f1_tel'] = $neu['mu_f1_dgr'] = "";
        $neu['mu_f1_handy'] = $neu['mu_f1_email'] = $neu['mu_f2_titel'] = $neu['mu_f2_vname'] = $neu['mu_f2_name'] = "";
        $neu['mu_f2_dgr'] = $neu['mu_f2_tel'] = $neu['mu_f2_handy'] = $neu['mu_f2_email'] = $neu['mu_uidaend'] = "";
        $neu['mu_aenddat'] = "";
        $neu['mu_changed_id'] = $neu['mu_changed_at'] = "";
    } else {

        $neu_0 = $links->getMuseenById($mu_id);
        
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
 
    $neu['mu_id'] = intval($neu['mu_id']);

    date_default_timezone_set('Europe/Berlin');
    
    $neu['mu_changed_id'] = $_SESSION['BS_Prim']['BE']['be_id'];

    if ($neu['staat_id'] != '') {
        $neu['mu_staat'] = $neu['staat_id'];
    }
    if ($neu['bdld_id'] != '') {
        $neu['mu_bdland'] = $neu['bdld_id'];
    }
    if ($neu['mandant_id'] != '') {
        $neu['mu_eigner'] = intval($neu['mandant_id']);
    }
    
    if ($neu['mu_changed_at'] == '') {
        $neu['mu_changed_at'] = date("Y-m-d H:m:s");
    }
    
    $neu['mu_anz_obj'] = intval($neu['mu_anz_obj']);
    
    foreach ( $neu as $key => $val) {
        if (substr($key,0,3) != 'mu_') {
            unset($neu[$key]);
        }
    }
   
    if ($neu['mu_id'] == 0) { # neuengabe
        $ret = $links->createMuseen($neu);
    } else { # Update
        $ret = $links->updateMuseen($neu['mu_id'] , $neu);
    }
    
    // Versuche, mit header() weiterzuleiten, wenn noch keine Ausgabe erfolgt ist
    if (!headers_sent()) {
        header("Location: MuseenList.php");
        exit; // Skript beenden, damit keine weitere Ausgabe erfolgt
    } else {
        // Falls Header schon gesendet wurde, JavaScript-Weiterleitung ausgeben
        $targetUrl = "MuseenList.php"; // Ziel-URL
        
        echo '<script type="text/javascript">
        // Sofortige Weiterleitung mit JavaScript
        window.location.href = "' . $targetUrl . '";
    </script>';
        
        // Optional: Fallback-Link für den Fall, dass JS deaktiviert ist
        echo '<noscript>
        <meta http-equiv="refresh" content="0;url=' . $targetUrl . '" />
        <p>Wenn Sie nicht automatisch weitergeleitet werden, <a href="' . $targetUrl . '">klicken Sie hier</a>.</p>
    </noscript>';
        
        exit; // Skript beenden
    }
}

switch ($phase) {
    case 0:
        require ('MuseenEdit_ph0_inc.php');
        break;
}
$page->renderFooter();
?>