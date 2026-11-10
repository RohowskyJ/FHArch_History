<?php

/**
 * Liste der vom Verein verliehenen Ehrungen, Wartung
 *
 * @author Josef Rohowsky - neu 2026
 *
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

# $_SESSION['BS_Prim'] ??= [];
$systemBasePath = $_SESSION['BS_Prim']['RoBase'] ?? dirname(__DIR__, 3) . DIRECTORY_SEPARATOR;

require_once $systemBasePath . 'vendor/autoload.php';

use FWHist\Core\EnvSessionManager;
use FWHist\Auth\AccessGuard;
use FWHist\Database\DB_GenericLog;
use FWHist\Services\TableColumnMetadata;
use FWHist\Services\FormRendererFlex;
use FWHist\Modules\Mitglieder\API\MI_MitgliederModule;
use FWHist\Services\HTMLPage;

$module = 'MVW';
$sub_mod = 'Ehrg';

$tabelle = 'mi_ehrungen';

const Prefix = '';

$debug = False; // Debug output Ein/Aus Schalter

// Shutdown-Funktion direkt am Anfang registrieren
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error !== null) {
        $message = "Shutdown error detected:\n" . print_r($error, true);
        error_log($message);
        // Optional: auch in eine separate Datei schreiben
        file_put_contents(__DIR__ . '/MI_Ehrg_Edit_fatal_error.log', $message, FILE_APPEND);
    }
});
    
    error_reporting(E_ALL);
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
    ini_set('error_log', __DIR__ . '/MI_Ehrg_Edit_php-error.log.txt');
    
    
    require $systemBasePath . 'login/Services/FuncsLib.php';
    require $systemBasePath . 'login/Services/CommFuncsLib.php';
    require $systemBasePath . 'login/Services/ConstLib.php';
    require $systemBasePath . 'login/Services/UploadUnifiedLib.php';

    $DBD = new DB_GenericLog();
    $guard = AccessGuard::fromSession($DBD);
    $canEdit = $guard->canWriteMemberData();
    if (!$canEdit) {
        http_response_code(403);
        exit('Für diesen Programmteil besteht keine Berechtigung.');
    }
    if ((int) ($_POST['phase'] ?? 0) === 1) {
        AccessGuard::requireCsrf();
    }
    $requestedId = (int) ($_GET['ID'] ?? $_POST['me_id'] ?? 0);
    if (!$canEdit && $requestedId === 0) {
        http_response_code(403);
        exit('Eine Neuanlage ist nicht erlaubt.');
    }
    $envManager = new EnvSessionManager();
    $basePath = $envManager->getEnv('basePath')  ;
    $configFileS = $envManager->getEnv('configFileS');
    
    $header = "";
    
    $page = new HTMLPage('Auszeichnungs - Verwaltung',
        $systemBasePath,
        $basePath,
        ['headExtras' => $header,
            'type' => 'Form',
            'width' => '60em',
        ]
        );
    
    $page->loadConfig($configFileS);
    $page->renderHeader();

# HTML_header('Auszeichnungs - Verwaltung', '', 'Form', '90em'); # Parm: Titel,Subtitel,HeaderLine,Type,width

initial_debug('POST','GET');
# var_dump($_POST);
// ============================================================================================================
// Eingabenerfassung und defauls
// ============================================================================================================

#var_dump($DBD);
$pdo = $DBD->getPDO();
#var_dump($pdo);

$meta = new TableColumnMetadata($pdo,'fharch_new',false);
#var_dump($meta);

$columnsByTables = $meta->getColumnsForTables(['mi_ehrungen']);
# var_dump($columnsByTables);

$mitgl = new MI_MitgliederModule($DBD);
# var_dump($mitgl);
#var_dump($_SERVER);
#var_dump($_POST);
#var_dump($_GET);
// ============================================================================================================
// Eingabenerfassung und defauls Teil 1 - alle POST Werte werden später in array $neu gestelltt
// ============================================================================================================
if (isset($_POST['phase'])) {
    $phase = $_POST['phase'];
} else {
    $phase = 0;
}
if (isset($_GET['ID'])) {
    $me_id = intval($_GET['ID']);
} else {    
    $me_id = 0;
}
$mi_id = (int) ($_POST['mi_id'] ?? $_GET['mi_id'] ?? $_SESSION[$module]['mi_id'] ?? 0);

if ($phase == 99) {
    header('Location:  MitglEhrgEdit.php?mi_id=' . $_SESSION[$module]['mi_id']);
}

# -------------------------------------------------------------------------------------------------------
# Überschreibe die Werte in array $neu - weitere Modifikationen in Edit_tn_check_v2.php !
# -------------------------------------------------------------------------------------------------------
if ($phase == 0) {
    if ($me_id == 0) {

        $neu['me_id'] = $me_id;
        $neu['mi_id'] = $mi_id;
        
        $neu['me_ehrung'] = $neu['me_eh_datum'] = $neu['me_begruendg'] = $neu['me_changed_at'] = "";
        $neu['me_bild1'] = $neu['me_bild2'] = $neu['me_bild3'] = $neu['me_bild4'] = "";
        $neu['me_changed_id'] = $_SESSION['BS_Prim']['BE']['be_id'];
    } else {
        try {
            #var_dump($me_id);
            $neu_0 = $mitgl->getMiEhrungById($me_id);
            
            $neu = $neu_0[0];
            unset($neu_0);
            $mi_id = (int) ($neu['mi_id'] ?? 0);
        } catch (Exception $e) {
            echo "Exception: " . $e->getMessage();
        }

    }
}

if ($phase == 1) {

    foreach ($_POST as $name => $value) {
        $neu[$name] = $value;
    }
    
    #var_dump($neu);
    #var_dump($_FILES);
 
   
    if (!empty($neu['me_eh_datum']) ) {
        $neu['me_eh_datum'] = convertInternationalDateToSql( $neu['me_eh_datum'], false);
    }
    
    foreach($neu as $key => $value ) {
        
        if (substr($key, 0, 3) == 'me_') {
            continue;
        }
        
        unset($neu[$key]);
    }

    $mi_id = intval($neu['mi_id']);
    $me_id = intval($neu['me_id']);
    
    if ($neu['me_id'] == 0) { # neueingabe
        
        try {
            $neu['mi_insert_id'] = $insert_id = $mitgl->createMiEhrung($neu);
        } catch (Exception $e) {
            echo "Exception: " . $e->getMessage();
        }

    } else { # update
       
        try {
            $neu['me_insert_id'] = $insert_id = $mitgl->updateMiEhrung($me_id, $neu);
        } catch (Exception $e) {
            echo "Exception: " . $e->getMessage();
        }

    }
    $target = "";
    if ($sub_mod == 'Ehrg') {
        $target = "MitglEdit.php?ID=" . urlencode($mi_id);
    } else {
        $target = "MitglEhrgList.php";
    }
    
    echo "Die Daten wurden gespeichert.<br>";
    echo "Sie werden gleich zurückgeleitet.";
   
    echo "<script>
             let target = " . json_encode($target) . ";
       console.log ('Rücksprung  ', target);
             setTimeout(function() {
             window.location.href = target;
            }, 2000); // 5 Sekunden warten
         </script>";
    exit;
   
}

switch ($phase) {
    case 0:
        require 'MitglEhrgEdit_ph0.inc.php';
        echo "<div class='w3-container'><fieldset><label>Weitere Ehrungen</label><br/>";
        require 'MitglEhrgList_inc.php';
        echo "</fieldset></div>";
        break;
}

$page->renderFooter();
?>