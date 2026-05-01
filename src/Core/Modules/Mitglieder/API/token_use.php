<!doctype html>
<html lang="de">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>MI_MemberRegistration_Chk_API.php – Vollständiges Skript (Token → Mitglied → Benutzer → Benutzerdetails)</title>
  <style>
    :root{
      --bg:#070a10;
      --panel:#0f1722;
      --panel2:#0c1420;
      --text:#eaf2ff;
      --muted:#b2c1d6;
      --line:rgba(255,255,255,.10);
      --accent:#86a9ff;
      --ok:#5df2c2;
      --warn:#ffd37a;
      --bad:#ff6d7d;
      --mono: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
      --shadow: 0 18px 70px rgba(0,0,0,.55);
    }
    html,body{height:100%}
    body{
      margin:0;
      color:var(--text);
      background:
        radial-gradient(900px 650px at 12% 18%, rgba(134,169,255,.13), transparent 55%),
        radial-gradient(880px 620px at 78% 12%, rgba(93,242,194,.10), transparent 55%),
        radial-gradient(1000px 700px at 40% 105%, rgba(255,109,125,.09), transparent 60%),
        var(--bg);
      font: 15px/1.6 system-ui, -apple-system, Segoe UI, Roboto, Ubuntu, Cantarell, "Noto Sans", "Helvetica Neue", Arial, sans-serif;
    }
    .wrap{max-width:1200px; margin:0 auto; padding:24px 16px 56px}
    header{
      display:flex; gap:16px; align-items:flex-start; justify-content:space-between;
      margin-bottom:16px;
    }
    h1{margin:0 0 6px; font-size:clamp(20px,2.2vw,30px); letter-spacing:.2px}
    p{margin:0; color:var(--muted); max-width:78ch}
    .pill{
      display:inline-flex; align-items:center; gap:8px;
      padding:8px 10px; border:1px solid var(--line);
      border-radius:999px; background:rgba(255,255,255,.04); box-shadow:var(--shadow)
    }
    .dot{width:8px; height:8px; border-radius:50%; background:var(--accent); box-shadow:0 0 0 4px rgba(134,169,255,.16)}
    .grid{display:grid; grid-template-columns: 1.25fr .75fr; gap:14px; align-items:start}
    @media (max-width: 980px){.grid{grid-template-columns:1fr}}
    .panel{
      background:linear-gradient(180deg, rgba(255,255,255,.06), rgba(255,255,255,.02));
      border:1px solid var(--line);
      border-radius:18px;
      box-shadow:var(--shadow);
      overflow:hidden;
    }
    .hd{padding:12px 14px; border-bottom:1px solid var(--line); background:rgba(15,23,34,.65); backdrop-filter: blur(9px)}
    .bd{padding:12px 14px}
    .k{font-family:var(--mono); font-size:13px; color:#dbe7ff}
    pre{
      margin:0; padding:14px;
      border-radius:14px;
      background:rgba(0,0,0,.35);
      border:1px solid rgba(255,255,255,.10);
      overflow:auto;
      white-space:pre;
      font-family:var(--mono);
      font-size:12.8px;
      line-height:1.45;
    }
    code{font-family:var(--mono)}
    .note{
      margin-top:10px;
      padding:10px 12px;
      border-left:3px solid rgba(134,169,255,.55);
      border-radius:12px;
      background:rgba(134,169,255,.07);
      color:var(--muted);
    }
    ul{margin:10px 0 0 18px; color:var(--muted)}
    .ok{color:var(--ok)} .warn{color:var(--warn)} .bad{color:var(--bad)}
  </style>
</head>
<body>
  <div class="wrap">
    <header>
      <div>
        <h1>Vollständiges PHP-Skript: Token bestätigen → <code>fv_mitglieder</code> → <code>be</code> → <code>fv_ben_dat</code></h1>
        <p>
          Annahmen laut deinen Angaben: <code>be_uid</code> ist immer <code>mi_email</code>.
          Verknüpfung Mitglied ↔ Benutzerdetails erfolgt in <code>fv_ben_dat</code> über <code>be_mi_id</code>.
          Feldmapping: <code>mi_*</code> kann sinn-gemäß auf <code>fd_*</code> gemappt werden (siehe Insert/Update unten).
        </p>
      </div>
      <div class="pill" aria-label="Technik">
        <span class="dot" aria-hidden="true"></span><span class="k">PDO • Transaction • FOR UPDATE • Duplikatschutz</span>
      </div>
    </header>

    <div class="grid">
      <section class="panel" aria-label="Skript">
        <div class="hd"><div class="k">MI_MemberRegistration_Chk_API.php (komplett)</div></div>
        <div class="bd">
<pre><code>&lt;?php
declare(strict_types=1);

/**
 * MI_MemberRegistration_Chk_API.php
 * Link: .../MI_MemberRegistration_Chk_API.php?token=...
 *
 * Tabellen:
 *  - fv_mi_anmeld  (Anmeldung/Hilfstabelle): enthält Token + gleiche mi_* Felder wie fv_mitglieder + spezifische Token-Felder
 *      Token-Felder (laut deiner ersten Liste):
 *        mi_neu_nr (PK), mi_neu_chkd ('N'/'J'), mi_neu_token (varchar64), mi_nau_tok_erz (datetime)
 *  - fv_mitglieder (Zieltabelle Mitglied): mi_* Felder
 *  - be            (Benutzer): be_uid = mi_email
 *  - fv_ben_dat    (Benutzerdetails): be_id, be_mi_id + fd_* Felder
 *
 * WICHTIG:
 *  - Passe ggf. DB-Zugangsdaten und ggf. Token-Feldnamen an (falls minimal abweichend).
 *  - In Produktion: Fehler ins Log, nicht an den Client.
 */

header('Content-Type: text/html; charset=utf-8');

// -------------------- Konfiguration --------------------
$dsn  = 'mysql:host=localhost;dbname=DEINE_DB;charset=utf8mb4';
$user = 'DB_USER';
$pass = 'DB_PASS';

// "System"-User / Admin-ID, der in created/changed Feldern steht
const SYSTEM_CREATED_ID_INT = 0;    // für be.be_created_id (int)
const SYSTEM_CREATED_ID_STR = '0';  // für fv_ben_dat fd_created_id/fd_changed_id (varchar)

// Token-Ablauf (optional)
const TOKEN_MAX_AGE_HOURS = 48;

// -------------------- Helpers --------------------
function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }

