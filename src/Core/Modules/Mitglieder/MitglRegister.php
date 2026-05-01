<?php

/**
 * Mitgliederverwaltung, Wartung
 * 
 * @author Josef Rohowsky - neu 2026
 */
session_start();

$module = 'MVW';
$sub_mod = 'Reg';

$Zugr = "Alle";

/** PHP Error_log */
error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/MirglReg_php-error.log.txt');

/**
 * Angleichung an den Root-Path
 *
 * @var string $path2ROOT
 */
$path2ROOT = "../../../../";

require $path2ROOT . 'login/Basis/common/BS_FuncsLib.php';
require $path2ROOT . 'login/Basis/common/FS_CommFuncsLib.php';
require $path2ROOT . 'login/Basis/common/FS_ConstLib.php';

require $path2ROOT . 'vendor/autoload.php';

$ber = userBerechtigtOK($Zugr);
if (!$ber) {
    header("Location $path2ROOT/public/");
}

# $ber = userBerechtigtOK($Zugr);

use Fharch\Core\Database\DB_GenericLog;
use Fharch\Core\Services\TableColumnMetadata;
use Fharch\Core\Services\FormRendererFlex;
use Fharch\Core\Modules\Mitglieder\API\MI_MitgliederModule;

$TABUcss = true;
$header = "";
HTML_header('Mitglieder- Verwaltung', $header, 'Form', '90em'); # Parm: Titel,Subtitel,HeaderLine,Type,width

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
$mitgl = new MI_MitgliederModule($DBD);
# var_dump($mitgl);
#var_dump($_SERVER);

// -------------------- Konfiguration --------------------

// "System"-User / Admin-ID, der in created/changed Feldern steht
const SYSTEM_CREATED_ID_INT = 99999;    // für be.be_created_id (int)
const SYSTEM_CREATED_ID_STR = '99990';  // für fv_ben_dat fd_created_id/fd_changed_id (varchar)

// Token-Ablauf (optional)
const TOKEN_MAX_AGE_HOURS = 48;

// -------------------- Input --------------------
$token = $_GET['token'] ?? '';
$token = is_string($token) ? trim($token) : '';

$unguelt = $abgelaufen = $bestaetigt = false; // Wenn true - Meldung Token ungültig der abgelaufen. Antrag neu erstellen
$email = "";

$mi_id = $be_id = NULL;
$neu = [];

if ($token === '') $unguelt = true; 
if (strlen($token) > 128) $unguelt = true; 

