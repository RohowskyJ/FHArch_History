<?php

declare(strict_types=1);

/**
 * Mitgliederverwaltung - Aktivierung EINER bestätigten Anmeldung
 *
 * Ablauf:
 *   GET  ?mi_neu_nr=X   -> Kontroll-Formular mit den Anmeldedaten (Muster MitglEdit_ph0.inc.php)
 *   POST phase=1        -> nach Klick auf "Jetzt aktivieren": Mitglied/Benutzer/Rolle anlegen,
 *                           mi_anmeldung.mi_neu_aktiv = 'J' setzen, Willkommensmail versenden.
 *
 * Berechtigung: ADM-MI
 *
 * @author Josef Rohowsky - neu 2026-09-25
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

$systemBasePath = $_SESSION['BS_Prim']['RoBase'] ?? dirname(__DIR__, 3) . DIRECTORY_SEPARATOR;
require_once $systemBasePath . 'vendor/autoload.php';

use FWHist\Core\EnvSessionManager;
use FWHist\Auth\AccessGuard;
use FWHist\Database\DB_GenericLog;
use FWHist\AllgVerw\API\AdminMailAddressRepository;
use FWHist\Services\TableColumnMetadata;
use FWHist\Services\FormRendererFlex;
use FWHist\Services\HTMLPage;

register_shutdown_function(function () {
    $error = error_get_last();
    if ($error !== null) {
        $message = "Shutdown error detected:\n" . print_r($error, true);
        error_log($message);
        file_put_contents($systemBasePath .  '/FWHist/login/AOrd_Verz/MitglAktivate_fatal_error.log', $message, FILE_APPEND);
    }
});

error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');
ini_set('error_log', $systemBasePath .  '/FWHist/login/AOrd_Verz/MitglAktivate_php-error.log.txt');

$module = 'MVW';
$sub_mod = 'AKTIVATE';
require $systemBasePath . 'login/Services/FuncsLib.php';
require $systemBasePath . 'login/Services/CommFuncsLib.php';
require $systemBasePath . 'login/Services/ConstLib.php';
require $systemBasePath . 'login/Services/PHP_MailFuncsLib.php';

$DBD = new DB_GenericLog();
$guard = AccessGuard::fromSession($DBD);
if (!$guard->canWriteMemberData()) {
    AccessGuard::deny('Für diesen Programmteil besteht keine Berechtigung.');
}
$phase = (int) ($_POST['phase'] ?? 0);
if ($phase === 1) {
    AccessGuard::requireCsrf();
}

$envManager = new EnvSessionManager();
$basePath = $envManager->getEnv('basePath');
$configFileS = $envManager->getEnv('configFileS');

$page = new HTMLPage(
    'Mitglieds- Aktivierung',
    $systemBasePath,
    $basePath,
    [
        'headExtras' => '',
        'type' => 'Form',
        'width' => '60em',
    ]
);
$page->loadConfig($configFileS);
$page->renderHeader();

initial_debug('POST', 'GET');

$currentAdminId = $guard->userId();

$pdo = $DBD->getPDO();

$meta = new TableColumnMetadata($pdo, 'fharch_new', false);
$meta->getColumnsForTables(['mi_anmeldung']);

$phase = isset($_POST['phase']) ? (int)$_POST['phase'] : 0;
$mi_neu_nr = isset($_POST['mi_neu_nr']) ? (int)$_POST['mi_neu_nr'] : (int)($_GET['mi_neu_nr'] ?? 0);

if ($mi_neu_nr <= 0) {
    echo "<p>Fehler: keine Anmeldung angegeben. <a href='MitglAktivierung.php'>Zurück zur Liste</a></p>";
    $page->renderFooter();
    exit;
}

// ============================================================================
// Phase 1: Submit "Jetzt aktivieren" - eigentliche Aktivierung durchführen
// ============================================================================
if ($phase === 1) {

    $ergebnis = null;

    try {
        $DBD->beginTransaction();

        $stNeu = $pdo->prepare(
            "SELECT * FROM mi_anmeldung WHERE mi_neu_nr = :nr AND mi_neu_chkd = 'J' AND (mi_neu_aktiv = 'N' OR mi_neu_aktiv = ''  ) FOR UPDATE"
        );
        $stNeu->execute([':nr' => $mi_neu_nr]);
        $orig = $stNeu->fetch();

        if (!$orig) {
            throw new \RuntimeException("Anmeldung Nr. $mi_neu_nr nicht gefunden oder bereits aktiviert.");
        }

        // Admin-Korrekturen aus dem Formular übernehmen (Werte aus $_POST haben Vorrang vor $orig)
        $felder = [
            'mi_mtyp', 'mi_org_typ', 'mi_org_name', 'mi_name', 'mi_vname', 'mi_titel', 'mi_n_titel',
            'mi_dgr', 'mi_anrede', 'mi_gebtag', 'mi_staat', 'mi_plz', 'mi_ort', 'mi_anschr',
            'mi_tel_handy', 'mi_fax', 'mi_email', 'mi_email_status', 'mi_vorst_funct', 'mi_ref_leit',
            'mi_ref_int_2', 'mi_ref_int_3', 'mi_ref_int_4', 'mi_sterbdat', 'mi_eintrdat', 'mi_austrdat',
            'mi_m_beitr_bez', 'mi_m_abo_bez', 'mi_m_beitr_bez_bis', 'mi_m_abo_bez_bis', 'mi_abo_ausg',
            'mi_einv_art', 'mi_einversterkl', 'mi_einv_dat', 'mi_ehrung',
        ];
        $neu = [];
        foreach ($felder as $f) {
            $val = $_POST[$f] ?? $orig[$f] ?? null;
            $neu[$f] = ($val === '') ? null : $val;
        }

        $email = trim((string)$neu['mi_email']);
        if ($email === '') {
            throw new \RuntimeException('E-Mail-Adresse darf nicht leer sein.');
        }
        $mi_id = $orig['mi_id'] ?? null;

        // -------------------- A) Mitglied in mi_mitglieder anlegen/aktualisieren --------------------
        $memberParams = [];
        foreach ($felder as $f) {
            $memberParams[':' . $f] = $neu[$f];
        }
        $memberParams[':mi_changed_id'] = $currentAdminId;
        $memberParams[':mi_changed_at'] = date('Y-m-d H:i:s');

        if (empty($mi_id)) {
            $cols = implode(', ', $felder) . ', mi_changed_id, mi_changed_at';
            $phs  = implode(', ', array_map(fn($f) => ':' . $f, $felder)) . ', :mi_changed_id, :mi_changed_at';
            $stInsMi = $pdo->prepare("INSERT INTO mi_mitglieder ($cols) VALUES ($phs)");
            $stInsMi->execute($memberParams);
            $mi_id = $DBD->getLastInsertId();
        } else {
            $setParts = implode(', ', array_map(fn($f) => "$f = :$f", $felder));
            $memberParams[':mi_id'] = (int)$mi_id;
            $stUpdMi = $pdo->prepare("UPDATE mi_mitglieder SET $setParts, mi_changed_id = :mi_changed_id, mi_changed_at = :mi_changed_at WHERE mi_id = :mi_id");
            $stUpdMi->execute($memberParams);
        }

        // -------------------- B) Benutzer in fv_benutzer anlegen (falls noch nicht vorhanden) --------------------
        $stBe = $pdo->prepare("SELECT be_id FROM fv_benutzer WHERE be_uid = :uid LIMIT 1 FOR UPDATE");
        $stBe->execute([':uid' => $email]);
        $be = $stBe->fetch();

        $istNeuerBenutzer = !$be;
        $nPw = null;

        if ($istNeuerBenutzer) {
            $stInsBe = $pdo->prepare("
                INSERT INTO fv_benutzer (be_uid, be_2fa_secret, be_2fa_enabled, be_2fa_email, be_created_id, be_created_at, be_changed_id, be_changed_at)
                VALUES (:uid, NULL, 0, :email, :created_id, NOW(), :changed_id, NOW())
            ");
            $stInsBe->execute([
                ':uid' => $email, ':email' => $email,
                ':created_id' => $currentAdminId, ':changed_id' => $currentAdminId,
            ]);
            $be_id = $DBD->getLastInsertId();
        } else {
            $be_id = (int)$be['be_id'];
        }

        // -------------------- C) Benutzerdetails in fv_ben_dat upsert --------------------
        $stFd = $pdo->prepare("SELECT fd_id FROM fv_ben_dat WHERE be_id = :be_id LIMIT 1 FOR UPDATE");
        $stFd->execute([':be_id' => $be_id]);
        $fd = $stFd->fetch();

        // Gemeinsame Felder für Insert UND Update
        $fdParamsCommon = [
            ':be_id' => $be_id, ':be_mi_id' => $mi_id,
            ':fd_anrede' => $neu['mi_anrede'], ':fd_tit_vor' => $neu['mi_titel'],
            ':fd_vname' => $neu['mi_vname'], ':fd_name' => $neu['mi_name'],
            ':fd_tit_nach' => $neu['mi_n_titel'], ':fd_adresse' => $neu['mi_anschr'],
            ':fd_plz' => $neu['mi_plz'], ':fd_ort' => $neu['mi_ort'],
            ':fd_staat_abk' => $neu['mi_staat'], ':fd_tel' => $neu['mi_tel_handy'],
            ':fd_email' => $email, ':fd_hp' => null,
            ':fd_sterb_dat' => $neu['mi_sterbdat'], ':fd_austr_dat' => $neu['mi_austrdat'],
            ':fd_changed_id' => (string)$currentAdminId,
        ];

        if (!$fd) {
            // Beim Insert zusätzlich fd_created_id - NICHT in $fdParamsCommon, da beim
            // Update kein :fd_created_id-Platzhalter in der SQL existiert (führte zu
            // "SQLSTATE[HY093]: Invalid parameter number: parameter was not defined",
            // weil PDO bei nativen Prepared Statements keine überzähligen, nicht in der
            // SQL referenzierten Array-Schlüssel toleriert).
            $fdParamsInsert = $fdParamsCommon + [':fd_created_id' => (string)$currentAdminId];

            $stInsFd = $pdo->prepare("
               INSERT INTO fv_ben_dat (
                    be_id, be_mi_id, fd_anrede, fd_tit_vor, fd_vname, fd_name, fd_tit_nach,
                    fd_adresse, fd_plz, fd_ort, fd_staat_abk, fd_tel, fd_email, fd_hp,
                    fd_sterb_dat, fd_austr_dat, fd_created_id, fd_created_at, fd_changed_id, fd_changed_at
              ) VALUES (
                    :be_id, :be_mi_id, :fd_anrede, :fd_tit_vor, :fd_vname, :fd_name, :fd_tit_nach,
                    :fd_adresse, :fd_plz, :fd_ort, :fd_staat_abk, :fd_tel, :fd_email, :fd_hp,
                    :fd_sterb_dat, :fd_austr_dat, :fd_created_id, NOW(), :fd_changed_id, NOW()
             )");
            $stInsFd->execute($fdParamsInsert);
        } else {
            $stUpdFd = $pdo->prepare("
              UPDATE fv_ben_dat SET
                  be_mi_id = :be_mi_id, fd_anrede = :fd_anrede, fd_tit_vor = :fd_tit_vor,
                  fd_vname = :fd_vname, fd_name = :fd_name, fd_tit_nach = :fd_tit_nach,
                  fd_adresse = :fd_adresse, fd_plz = :fd_plz, fd_ort = :fd_ort, fd_staat_abk = :fd_staat_abk,
                  fd_tel = :fd_tel, fd_email = :fd_email, fd_hp = :fd_hp, fd_sterb_dat = :fd_sterb_dat,
                  fd_austr_dat = :fd_austr_dat, fd_changed_id = :fd_changed_id, fd_changed_at = NOW()
                  WHERE be_id = :be_id
           ");
            $stUpdFd->execute($fdParamsCommon);
        }

        // -------------------- D) + E) Zugangsdaten + Rolle NUR bei neuem Benutzer --------------------
        if ($istNeuerBenutzer) {
            $nPw = 'NeuBen-' . bin2hex(random_bytes(4));
            $newPW = password_hash($nPw, PASSWORD_BCRYPT);

            $stInsErl = $pdo->prepare("
                INSERT INTO fv_erlauben (be_id, fe_pw, fe_pw_chgd_id, fe_pw_chgd_at, fe_created_id, fe_created_at, fe_changed_id, fe_changed_at)
                VALUES (:beid, :secret, :pwchgd_id, NOW(), :created_id, NOW(), :changed_id, NOW())
            ");
            $stInsErl->execute([
                ':beid' => $be_id, ':secret' => $newPW,
                ':pwchgd_id' => $currentAdminId, ':created_id' => $currentAdminId, ':changed_id' => $currentAdminId,
            ]);

            $stRole = $pdo->query(
                "SELECT fl_id, fl_modules FROM fv_rollen_beschr"
            );
            $memberRoleId = null;
            foreach ($stRole->fetchAll(PDO::FETCH_ASSOC) as $role) {
                $roleCodes = array_map('trim', explode(',', (string) ($role['fl_modules'] ?? '')));
                if (in_array('ONU-ALL', $roleCodes, true)) {
                    $memberRoleId = (int) $role['fl_id'];
                    break;
                }
            }
            if ($memberRoleId === null) {
                throw new \RuntimeException('Die Rolle ONU-ALL ist in fv_rollen_beschr nicht definiert.');
            }

            $stCheckRolle = $pdo->prepare("SELECT 1 FROM fv_rolle WHERE be_id = :beid AND fl_id = :flid LIMIT 1 FOR UPDATE");
            $stCheckRolle->execute([':beid' => $be_id, ':flid' => $memberRoleId]);
            if (!$stCheckRolle->fetch()) {
                $stInsRolle = $pdo->prepare("
                     INSERT INTO fv_rolle (be_id, fl_id, fr_created_id, fr_created_at, fr_changed_id, fr_changed_at)
                         VALUES (:beid, :flid, :created_id, NOW(), :changed_id, NOW())
                ");
                $stInsRolle->execute([
                    ':beid' => $be_id, ':flid' => $memberRoleId,
                    ':created_id' => $currentAdminId, ':changed_id' => $currentAdminId,
                ]);
            }
        }

        // -------------------- F) Anmeldung finalisieren --------------------
        $DBD->update(
            'mi_anmeldung',
            ['mi_id' => $mi_id, 'mi_neu_aktiv' => 'J'],
            ['mi_neu_nr' => $mi_neu_nr],
            '',
            false
        );

        $DBD->commit();

        $ergebnis = ['ok' => true, 'neu' => $neu, 'email' => $email, 'mi_id' => $mi_id, 'nPw' => $nPw, 'istNeuerBenutzer' => $istNeuerBenutzer];
    } catch (\Throwable $e) {
        $DBD->rollBack();
        error_log('MitglAktivate: Fehler bei Aktivierung mi_neu_nr=' . $mi_neu_nr . ': ' . $e->getMessage());
        echo "<p>Es ist ein Fehler aufgetreten: " . htmlspecialchars($e->getMessage(), ENT_QUOTES) . "</p>";
        echo "<p><a href='MitglAktivierung.php'>Zurück zur Liste</a></p>";

        $page->renderFooter();
        exit;
    }

    // -------------------- Willkommensmail + Log NACH erfolgreichem Commit --------------------
    $neu = $ergebnis['neu'];
    $anr = (($neu['mi_anrede'] ?? '') === 'Hr.') ? 'Werter Kamerad, Herr ' : 'Werte Kameradin, Frau ';
    $text = $anr . trim(($neu['mi_titel'] ?? '') . ' ' . ($neu['mi_vname'] ?? '') . ' ' . ($neu['mi_name'] ?? '') . ' ' . ($neu['mi_n_titel'] ?? '')) . ", <h2>Willkommen!</h2>";
    if ($ergebnis['istNeuerBenutzer']) {
        $text .= "<p>Die Anmeldung wurde abgeschlossen. Die interne Seite ist nun nutzbar. "
               . "Der Benutzer-Id ist die E-Mail-Adresse. Das Passwort ist <b>" . htmlspecialchars($ergebnis['nPw'], ENT_QUOTES) . "</b> "
               . "und muss ehestmöglich geändert werden.</p><p>Die Mitglieds-Nummer ist " . (int)$ergebnis['mi_id'] . ".</p>";
    } else {
        $text .= "<p>Ihre Mitgliedsdaten wurden aktualisiert. Ihre bestehenden Zugangsdaten bleiben unverändert.</p>";
    }
    $text .= "<p>Mit Kameradschaftlichen Grüßen, der Mitglieder- Verantwortliche.</p>";
       
    $adr_list = (new \FWHist\AllgVerw\API\AdminMailAddressRepository($pdo))
    ->getActiveAddressesByGroup('Mitgl');
    echo __LINE__ . " $adr_list , josef@kexi.at  ,  VFHNÖ Mitglieds- Neuanmeldung 1. Teil , $text <br>";
    
    /** falls keine Zusatzmail definiert wurde, an die Default Mail- Adresse schicken */
    if ($adr_list == '') {
        // Mail an den ANtragssteller
        $ini_arr = parse_ini_file($basePath . 'login/common/config_s.ini',True,INI_SCANNER_NORMAL);
        $adr_list = $ini_arr['Config']['vema'];
    }
   
    sendEmail($ergebnis['email'], "Willkommen! Anmeldung abgeschlossen", $text);

    $dsn = "AnmeldLog";
    $log_rec = "Aktivierung durch Admin (be_id=$currentAdminId) am " . date("d.m.Y H:i:s") . "\n";
    $log_rec .= "Anmeldung-Nr.: $mi_neu_nr -> Mitgliedsnummer: " . $ergebnis['mi_id'] . "\n";
    $log_rec .= "Neuer Benutzer angelegt: " . ($ergebnis['istNeuerBenutzer'] ? 'Ja' : 'Nein, bestehender Benutzer verknüpft') . "\n";
    writelog($dsn, $log_rec);

    echo "<p>Aktivierung abgeschlossen. Mitgliedsnummer: " . (int)$ergebnis['mi_id'] . "</p>";
    echo "<p><a href='MitglAktivierung.php'>Zurück zur Liste offener Anmeldungen</a></p>";
    
    echo "Die Daten wurden gespeichert.<br>";
    echo "Sie werden gleich zurückgeleitet.";
    
    echo '<script>
    setTimeout(function() {
        window.location.href = "MitglAktivierung.php";
    }, 2000); // 2 Sekunden warten