function respond(string $title, string $msg, int $code = 200): never {
  http_response_code($code);
  echo '&lt;!doctype html&gt;&lt;html lang="de"&gt;&lt;meta charset="utf-8"&gt;&lt;meta name="viewport" content="width=device-width,initial-scale=1"&gt;';
  echo '&lt;title&gt;' . h($title) . '&lt;/title&gt;';
  echo '&lt;body style="font-family:system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif; padding:18px; line-height:1.5"&gt;';
  echo '&lt;h2&gt;' . h($title) . '&lt;/h2&gt;';
  echo '&lt;p&gt;' . h($msg) . '&lt;/p&gt;';
  echo '&lt;/body&gt;&lt;/html&gt;';
  exit;
}

function fail(string $msg, int $code = 400): never { respond('Fehler', $msg, $code); }
function ok(string $msg): never { respond('Bestätigt', $msg, 200); }

// -------------------- Input --------------------
$token = $_GET['token'] ?? '';
$token = is_string($token) ? trim($token) : '';

if ($token === '') fail('Token fehlt.');
if (strlen($token) &gt; 128) fail('Token ungültig.');

// -------------------- DB Connect (PDO) --------------------
try {
  $pdo = new PDO($dsn, $user, $pass, [
    PDO::ATTR_ERRMODE =&gt; PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE =&gt; PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES =&gt; false,
  ]);
} catch (Throwable $e) {
  fail('DB-Verbindung fehlgeschlagen.', 500);
}