# if (!$unguelt) {
    # $pdo->beginTransaction();
    
    // 1) Anmeldung anhand Token holen und sperren
    $stNeu = $pdo->prepare(
        "SELECT
            mi_neu_nr,
            mi_neu_chkd,
            mi_neu_token,
            mi_neu_tok_erz,
            mi_id,
            mi_mtyp, mi_org_typ, mi_org_name,
            mi_name, mi_vname, mi_titel, mi_n_titel,
            mi_dgr, mi_anrede, mi_gebtag,
            mi_staat, mi_plz, mi_ort, mi_anschr,
            mi_tel_handy, mi_fax,
            mi_email, mi_email_status,
            mi_vorst_funct, mi_ref_leit,
            mi_ref_int_2, mi_ref_int_3, mi_ref_int_4,
            mi_sterbdat, mi_eintrdat, mi_austrdat,
            mi_m_beitr_bez, mi_m_abo_bez,
            mi_m_beitr_bez_bis, mi_m_abo_bez_bis,
            mi_abo_ausg,
            mi_einv_art, mi_einversterkl, mi_einv_dat,
            mi_ehrung,
            mi_changed_id, mi_changed_at
         FROM fv_mi_anmeld
         WHERE mi_neu_token = :token
         LIMIT 1
         FOR UPDATE
    ");
    
    $stNeu->execute([':token' => $token]);
    $neu = $stNeu->fetch();
    
    var_dump($neu);
    if ($unguelt) {goto mail;}
    
    
    if (empty($neu) ) { // Kein Datensatz vorhanden - Meldung und Abbruch == ungültig
        $unguelt = true;
        echo __LINE__ . " Kein Datensatz vorhanden <br>";
        goto mail;
    }
    // 2) Bereits bestätigt?
    if (($neu['mi_neu_chkd'] ?? 'N') === 'J') {
        $bestaetigt = true;
        goto mail;
    }
    
    // 3) Optional: Ablaufzeit prüfen
    if (!empty($neu['mi_neu_tok_erz'])) {
        $createdAt = new DateTime((string)$neu['mi_neu_tok_erz']);
        $now = new DateTime('now');
        $diffHours = (((int)$now->format('U')) - ((int)$createdAt->format('U'))) / 3600;
        if ($diffHours > TOKEN_MAX_AGE_HOURS) {
            $abgelaufen = true;
            goto mail;
        }
    }
    # if (!$abgelaufen && !$bestaetigt) {
        // 4) E-Mail prüfen (wird be_uid)
        $email = trim((string)($neu['mi_email'] ?? ''));
        
        // -------------------- A) Mitglied in fv_mitglieder anlegen/aktualisieren --------------------
        $mi_id = $neu['mi_id'] ?? null;
        // Hilfsfunktion: array nur mit erlaubten Keys aufbauen
        $memberParams = [
            ':mi_mtyp' => $neu['mi_mtyp'] ?? null,
            ':mi_org_typ' => $neu['mi_org_typ'] ?? null,
            ':mi_org_name' => $neu['mi_org_name'] ?? null,
            ':mi_name' => $neu['mi_name'] ?? null,
            ':mi_vname' => $neu['mi_vname'] ?? null,
            ':mi_titel' => $neu['mi_titel'] ?? null,
            ':mi_n_titel' => $neu['mi_n_titel'] ?? null,
            ':mi_dgr' => $neu['mi_dgr'] ?? null,
            ':mi_anrede' => $neu['mi_anrede'] ?? null,
            ':mi_gebtag' => $neu['mi_gebtag'] ?? null,
            ':mi_staat' => $neu['mi_staat'] ?? null,
            ':mi_plz' => $neu['mi_plz'] ?? null,
            ':mi_ort' => $neu['mi_ort'] ?? null,
            ':mi_anschr' => $neu['mi_anschr'] ?? null,
            ':mi_tel_handy' => $neu['mi_tel_handy'] ?? null,
            ':mi_fax' => $neu['mi_fax'] ?? null,
            ':mi_email' => $email,
            ':mi_email_status' => $neu['mi_email_status'] ?? null,
            ':mi_vorst_funct' => $neu['mi_vorst_funct'] ?? null,
            ':mi_ref_leit' => $neu['mi_ref_leit'] ?? null,
            ':mi_ref_int_2' => $neu['mi_ref_int_2'] ?? null,
            ':mi_ref_int_3' => $neu['mi_ref_int_3'] ?? null,
            ':mi_ref_int_4' => $neu['mi_ref_int_4'] ?? null,
            ':mi_sterbdat' => $neu['mi_sterbdat'] ?? null,
            ':mi_eintrdat' => $neu['mi_eintrdat'] ?? null,
            ':mi_austrdat' => $neu['mi_austrdat'] ?? null,
            ':mi_m_beitr_bez' => $neu['mi_m_beitr_bez'] ?? null,
            ':mi_m_abo_bez' => $neu['mi_m_abo_bez'] ?? null,
            ':mi_m_beitr_bez_bis' => $neu['mi_m_beitr_bez_bis'] ?? null,
            ':mi_m_abo_bez_bis' => $neu['mi_m_abo_bez_bis'] ?? null,
            ':mi_abo_ausg' => $neu['mi_abo_ausg'] ?? null,
            ':mi_einv_art' => $neu['mi_einv_art'] ?? null,
            ':mi_einversterkl' => $neu['mi_einversterkl'] ?? null,
            ':mi_einv_dat' => $neu['mi_einv_dat'] ?? null,
            ':mi_ehrung' => $neu['mi_ehrung'] ?? null,
            ':mi_changed_id' => $neu['mi_changed_id'] ?? SYSTEM_CREATED_ID_INT,
            ':mi_changed_at' => $neu['mi_changed_at'] ?? null,
        ];
        
        if (empty($mi_id)) {
            $stInsMi = $pdo->prepare("
               INSERT INTO fv_mitglieder (
                  mi_mtyp, mi_org_typ, mi_org_name,
                  mi_name, mi_vname, mi_titel, mi_n_titel,
                  mi_dgr, mi_anrede, mi_gebtag,
                  mi_staat, mi_plz, mi_ort, mi_anschr,
                  mi_tel_handy, mi_fax,
                  mi_email, mi_email_status,
                  mi_vorst_funct, mi_ref_leit,
                  mi_ref_int_2, mi_ref_int_3, mi_ref_int_4,
                  mi_sterbdat, mi_eintrdat, mi_austrdat,
                  mi_m_beitr_bez, mi_m_abo_bez,
                  mi_m_beitr_bez_bis, mi_m_abo_bez_bis,
                  mi_abo_ausg,
                  mi_einv_art, mi_einversterkl, mi_einv_dat,
                  mi_ehrung,
                  mi_changed_id, mi_changed_at
              ) VALUES (
                 :mi_mtyp, :mi_org_typ, :mi_org_name,
                 :mi_name, :mi_vname, :mi_titel, :mi_n_titel,
                 :mi_dgr, :mi_anrede, :mi_gebtag,
                 :mi_staat, :mi_plz, :mi_ort, :mi_anschr,
                 :mi_tel_handy, :mi_fax,
                 :mi_email, :mi_email_status,
                 :mi_vorst_funct, :mi_ref_leit,
                 :mi_ref_int_2, :mi_ref_int_3, :mi_ref_int_4,
                 :mi_sterbdat, :mi_eintrdat, :mi_austrdat,
                 :mi_m_beitr_bez, :mi_m_abo_bez,
                 :mi_m_beitr_bez_bis, :mi_m_abo_bez_bis,
                 :mi_abo_ausg,
                 :mi_einv_art, :mi_einversterkl, :mi_einv_dat,
                 :mi_ehrung,
                 :mi_changed_id, :mi_changed_at
               )
            ");
             
            $stInsMi->execute($memberParams);
            $mi_id = (int)$pdo->lastInsertId();
            
            // mi_id zurück in fv_mi_anmeld schreiben
            $stUpdAnmeldMiId = $pdo->prepare("UPDATE fv_mi_anmeld SET mi_id = :mi_id WHERE mi_neu_nr = :nr");
            $stUpdAnmeldMiId->execute([':mi_id' => $mi_id, ':nr' => $neu['mi_neu_nr']]);
        } else {
            // Wenn ihr Updates nach Bestätigung wollt:
            $stUpdMi = $pdo->prepare("
              UPDATE fv_mitglieder SET
                 mi_mtyp = :mi_mtyp,
                 mi_org_typ = :mi_org_typ,
                 mi_org_name = :mi_org_name,
                 mi_name = :mi_name,
                 mi_vname = :mi_vname,
                 mi_titel = :mi_titel,
                 mi_n_titel = :mi_n_titel,
                 mi_dgr = :mi_dgr,
                 mi_anrede = :mi_anrede,
                 mi_gebtag = :mi_gebtag,
                 mi_staat = :mi_staat,
                 mi_plz = :mi_plz,
                 mi_ort = :mi_ort,
                 mi_anschr = :mi_anschr,
                 mi_tel_handy = :mi_tel_handy,
                 mi_fax = :mi_fax,
                 mi_email = :mi_email,
                 mi_email_status = :mi_email_status,
                 mi_vorst_funct = :mi_vorst_funct,
                 mi_ref_leit = :mi_ref_leit,
                 mi_ref_int_2 = :mi_ref_int_2,
                 mi_ref_int_3 = :mi_ref_int_3,
                 mi_ref_int_4 = :mi_ref_int_4,
                 mi_sterbdat = :mi_sterbdat,
                 mi_eintrdat = :mi_eintrdat,
                 mi_austrdat = :mi_austrdat,
                 mi_m_beitr_bez = :mi_m_beitr_bez,
                 mi_m_abo_bez = :mi_m_abo_bez,
                 mi_m_beitr_bez_bis = :mi_m_beitr_bez_bis,
                 mi_m_abo_bez_bis = :mi_m_abo_bez_bis,
                 mi_abo_ausg = :mi_abo_ausg,
                 mi_einv_art = :mi_einv_art,
                 mi_einversterkl = :mi_einversterkl,
                 mi_einv_dat = :mi_einv_dat,
                 mi_ehrung = :mi_ehrung,
                 mi_changed_id = :mi_changed_id,
                 mi_changed_at = :mi_changed_at
                 WHERE mi_id = :mi_id
             ");
            
            $memberParams[':mi_id'] = (int)$mi_id;
            $result = $stUpdMi->execute($memberParams);
        }
        
        // Sperren, um Race Conditions zu vermeiden
        $stBe = $pdo->prepare("SELECT be_id FROM fv_benutzer WHERE be_uid = :uid LIMIT 1 FOR UPDATE");
        $stBe->execute([':uid' => $email]);
        $be = $stBe->fetch();
        
        if (!$be) {
            
            $stInsBe = $pdo->prepare("
            INSERT INTO fv_benutzer (be_uid, be_2fa_secret, be_2fa_enabled, be_2fa_email, be_created_id, be_created_at, be_changed_id, be_changed_at)
                VALUES (:uid, :secret, :enabled, :email, :created_id, NOW(), :changed_id, NOW())
            ");
            $stInsBe->execute([
                ':uid' => $email,
                ':secret' => null,
                ':enabled' => 0,
                ':email' => $email,
                ':created_id' => SYSTEM_CREATED_ID_INT,
                ':changed_id' => SYSTEM_CREATED_ID_INT,
            ]);
            $be_id = (int)$pdo->lastInsertId();
        } else {
            $be_id = (int)$be['be_id'];
            
            // Optional: sicherstellen, dass be_2fa_email gesetzt ist (falls ihr es braucht)
            // $pdo->prepare("UPDATE be SET be_2fa_email = COALESCE(be_2fa_email, :email) WHERE be_id = :id")
            //     ->execute([':email' => $email, ':id' => $be_id]);
        }
        
        // -------------------- C) Benutzerdetails in fv_ben_dat upsert (verknüpft mit Mitglied) --------------------
        // Es kann Benutzer geben ohne Mitglied; hier: wenn Mitglied bestätigt, dann be_mi_id setzen.
        // Duplikatschutz: 1 Datensatz pro be_id (empfohlen). Falls bei euch anders: anpassen.
        $stFd = $pdo->prepare("SELECT fd_id FROM fv_ben_dat WHERE be_id = :be_id LIMIT 1 FOR UPDATE");
        $stFd->execute([':be_id' => $be_id]);
        $fd = $stFd->fetch();
        
        // Mapping mi_* → fd_*
        // Sinn-gemäß laut deiner Vorgabe:
        $fdParams = [
            ':be_id' => $be_id,
            ':be_mi_id' => $mi_id,
            
            ':fd_anrede' => $neu['mi_anrede'] ?? null,
            ':fd_tit_vor' => $neu['mi_titel'] ?? null,
            ':fd_vname' => $neu['mi_vname'] ?? null,
            ':fd_name' => $neu['mi_name'] ?? null,
            ':fd_tit_nach' => $neu['mi_n_titel'] ?? null,
            
            ':fd_adresse' => $neu['mi_anschr'] ?? null,
            ':fd_plz' => $neu['mi_plz'] ?? null,
            ':fd_ort' => $neu['mi_ort'] ?? null,
            ':fd_staat_abk' => $neu['mi_staat'] ?? null,
            
            ':fd_tel' => $neu['mi_tel_handy'] ?? null,
            ':fd_email' => $email,
            
            // nicht vorhanden in mi_*:
            ':fd_hp' => null,
            
            // Datum-Felder in fv_ben_dat sind varchar(12) – wir formatieren YYYY-MM-DD, falls vorhanden
            ':fd_sterb_dat' => !empty($neu['mi_sterbdat']) ? (string)$neu['mi_sterbdat'] : null,
            ':fd_austr_dat' => !empty($neu['mi_austrdat']) ? (string)$neu['mi_austrdat'] : null,
            
            ':fd_created_id' => SYSTEM_CREATED_ID_STR,
            ':fd_changed_id' => SYSTEM_CREATED_ID_STR,
        ];
        
        if (!$fd) {
            $stInsFd = $pdo->prepare("
               INSERT INTO fv_ben_dat (
                    be_id, be_mi_id,
                    fd_anrede, fd_tit_vor, fd_vname, fd_name, fd_tit_nach,
                    fd_adresse, fd_plz, fd_ort, fd_staat_abk,
                    fd_tel, fd_email, fd_hp,
                    fd_sterb_dat, fd_austr_dat,
                    fd_created_id, fd_created_at,
                    fd_changed_id, fd_changed_at
              ) VALUES (
                    :be_id, :be_mi_id,
                    :fd_anrede, :fd_tit_vor, :fd_vname, :fd_name, :fd_tit_nach,
                    :fd_adresse, :fd_plz, :fd_ort, :fd_staat_abk,
                    :fd_tel, :fd_email, :fd_hp,
                    :fd_sterb_dat, :fd_austr_dat,
                    :fd_created_id, NOW(),
                    :fd_changed_id, NOW()
             )
             ");
            $stInsFd->execute($fdParams);
        } else {
           $stUpdFd = $pdo->prepare("
              UPDATE fv_ben_dat SET
                  be_mi_id = :be_mi_id,
                  fd_anrede = :fd_anrede,
                  fd_tit_vor = :fd_tit_vor,
                  fd_vname = :fd_vname,
                  fd_name = :fd_name,
                  fd_tit_nach = :fd_tit_nach,
                  fd_adresse = :fd_adresse,
                  fd_plz = :fd_plz,
                  fd_ort = :fd_ort,
                  fd_staat_abk = :fd_staat_abk,
                  fd_tel = :fd_tel,
                  fd_email = :fd_email,
                  fd_hp = :fd_hp,
                  fd_sterb_dat = :fd_sterb_dat,
                  fd_austr_dat = :fd_austr_dat,
                  fd_changed_id = :fd_changed_id,
                  fd_changed_at = NOW()
                  WHERE be_id = :be_id
           ");
            $stUpdFd->execute($fdParams);
        }
        
        // -------------------- D) Anmeldung finalisieren: bestätigt setzen --------------------
        $stDone = $pdo->prepare("
            UPDATE fv_mi_anmeld
            SET mi_neu_chkd = 'J' , mi_neu_token = NULL
            WHERE mi_neu_nr = :nr
        ");
        $stDone->execute([':nr' => $neu['mi_neu_nr']]);
        
    #}
    /** 
     * Anlegen Berechtigungen
     * für neuen Benutzer
     * 
     * @var string $newPW
     */
    $nPw = 'NeuBen'.$mi_id;
    $newPW = password_hash($nPw,  PASSWORD_BCRYPT);
   
    $stInsErl =  $pdo->prepare("
        INSERT INTO fv_erlauben (be_id, fe_pw, fe_pw_chgd_id, fe_pw_chgd_at, fe_created_id, fe_created_at, fe_changed_id, fe_changed_at)
                VALUES (:beid, :secret, :pwchgd_id, now(), :created_id, NOW(), :changed_id, NOW()
            );
    ");
    $stInsErl->execute([
        ':beid' => $be_id,
        ':secret' => $newPW,
        ':pwchgd_id' => SYSTEM_CREATED_ID_INT,
        ':created_id' => SYSTEM_CREATED_ID_INT,
        ':changed_id' => SYSTEM_CREATED_ID_INT
    ]);
    $en_id = (int)$pdo->lastInsertId();
    /** 
     * Rollenzuordnung default ONU
     * 
     */
    #fv_rolle
    $stInsRolle =  $pdo->prepare("
         INSERT INTO fv_rolle (be_id, fl_id, fr_created_id, fr_created_at, fr_changed_id, fr_changed_at)
             VALUES (:beid, :flid, :created_id, NOW(), :changed_id, NOW()
             );

    ");
    $stInsRolle->execute([
        ':beid' => $be_id,
        ':flid' => 14,
        ':created_id' => SYSTEM_CREATED_ID_INT,
        ':changed_id' => SYSTEM_CREATED_ID_INT,
    ]);
# }

mail:

/** 
 * Abgeschlossen E- Main an den Mitgliedswerber eunstsprechend des Ablaufes
 * 
 * $unguelt = True  ...  ungültiger Token wurde gesendet
 * $abgelaufen = true  ... erst nach 48 Stunden gemeldet
 * $bestaetigt = true  ... wurde bereits angelegt
 * keiner als true markiert ... normaler Bestätigungs- Ablauf, Bestätigung, Passwort
 */
   
$text = "";
$msg = "";

if ($unguelt || $abgelaufen | !$bestaetigt) {
    if ($unguelt) { // ungültiger Token
        $subject = "Diese Anforderung ist ungültig - keine Daten vorhanden.";
        $msg = "<p>Bitte Anmeldung neu eingeben.</p>";
        echo "<p>Ungültiger Bestätigungscode - Abbruch. Bitte neu anmelden </p>";
        exit;
    } elseif ($abgelaufen){ // Bestätigun zu spaät (48 Stunden Frist)
        $subject = "Diese Anmeldeüberprüfung wurde zu spät abgeschickt.";
        $msg = "<p>Diese Anmeldeüberprüfung wurde nach mehr als 48 Stunden nach der Anmeldung gestarted. Bitte neu eingeben.</p>";
        echo "<p>Bestätingung wurde zu spät (nach mehr als 48 Stunden) abgeschickt - bitte neu anmelden </p>";
    } elseif ($bestaetigt) { // Anmeldung wurde bereits abgeschlossen
        $subject = "";
        $msg = "";
        echo "<p>Die Anmeldung wurde bereits abgeschlossen. Die Interne Seite ist bereits nutzbar </p>";
    } else {  // normaler Ablauf, Bestätigung Anmeldung und PW
        $subject = "Willkommen! Anmeldung Abgeschlossen";
        $msg = "<p>Die Anmeldung wurde damit abgeschlossen. Die interne Seite ist nun Nutzbar. Der Benutzer- Id ist die E-Mail- Adresse (exakt so geschrieben wie bei der Anmeldung)
              Das Passwort ist NeuBen$mi_id und es muss ehestmöglich geändert werden.</p>";
        echo "<p>Die Anmeldung wurde damit abgeschlossen. Die interne Seite ist nun Nutzbar. Der Benutzer- Id ist die E-Mail- Adresse (exakt so geschrieben wie bei der Anmeldung).</p>
              <p>Das Passwort ist NeuBen$mi_id und es muss ehestmöglich geändert werden.</p>
              <p>Die Mitglieds- Nummer ist $mi_id</p>.
              ";
        
    }
}

$anr = 'Werte Kameradin, Frau ';
if ($neu['mi_anrede'] == 'Hr.') {
    $anr = 'Werter Kamerad, Herr ';
}

// Standard- Nachricht

$text = $anr . $neu['mi_titel'] . " " . $neu['mi_vname'] . " " . $neu['mi_name'] . " " . $neu['mi_n_titel'] . ", ";
$text .= "<h2>Willkommen!</h2>";
$text .= $msg; //"";
$text .= "<p>Mit Kameradschaftlichen Grüßen, der Mitglieder- Verantwortliche.  $nPw </p>";
    
// Bestätigungs-Mail senden, Anzeigen!
sendEmail($email, $subject, $text);
    
$datum = date("d.m.Y:");
$zeit = date("H:i:s");
    
$dsn = $path2ROOT."data/AOrd_Verz/Logging/logs/AnmeldLog";

$log_rec = "Anmeldebestätigung der E-Mail und aktivierung der Daten \n ";
$log_rec .= "**** PFLICHTFELDER **** \nAnrede: " . $neu['mi_anrede'] . "\n";
$log_rec .= "Familienname: " . $neu['mi_name'] . "\n";
$log_rec .= "Vorname: " . $neu['mi_vname'] . "\n";
$log_rec .= "E-Mail: " . $neu['mi_email'] . "\n";
$log_rec .= "Adresse: " . $neu['mi_anschr'] . "\n";
$log_rec .= "PLZ: " . $neu['mi_plz'] . "\n";
$log_rec .= "Ort: " . $neu['mi_ort'] . "\n";
    
// ******** Optionale Felder ********
$log_rec .= "**** Optionale Felder ****\nTitel: " . $neu['mi_titel'] . "\n";
$log_rec .= "**** Optionale Felder ****\n nachfolg. Titel: " . $neu['mi_n_titel'] . "\n";
    
$log_rec .= "Tel Nummmer: " . $neu['mi_tel_handy'] . "\n";
$log_rec .= "Fax: " . $neu['mi_fax'] . "\n";
$log_rec .= "Geburtsdatum: " . $neu['mi_gebtag'] . "\n";
$log_rec .= "Oganisationstyp: " . $neu['mi_org_typ'] . "\n";
$log_rec .= "Organisation: " . $neu['mi_org_name'] . "\n";
$log_rec .= "Einverstaendniserklaerung: " . $neu['mi_einversterkl'] . " $datum $zeit " . $neu['mi_einv_art'] . "\n";
$log_rec .= "Mitgliedsnummer:  " . $neu['mi_id'] . "\n";
    
$text = " $log_rec ***** \"LOG ENDE\" *****\n";
$text .= "Orig.TCP = " . $_SERVER['REMOTE_ADDR'] . "\n";
    
$fname = writelog($dsn, $text);
$tr = array(
    "\n" => "<br>"
    
);
    
$text = strtr($text, $tr);
    
$adr_list = VS_Mail_Set('Mitgl');
    
sendEMail($neu['mi_email'] . ", $adr_list , josef@kexi.at", "VFHNÖ Mitglieds- Neuanmeldung ", $text); 
    
HTML_trailer();

?>