</script>';
    exit;
    
    $page->renderFooter();
    exit;
}

// ============================================================================
// Phase 0: Kontroll-Formular anzeigen
// ============================================================================
$neu = $DBD->fetchOne(
    "SELECT * FROM mi_anmeldung WHERE mi_neu_nr = :nr AND mi_neu_chkd = 'J' AND (mi_neu_aktiv = 'N' OR mi_neu_aktiv = '' ) LIMIT 1",
    [':nr' => $mi_neu_nr]
);

if ($neu === null) {
    echo "<p>Anmeldung Nr. $mi_neu_nr nicht gefunden oder bereits aktiviert.</p>";
    echo "<p><a href='MitglAktivierung.php'>Zurück zur Liste</a></p>";
    $page->renderFooter();
    exit;
}

$editProtect = false;
$forms = new FormRendererFlex($meta, 0, $neu, [], $editProtect, $module);

echo "<div class='white'>";
echo "<form method='post' action='MitglAktivate.php' onsubmit=\"return confirm('Anmeldung Nr. $mi_neu_nr jetzt endgültig aktivieren? Mitglied, Benutzer und Zugangsdaten werden angelegt.');\">";
echo "<input type='hidden' name='phase' value='1'>";
echo AccessGuard::csrfField();
echo "<input type='hidden' name='mi_neu_nr' value='" . (int)$neu['mi_neu_nr'] . "'>";