try {
  $pdo-&gt;beginTransaction();

  // 1) Anmeldung anhand Token holen und sperren
  $stNeu = $pdo-&gt;prepare(&lt;&lt;&lt;SQL
SELECT
  -- Token / Status Felder:
  mi_neu_nr,
  mi_neu_chkd,
  mi_neu_token,
  mi_nau_tok_erz,

  -- Mitgliedsfelder (aus fv_mi_anmeld, identisch zu fv_mitglieder):
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
SQL);

  $stNeu-&gt;execute([':token' =&gt; $token]);
  $neu = $stNeu-&gt;fetch();

  if (!$neu) {
    $pdo-&gt;rollBack();
    fail('Ungültiger Token.');
  }

  // 2) Bereits bestätigt?
  if (($neu['mi_neu_chkd'] ?? 'N') === 'J') {
    $pdo-&gt;rollBack();
    ok('Dieser Link wurde bereits bestätigt. Du kannst dich jetzt einloggen.');
  }

  // 3) Optional: Ablaufzeit prüfen
  if (!empty($neu['mi_nau_tok_erz'])) {
    $createdAt = new DateTime((string)$neu['mi_nau_tok_erz']);
    $now = new DateTime('now');
    $diffHours = (((int)$now-&gt;format('U')) - ((int)$createdAt-&gt;format('U'))) / 3600;
    if ($diffHours &gt; TOKEN_MAX_AGE_HOURS) {
      $pdo-&gt;rollBack();
      fail('Dieser Bestätigungslink ist abgelaufen. Bitte erneut registrieren.');
    }
  }

  // 4) E-Mail prüfen (wird be_uid)
  $email = trim((string)($neu['mi_email'] ?? ''));
  if ($email === '') {
    $pdo-&gt;rollBack();
    fail('E-Mail fehlt in der Anmeldung (mi_email).');
  }

  // -------------------- A) Mitglied in fv_mitglieder anlegen/aktualisieren --------------------
  $mi_id = $neu['mi_id'] ?? null;

  // Hilfsfunktion: array nur mit erlaubten Keys aufbauen
  $memberParams = [
    ':mi_mtyp' =&gt; $neu['mi_mtyp'] ?? null,
    ':mi_org_typ' =&gt; $neu['mi_org_typ'] ?? null,
    ':mi_org_name' =&gt; $neu['mi_org_name'] ?? null,
    ':mi_name' =&gt; $neu['mi_name'] ?? null,
    ':mi_vname' =&gt; $neu['mi_vname'] ?? null,
    ':mi_titel' =&gt; $neu['mi_titel'] ?? null,
    ':mi_n_titel' =&gt; $neu['mi_n_titel'] ?? null,
    ':mi_dgr' =&gt; $neu['mi_dgr'] ?? null,
    ':mi_anrede' =&gt; $neu['mi_anrede'] ?? null,
    ':mi_gebtag' =&gt; $neu['mi_gebtag'] ?? null,
    ':mi_staat' =&gt; $neu['mi_staat'] ?? null,
    ':mi_plz' =&gt; $neu['mi_plz'] ?? null,
    ':mi_ort' =&gt; $neu['mi_ort'] ?? null,
    ':mi_anschr' =&gt; $neu['mi_anschr'] ?? null,
    ':mi_tel_handy' =&gt; $neu['mi_tel_handy'] ?? null,
    ':mi_fax' =&gt; $neu['mi_fax'] ?? null,
    ':mi_email' =&gt; $email,
    ':mi_email_status' =&gt; $neu['mi_email_status'] ?? null,
    ':mi_vorst_funct' =&gt; $neu['mi_vorst_funct'] ?? null,
    ':mi_ref_leit' =&gt; $neu['mi_ref_leit'] ?? null,
    ':mi_ref_int_2' =&gt; $neu['mi_ref_int_2'] ?? null,
    ':mi_ref_int_3' =&gt; $neu['mi_ref_int_3'] ?? null,
    ':mi_ref_int_4' =&gt; $neu['mi_ref_int_4'] ?? null,
    ':mi_sterbdat' =&gt; $neu['mi_sterbdat'] ?? null,
    ':mi_eintrdat' =&gt; $neu['mi_eintrdat'] ?? null,
    ':mi_austrdat' =&gt; $neu['mi_austrdat'] ?? null,
    ':mi_m_beitr_bez' =&gt; $neu['mi_m_beitr_bez'] ?? null,
    ':mi_m_abo_bez' =&gt; $neu['mi_m_abo_bez'] ?? null,
    ':mi_m_beitr_bez_bis' =&gt; $neu['mi_m_beitr_bez_bis'] ?? null,
    ':mi_m_abo_bez_bis' =&gt; $neu['mi_m_abo_bez_bis'] ?? null,
    ':mi_abo_ausg' =&gt; $neu['mi_abo_ausg'] ?? null,
    ':mi_einv_art' =&gt; $neu['mi_einv_art'] ?? null,
    ':mi_einversterkl' =&gt; $neu['mi_einversterkl'] ?? null,
    ':mi_einv_dat' =&gt; $neu['mi_einv_dat'] ?? null,
    ':mi_ehrung' =&gt; $neu['mi_ehrung'] ?? null,
    ':mi_changed_id' =&gt; $neu['mi_changed_id'] ?? SYSTEM_CREATED_ID_INT,
    ':mi_changed_at' =&gt; $neu['mi_changed_at'] ?? null,
  ];

  if (empty($mi_id)) {
    $stInsMi = $pdo-&gt;prepare(&lt;&lt;&lt;SQL
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
SQL);

    $stInsMi-&gt;execute($memberParams);
    $mi_id = (int)$pdo-&gt;lastInsertId();

    // mi_id zurück in fv_mi_anmeld schreiben
    $stUpdAnmeldMiId = $pdo-&gt;prepare("UPDATE fv_mi_anmeld SET mi_id = :mi_id WHERE mi_neu_nr = :nr");
    $stUpdAnmeldMiId-&gt;execute([':mi_id' =&gt; $mi_id, ':nr' =&gt; $neu['mi_neu_nr']]);
  } else {
    // Wenn ihr Updates nach Bestätigung wollt:
    $stUpdMi = $pdo-&gt;prepare(&lt;&lt;&lt;SQL
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
SQL);

    $memberParams[':mi_id'] = (int)$mi_id;
    $stUpdMi-&gt;execute($memberParams);
  }

  // -------------------- B) Benutzer in "be" sicherstellen (be_uid = mi_email) --------------------
  // Sperren, um Race Conditions zu vermeiden
  $stBe = $pdo-&gt;prepare("SELECT be_id FROM be WHERE be_uid = :uid LIMIT 1 FOR UPDATE");
  $stBe-&gt;execute([':uid' =&gt; $email]);
  $be = $stBe-&gt;fetch();

  if (!$be) {
    $stInsBe = $pdo-&gt;prepare(&lt;&lt;&lt;SQL
INSERT INTO be (be_uid, be_2fa_secret, be_2fa_enabled, be_2fa_email, be_created_id)
VALUES (:uid, :secret, :enabled, :email, :created_id)
SQL);
    $stInsBe-&gt;execute([
      ':uid' =&gt; $email,
      ':secret' =&gt; null,
      ':enabled' =&gt; 0,
      ':email' =&gt; $email,
      ':created_id' =&gt; SYSTEM_CREATED_ID_INT,
    ]);
    $be_id = (int)$pdo-&gt;lastInsertId();
  } else {
    $be_id = (int)$be['be_id'];

    // Optional: sicherstellen, dass be_2fa_email gesetzt ist (falls ihr es braucht)
    // $pdo-&gt;prepare("UPDATE be SET be_2fa_email = COALESCE(be_2fa_email, :email) WHERE be_id = :id")
    //     -&gt;execute([':email' =&gt; $email, ':id' =&gt; $be_id]);
  }

  // -------------------- C) Benutzerdetails in fv_ben_dat upsert (verknüpft mit Mitglied) --------------------
  // Es kann Benutzer geben ohne Mitglied; hier: wenn Mitglied bestätigt, dann be_mi_id setzen.
  // Duplikatschutz: 1 Datensatz pro be_id (empfohlen). Falls bei euch anders: anpassen.
  $stFd = $pdo-&gt;prepare("SELECT fd_id FROM fv_ben_dat WHERE be_id = :be_id LIMIT 1 FOR UPDATE");
  $stFd-&gt;execute([':be_id' =&gt; $be_id]);
  $fd = $stFd-&gt;fetch();

  // Mapping mi_* → fd_*
  // Sinn-gemäß laut deiner Vorgabe:
  $fdParams = [
    ':be_id' =&gt; $be_id,
    ':be_mi_id' =&gt; $mi_id,

    ':fd_anrede' =&gt; $neu['mi_anrede'] ?? null,
    ':fd_tit_vor' =&gt; $neu['mi_titel'] ?? null,
    ':fd_vname' =&gt; $neu['mi_vname'] ?? null,
    ':fd_name' =&gt; $neu['mi_name'] ?? null,
    ':fd_tit_nach' =&gt; $neu['mi_n_titel'] ?? null,

    ':fd_adresse' =&gt; $neu['mi_anschr'] ?? null,
    ':fd_plz' =&gt; $neu['mi_plz'] ?? null,
    ':fd_ort' =&gt; $neu['mi_ort'] ?? null,
    ':fd_staat_abk' =&gt; $neu['mi_staat'] ?? null,

    ':fd_tel' =&gt; $neu['mi_tel_handy'] ?? null,
    ':fd_email' =&gt; $email,

    // nicht vorhanden in mi_*:
    ':fd_hp' =&gt; null,

    // Datum-Felder in fv_ben_dat sind varchar(12) – wir formatieren YYYY-MM-DD, falls vorhanden
    ':fd_sterb_dat' =&gt; !empty($neu['mi_sterbdat']) ? (string)$neu['mi_sterbdat'] : null,
    ':fd_austr_dat' =&gt; !empty($neu['mi_austrdat']) ? (string)$neu['mi_austrdat'] : null,

    ':fd_created_id' =&gt; SYSTEM_CREATED_ID_STR,
    ':fd_changed_id' =&gt; SYSTEM_CREATED_ID_STR,
  ];

  if (!$fd) {
    $stInsFd = $pdo-&gt;prepare(&lt;&lt;&lt;SQL
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
SQL);
    $stInsFd-&gt;execute($fdParams);
  } else {
    $stUpdFd = $pdo-&gt;prepare(&lt;&lt;&lt;SQL
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
SQL);
    $stUpdFd-&gt;execute($fdParams);
  }

  // -------------------- D) Anmeldung finalisieren: bestätigt setzen --------------------
  $stDone = $pdo-&gt;prepare(&lt;&lt;&lt;SQL
UPDATE fv_mi_anmeld
SET mi_neu_chkd = 'J'
WHERE mi_neu_nr = :nr
SQL);
  $stDone-&gt;execute([':nr' =&gt; $neu['mi_neu_nr']]);

  // Optional:
  // - Token ungültig machen (z.B. mi_neu_token = NULL) oder rotieren
  // - Timestamp setzen (falls mi_nau_tok_erz "Token erzeugt am" ist, dann besser eigenes Feld mi_confirmed_at)
  // Beispiel:
  // $pdo-&gt;prepare("UPDATE fv_mi_anmeld SET mi_neu_token = NULL WHERE mi_neu_nr = :nr")-&gt;execute([':nr' =&gt; $neu['mi_neu_nr']]);

  $pdo-&gt;commit();

  ok('E-Mail bestätigt. Mitglied, Benutzer und Benutzerdetails sind angelegt/verknüpft.');

} catch (Throwable $e) {
  if ($pdo-&gt;inTransaction()) $pdo-&gt;rollBack();

  // In Produktion: $e-&gt;getMessage() in Error-Log schreiben.
  fail('Unerwarteter Fehler beim Bestätigen. Bitte später erneut versuchen.', 500);
}
</code></pre>

          <div class="note">
            <div class="k"><span class="warn">Hinweise zur Robustheit:</span></div>
            <ul>
              <li>Empfohlen: <code>UNIQUE</code> auf <code>fv_mi_anmeld(mi_neu_token)</code> und <code>be(be_uid)</code>.</li>
              <li>Empfohlen: <code>UNIQUE</code> auf <code>fv_ben_dat(be_id)</code>, wenn wirklich pro Benutzer genau ein Detaildatensatz existieren soll.</li>
              <li>Wenn <code>mi_nau_tok_erz</code> „Token erzeugt am“ ist, nutze für Bestätigung besser ein eigenes Feld (z.B. <code>mi_confirmed_at</code>) statt es zu überschreiben.</li>
            </ul>
          </div>
        </div>
      </section>

      <aside class="panel" aria-label="ToDos und Anpassungen">
        <div class="hd"><div class="k">Was du ggf. noch anpassen musst</div></div>
        <div class="bd">
          <div class="k">1) Feldnamen der Token-Spalten</div>
          <ul>
            <li>Im Script genutzt: <code>mi_neu_nr</code>, <code>mi_neu_chkd</code>, <code>mi_neu_token</code>, <code>mi_nau_tok_erz</code></li>
            <li>Wenn bei euch abweichend: nur im SELECT/UPDATE anpassen.</li>
          </ul>

          <div class="k" style="margin-top:12px;">2) Datums-/Typfelder</div>
          <ul>
            <li><code>fv_ben_dat.fd_sterb_dat</code> / <code>fd_austr_dat</code> sind <code>varchar(12)</code>; ich schreibe <code>YYYY-MM-DD</code> als String hinein.</li>
            <li>Wenn ihr dort ein anderes Format erwartet (z.B. <code>DD.MM.YYYY</code>): sag kurz Bescheid, dann baue ich die Formatierung ein.</li>
          </ul>

          <div class="k" style="margin-top:12px;">3) Login/Passwort</div>
          <ul>
            <li>Deine Tabelle <code>be</code> hat kein Passwortfeld gezeigt. Falls es ein Passwort/Hash-Feld gibt, muss das beim Registrieren separat gesetzt werden (oder ein „Passwort setzen“-Link versendet werden).</li>
          </ul>

          <div class="k" style="margin-top:12px;">4) „Mitglied existiert schon“</div>
          <ul>
            <li>Wenn es möglich ist, dass eine E-Mail bereits in <code>fv_mitglieder</code> existiert, solltest du vor dem Insert prüfen, ob ein Mitglied mit dieser E-Mail schon existiert und dann statt Insert ein Update/Linking machen.</li>
          </ul>
        </div>
      </aside>
    </div>
  </div>
</body>
</html>