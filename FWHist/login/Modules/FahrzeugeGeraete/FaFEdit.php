<?php

declare(strict_types=1);

/**
 *Motorisierte- Fahrzeuge- Daten- Wartung
 *
 * @author Josef Rohowsky - neu 2026
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
        file_put_contents(__DIR__ . '/../../../login/AOrd_Verz/Logging/FAF_Edit_fatal_error.log', $message, FILE_APPEND);
    }
});

error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/../../../login/AOrd_Verz/Logging/FAF_Edit_php-error.log.txt');


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
use FWHist\Auth\AccessGuard;
use FWHist\Modules\FahrzeugeGeraete\API\DB_FzgGer;
use FWHist\Modules\FahrzeugeGeraete\API\FzgGerTableDefinitions;
use FWHist\Services\TableColumnMetadata;
use FWHist\Services\FormRendererFlex;
use FWHist\Services\HTMLPage;

$module = 'FAF-Edit';
$sub_mod = 'Edit';
$module = "FZG-GER";

$debug = False; // Debug output Ein/Aus Schalter

require $systemBasePath . 'login/Services/FuncsLib.php';
require $systemBasePath . 'login/Services/CommFuncsLib.php';
require $systemBasePath . 'login/Services/ConstLib.php';
require $systemBasePath . 'login/Services/UploadUnifiedLib.php';

$envManager = new EnvSessionManager();
$basePath = $envManager->getEnv('basePath')  ;
$configFileS = $envManager->getEnv('configFileS');

$header = "";

$page = new HTMLPage('Motorisierte Fahrzeuge- Daten Verwaltung',
    $systemBasePath,
    $basePath,
    ['headExtras' => $header,
        'type' => 'Form',
        'width' => '60em',
    ]
    );

$page->loadConfig($configFileS);

// ============================================================================================================
// Eingabenerfassung und Zugriffsprüfung
// ============================================================================================================
$DBD = new DB_GenericLog();
$guard = AccessGuard::fromSession($DBD);
if (!$guard->canReadMandantData()) {
    AccessGuard::deny('Anmeldung erforderlich.');
}

$rawEiId = $_POST['ei_id'] ?? $_GET['ei_id'] ?? null;
$ei_id = filter_var($rawEiId, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
if ($ei_id === false) {
    http_response_code(400);
    exit('Keine gültige Eigentümer-ID übergeben.');
}

$rawFzId = $_POST['fz_id'] ?? $_GET['fz_id'] ?? 0;
$fz_id = filter_var($rawFzId, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);
if ($fz_id === false) {
    http_response_code(400);
    exit('Keine gültige Fahrzeug-ID übergeben.');
}

$phase = filter_var($_POST['phase'] ?? 0, FILTER_VALIDATE_INT);
if ($phase === false || !in_array($phase, [0, 1], true)) {
    http_response_code(400);
    exit('Ungültige Formularphase.');
}

$ber = $guard->canWriteMandantData('FZG', $ei_id);
if ($phase === 1) {
    if (!$ber) {
        AccessGuard::deny('Für Änderungen an Fahrzeugen dieses Mandanten fehlt die Berechtigung.');
    }
    AccessGuard::requireCsrf();
}

$page->renderHeader();
initial_debug('POST','GET'); # Wenn $debug=true - Ausgabe von Debug Informationen: $_POST, $_GET, $_FILES

$pdo = $DBD->getPDO();
#var_dump($pdo);

$meta = new TableColumnMetadata($pdo,'fharch_new',false);
# var_dump($meta);

#echo __LINE__ . " fz_id $fz_id ei_id $ei_id<br>";
$columnsByTables = $meta->getColumnsForTables([$ei_id . '_fza_fahrzeug', $ei_id . '_fza-eigner' ]);

#var_dump($columnsByTables);
# var_dump($meta);
$fzgger = new DB_FzgGer($DBD, $ei_id);
#var_dump($mitgl);
#var_dump($_SERVER);
// ============================================================================================================
// Eingabenerfassung und defauls Teil 1 - alle POST Werte werden später in array $neu gestelltt
// ============================================================================================================

#var_dump($_GET);
#var_dump($_POST);

if (isset($_POST['proj'])) {
    $proj = $_POST['proj'];
}

#echo __LINE__ . " fz_id $fz_id ei_id $ei_id<br>";
# -------------------------------------------------------------------------------------------------------
# Überschreibe die Werte in array $neu - weitere Modifikationen in Edit_tn_check_v2.php !
# -------------------------------------------------------------------------------------------------------
if ($phase == 0) {
    if ($fz_id == 0) {
        $neu = [
            'fz_id' => 0,
            'ei_id' => $ei_id,
            "fz_invnr" => "0",
            'fz_sammlg' => 'MA_F',
            "sa_name" => "Kraftfahrzeug",
            'fz_name' => '',
            'fz_taktbez' => "",
            'fz_hist_bezeichng' => '',
            "fz_baujahr" => "",
            'fz_indienstst' => "",
            'fz_ausdienst' => "",
            'fz_zeitraum' => "",
            'fz_allg_beschr' => '',
            'fz_herstell_fg' => '',
            'fz_motor' => '',
            'fz_antrieb' => '',
            'fz_typ' => '',
            'fz_modell' => '',
            'fz_geschwindigkeit' => '',
            'fz_aufbauer' => '',
            'fz_aufb_typ' => '',
            'fz_besatzung' => '',
            'fz_bild_1' => "",
            'fz_b_1_komm' => "",
            'fz_bild_2' => "",
            'fz_b_2_komm' => "",
            'fz_bild_3' => "",
            'fz_b_3_komm' => "",
            'fz_bild_4' => "",
            'fz_b_4_komm' => "",
            'fz_zustand' => "",
            'fz_ctif_klass' => "",
            'fz_ctif_date' => "",
            'fz_ctif_juroren' => '',
            'fz_ctif_darst_jahr' => '',
            "fz_beschreibg_det" => "",
            "fz_eigent_freig" => "",
            "fz_verfueg_freig" => "",
            "fz_pruefg_id" => "",
            "fz_pruefg" => "",
            'fz_l_tank' => '',
            'fz_l_monitor' => '',
            'fz_l_pumpe' =>   '',
            'fz_t_kran' => '',
            'fz_t_winde' => '',
            'fz_t_leiter' => '',
            'fz_t_abschlepp' => '',
            'fz_t_geraet'    => '',
            'fz_t_beleuchtg' => '',
            'fz_t_strom' => '',
            'fz_g_atemsch' => '',
            'fz_changet_id' =>'',
            'fz_changed_at' => ""
        ];
        
    } else {
        
        $neu = $fzgger->getFaFById($fz_id);
        if ($neu === null || (isset($neu['ei_id']) && (int) $neu['ei_id'] !== $ei_id)) {
            http_response_code(404);
            exit('Fahrzeugdatensatz nicht gefunden.');
        }
        $neu['ei_id'] = $ei_id;
    }
}

if ($phase == 1) {
    $neu = [];
    if ($fz_id > 0) {
        $neu = $fzgger->getFaFById($fz_id);
        if ($neu === null || (isset($neu['ei_id']) && (int) $neu['ei_id'] !== $ei_id)) {
            http_response_code(404);
            exit('Fahrzeugdatensatz nicht gefunden.');
        }
    }

    foreach ($_POST as $name => $value)
    {
        if (is_string($value)) {
            $neu[$name] = trim($value);
        }
    }
    # var_dump($neu);
    date_default_timezone_set('Europe/Berlin');
    
    $neu['fz_changed_id'] = $_SESSION['BS_Prim']['BE']['be_id'];
    $neu['fz_changed_at'] =  date('Y-m-d H:m:s');
    
    if (($neu['abk_id'] ?? '') != '') {
        $neu['fz_taktbez'] = $neu['abk_id'];
    }
    if (($neu['firm_id'] ?? '') != '') {
        $neu['fz_herstell_fg'] = $neu['firm_id'];
    }
    if (($neu['aufb_id'] ?? '') != '') {
        $neu['fz_aufbauer'] = $neu['aufb_id'];
    }
    
    foreach ( $neu as $key => $val) {
        if (substr($key,0,3) != 'fz_' && substr($key,0,3) != 'ei_' ) {
            unset($neu[$key]);
        }
    }
    
    $neu['fz_id'] = $fz_id;
    $neu['ei_id'] = $ei_id;
    FzgGerTableDefinitions::ensureTenantTable($pdo, $ei_id, $ei_id . '_fza_fahrzeug');
    
    if ($neu['fz_id'] == 0) { # neuengabe
        $ret = $fzgger->insertFaF($neu);
    } else { # Update
        $ret = $fzgger->updateFaF($neu, ['fz_id' => $fz_id] );
    }
    
    #header("Location:  FzAList.php?ei_id=$ei_id");
    echo "Die Daten wurden gespeichert.<br>";
    echo "Sie werden gleich zurückgeleitet.";
    
    echo "<script>
    setTimeout(function() {
        window.location.href = 'FzAList.php?mand_id=" . $ei_id . "&sammlg=MA_F';
    }, 2000); // 2 Sekunden warten
    </script>";
    exit;
}

switch ($phase) {
    case 0:
        require ('FaFEdit_ph0_inc.php');
        break;
}

$page->renderFooter();

?>