echo $forms->renderHeader('Anmeldedaten prüfen (Nr. ' . (int)$neu['mi_neu_nr'] . ')');

echo $forms->renderTrenner('Organisations- Daten');
echo $forms->renderSelectFieldFlex('mi_org_typ', M_Org);
echo $forms->renderTextLikeFieldFlex('mi_org_name', 50);

echo $forms->renderTrenner('Mitglieds-Daten');
echo $forms->renderSelectFieldFlex('mi_mtyp', M_Typ);
echo $forms->renderSelectFieldFlex('mi_anrede', ["Fr." => "Frau", "Hr." => "Herr"]);
echo $forms->renderTextLikeFieldFlex('mi_dgr', 10, 'FF Dienstgrad');
echo $forms->renderTextLikeFieldFlex('mi_titel', 10);
echo $forms->renderTextLikeFieldFlex('mi_name', 50);
echo $forms->renderTextLikeFieldFlex('mi_vname', 50);
echo $forms->renderTextLikeFieldFlex('mi_n_titel', 10);
echo $forms->renderTextLikeFieldFlex('mi_gebtag', 10, '', "type='date'");
echo $forms->renderTextLikeFieldFlex('mi_staat', 0);
AutoCompForm_Staat();
echo $forms->renderTextLikeFieldFlex('mi_anschr', 50);
echo $forms->renderTextLikeFieldFlex('mi_plz', 7);
echo $forms->renderTextLikeFieldFlex('mi_ort', 50);
echo $forms->renderTextLikeFieldFlex('mi_tel_handy', 50);
echo $forms->renderTextLikeFieldFlex('mi_fax', 50);
echo $forms->renderTextLikeFieldFlex('mi_email', 50);

