<?php

declare(strict_types=1);

/**
 * Buch- Rezensionen, Wartung
 *
 * @author Josef Rohowsky - neu 2020
 *
 * RBAC-Umstellung (Referenz-Implementierung für Module mit Freigabe):
 *  - Berechtigung zentral über FWHist\Auth\AccessGuard (kein userHasRole()/$Zugr mehr im Script)
 *  - nicht freigegebene Einträge sind nur für Bearbeiter (ADM-OEF) und Freigeber (ADM-FREI) sichtbar
 *  - freigegebene Einträge ändert nur ADM-FREI; Freigabe/Rücknahme nur ADM-FREI
 *  - nur Felder aus einer Whitelist werden übernommen (bu_frei_* werden serverseitig gesetzt)
 *  - CSRF-Token für das Speichern
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
        file_put_contents(__DIR__ . '/../../../login/AOrd_Verz/Logging/BU_Edit_fatal_error.log', $message, FILE_APPEND);
    }
});

error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/../../../login/AOrd_Verz/Logging/BU_Edit_php-error.log.txt');
    
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

$systemBasePath = $_SESSION['BS_Prim']['RoBase'] ?? dirname(__DIR__, 3) . DIRECTORY_SEPARATOR;

require_once $systemBasePath . 'vendor/autoload.php';

use FWHist\Auth\AccessGuard;
use FWHist\Core\EnvSessionManager;
use FWHist\Database\DB_GenericLog;
use FWHist\Modules\Oeffentlichkeitsarbeit\API\DB_Oeffentlich;
use FWHist\Services\TableColumnMetadata;
use FWHist\Services\FormRendererFlex;
use FWHist\Services\FormRendererFlex_Ext;
use FWHist\Services\HTMLPage;

$envManager = new EnvSessionManager();

$module = 'OEF-BU';
$sub_mod = 'BU';

require $systemBasePath . 'login/Services/FuncsLib.php';
require $systemBasePath . 'login/Services/CommFuncsLib.php';
require $systemBasePath . 'login/Services/ConstLib.php';
require $systemBasePath . 'login/Services/UploadUnifiedLib.php';

$basePath = $envManager->getEnv('basePath');
$configFileS = $envManager->getEnv('configFileS');

// ============================================================================================================
// Datenbank, Datensatz und Berechtigung - VOR jeder Ausgabe
// ============================================================================================================
$DBD = new DB_GenericLog();
$pdo = $DBD->getPDO();
$guard = AccessGuard::fromSession($DBD);
$isPublicView = AccessGuard::isPublicView();
// Ansehen: intern jeder Angemeldete (schreibgeschützt), extern nur freigegebene Module der Whitelist
if (!$guard->canViewEditForm('BU', $isPublicView)) {
    AccessGuard::deny('Keine Berechtigung.');
}

$links = new DB_Oeffentlich($DBD);

$phase = (int) ($_POST['phase'] ?? 0);
$bu_id = (int) ($_POST['bu_id'] ?? $_GET['ID'] ?? 0);

$record = null;
$released = false;
if ($bu_id !== 0) {
    $rows = $links->getBuecherById($bu_id);
    $record = $rows[0] ?? null;
    if ($record === null) {
        AccessGuard::deny('Eintrag nicht gefunden.', 404);
    }
    $released = ($record['bu_frei_stat'] ?? '') === 'F';
}

$canRelease = $guard->canApproveReviews();
$canEdit = $guard->canEditReview($released); // false in der externen Ansicht

if ($bu_id === 0 && !$canEdit) {
    AccessGuard::deny('Zum Anlegen einer Buchbesprechung ist die Berechtigung ADM-OEF erforderlich.');
}
// Nicht freigegebene Einträge sind nur für Bearbeiter und Freigeber sichtbar (kein Durchzählen der IDs)
if ($bu_id !== 0 && !$released && !$guard->canSeeUnreleasedReviews()) {
    AccessGuard::deny('Eintrag nicht gefunden.', 404);
}
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && $phase === 1) {
    if (!$canEdit) {
        AccessGuard::deny('Änderungen sind nicht erlaubt.');
    }
    if (!AccessGuard::checkCsrf($_POST['csrf'] ?? null)) {
        AccessGuard::deny('Ungültige Anfrage (Sicherheits-Token fehlt oder ist abgelaufen). Bitte Seite neu laden.');
    }
}

// ============================================================================================================
// Seite
// ============================================================================================================
$header = "";

$page = new HTMLPage('Buch- Rezensionen',
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

$meta = new TableColumnMetadata($pdo,'fharch_new',false);
$columnsByTables = $meta->getColumnsForTables(['oe_buecher' ]);

// ============================================================================================================
// Phase 0: Anzeige
// ============================================================================================================
if ($phase === 0) {
    if ($bu_id === 0) {
        $neu['bu_id'] = $bu_id;
        $neu['bu_titel'] = $neu['bu_utitel'] = $neu['bu_author'] = $neu['bu_verlag'] = "";
        $neu['bu_isbn'] = $neu['bu_preis'] = $neu['bu_seiten'] = $neu['bu_bilder_anz'] = $neu['bu_bilder_art'] = "";
        $neu['bu_format'] = $neu['bu_teaser'] = $neu['bu_text'] = $neu['bu_bild_1'] = $neu['bu_text_1'] = "";
        $neu['bu_bild_2'] = $neu['bu_text_2'] = $neu['bu_bild_3'] = $neu['bu_text_3'] = $neu['bu_bild_4'] = $neu['bu_text_4'] = "";
        $neu['bu_bild_5'] = $neu['bu_text_5'] = $neu['bu_bild_6'] = $neu['bu_text_6'] =  "";
        $neu['bu_bew_ges'] = $neu['bu_bew_bild'] = $neu['bu_txt'] = $neu['bu_editor'] =  "";
        $neu['bu_edit_dat'] = $neu['bu_frei_dat'] = '0000-00-00';
        $neu['bu_ed_id'] = $neu['bu_frei_id'] = 0;
        $neu['bu_frei_stat'] = "U";
        $neu['bu_changed_id'] = $neu['bu_changed_at'] = "";
        $neu['ei_id'] = 0; // notwendig wegen Massupload
    } else {
        $neu = $record;
        $neu['ei_id'] = 0; // notwendig wegen Massupload

        if ($debug) {
            echo '<pre class=debug>';
            echo '<hr>$neu: ';
            var_dump($neu);
            echo '</pre>';
        }
    }
}

// ============================================================================================================
// Phase 1: Speichern (Berechtigung und CSRF sind oben bereits geprüft)
// ============================================================================================================
if ($phase === 1) {
    // Nur diese Felder werden aus dem Formular übernommen. bu_frei_* und bu_changed_* setzt der Server.
    $allowedFields = [
        'bu_titel', 'bu_utitel', 'bu_author', 'bu_verlag', 'bu_isbn', 'bu_preis', 'bu_seiten',
        'bu_bilder_anz', 'bu_bilder_art', 'bu_format', 'bu_teaser', 'bu_text',
        'bu_bew_ges', 'bu_bew_bild', 'bu_bew_txt',
        'bu_editor', 'bu_ed_id', 'bu_edit_dat',
        'bu_bild_1', 'bu_bild_2', 'bu_bild_3', 'bu_bild_4', 'bu_bild_5', 'bu_bild_6',
    ];

    $neu = [];
    foreach ($allowedFields as $field) {
        $value = $_POST[$field] ?? null;
        if (is_string($value)) {
            $neu[$field] = trim($value);
        }
    }
    foreach (['bu_bild_1', 'bu_bild_2', 'bu_bild_3', 'bu_bild_4', 'bu_bild_5', 'bu_bild_6'] as $field) {
        if (isset($neu[$field])) {
            $neu[$field] = basename($neu[$field]); // nur Dateinamen, keine Pfade
        }
    }

    date_default_timezone_set('Europe/Berlin');

    $neu['bu_id'] = $bu_id;
    $neu['bu_ed_id'] = (int) ($neu['bu_ed_id'] ?? 0);
    $neu['bu_seiten'] = (int) ($neu['bu_seiten'] ?? 0);
    $neu['bu_changed_id'] = $guard->userId();

    // Freigabe-Felder (bu_frei_*): Neuanlage = unfrei; Freigabe/Rücknahme nur ADM-FREI, Freigeber und Datum setzt der Server
    if (isset($_POST['bu_frei_stat']) && is_string($_POST['bu_frei_stat'])) {
        $neu['bu_frei_stat'] = $_POST['bu_frei_stat'];   // nur Wunsch; applyReviewRelease() entscheidet
    }
    $neu = $guard->applyReviewRelease($neu, $record, $guard->userId());
    
    unset($neu['csrf']);

    if ($bu_id === 0) { # Neueingabe
        $ret = $links->createBuecher($neu);
    } else { # Update
        $ret = $links->updateBuecher($bu_id, $neu);
    }

    echo "Die Daten wurden gespeichert.<br>";
    echo "Sie werden gleich zurückgeleitet.";

    echo '<script>
    setTimeout(function() {
        window.location.href = "BuchList.php";
    }, 2000); // 2 Sekunden warten
</script>';
    exit;
}

switch ($phase) {
    case 0:
        require ('BuchEdit_ph0_inc.php');
        break;
}
$page->renderFooter();
?>