echo $forms->renderTrenner('Mitarbeit, Funktion');
echo $forms->renderSelectFieldFlex('mi_vorst_funct', V_Funktion);
echo $forms->renderSelectFieldFlex('mi_ref_leit', L_Funktion);

echo $forms->renderTrenner('Wo sind meine Interessen: ');
echo $forms->renderCheckboxFieldFlex('mi_ref_int_2', VF_Referate_anmeld[2]);
echo $forms->renderCheckboxFieldFlex('mi_ref_int_3', VF_Referate_anmeld[3]);
echo $forms->renderCheckboxFieldFlex('mi_ref_int_4', VF_Referate_anmeld[4]);

echo $forms->renderTrenner('Ein- Austritt, Sterbedatum');
echo $forms->renderTextLikeFieldFlex('mi_eintrdat', 15, '', "type='date'");
echo $forms->renderTextLikeFieldFlex('mi_austrdat', 15, '', "type='date'");
echo $forms->renderTextLikeFieldFlex('mi_sterbdat', 15, '', "type='date'");

echo $forms->renderTrenner('Datenschutz- Erklärung (DSVGO)');
echo $forms->renderSelectFieldFlex('mi_einv_art', M_Einv, '', '', 'readonly');
echo $forms->renderTextLikeFieldFlex('mi_einversterkl', 0, '', '', 'readonly');
echo $forms->renderTextLikeFieldFlex('mi_einv_dat', 0, '', '', 'readonly');

echo "<p>Nach Kontrolle (und ggf. Korrektur der Daten oben) mit ";
echo "<button type='submit' class='green'>Jetzt aktivieren</button> Mitglied, Benutzer und Zugangsdaten anlegen.</p>";
echo "<p><a href='MitglAktivierung.php'>Abbrechen, zurück zur Liste</a></p>";

echo "</form>";
echo "</div>";

echo "<script type='text/javascript' src='" . $basePath . "/FWHist/js/AutoComp_Staat.js'></script>";

$page->renderFooter();
