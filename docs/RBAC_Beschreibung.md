FWArchiv – Rollen- und Berechtigungskonzept (RBAC)

Stand: 28.10.2026 · Namespace FWHist\Auth · Klassen Auth, AccessGuard

1. Grundprinzipien
Rollen sind global, sie gehören einem Benutzer, nicht einem Mandanten. Der Mandantenbezug entsteht nur über die Zuordnungstabelle fv_benutzer_mandant (nur für ONU-ALL).
Lesen von Mandantendaten darf jeder gültig angemeldete Benutzer. Ausnahmen (Mitglieder, Mandanten-Stamm, Benutzer, Listen mit Rollenbindung) stehen in Abschnitt 4.
Schreiben von Mandantendaten = Fachrolle des Moduls (gilt für alle Mandanten) ODER ONU-ALL UND Mandant in fv_benutzer_mandant. ADM-ALLE darf immer alles.
Es gibt genau eine Stelle für die Regeln: AccessGuard. Die Module fragen nur (canWriteMandantData('INV', $ei_id)), sie kennen keine Rollennamen mehr. Ausnahme: die tabellengesteuerten Listen-Rollen in ListHostAPI.php (Abschnitt 6.3).
Serverseitig prüfen, nie der Oberfläche vertrauen. editProtect, ausgeblendete Buttons und Ähnliches sind Komfort. Maßgeblich ist die Prüfung im Script, das speichert, hochlädt oder Daten liefert.
Client-Werte sind nie Berechtigung. ei_id, eigner, storPath, urhNr kommen vom Browser und werden gegen den berechtigten Mandanten geprüft oder bereinigt.
Im Zweifel verweigern (Default-Deny): unbekannte Upload-Module → 403, Listen ohne Eintrag in der Öffentlich-Liste → Login nötig.
Auf externen Seiten wird nie geschrieben. Alle schreibenden Guard-Methoden (canWriteMandantData, canEditPsa, canEditOeffentlichkeit, canApproveReviews, canWriteMemberData, canUpload, canGrantRole …) liefern auf einer externen Seite (smod = 'index') false, auch für angemeldete Administratoren.
Rollen und Mandanten werden bei jedem Request frisch aus der DB geladen (3 kleine Abfragen). Ein Rollenentzug oder eine Deaktivierung wirkt daher sofort, auch bei laufender Session.
2. Rollenkatalog
Rolle	Bedeutung	Mandantenbezug
ADM-ALLE	Super-Admin: erfüllt jede Prüfung	–
ADM-ZUGR	Zugriffsverwaltung: Rollen und Mandanten-Zuordnung pflegen, darf alle Rollen vergeben außer ADM-ALLE (auch weitere ADM-ZUGR)	–
ADM-MA	Mandanten-Stammdatei ma_mandanten (keine Modul-Daten); Export/Löschen/Freigeben	–
ADM-MI	Mitgliederdaten mi_* lesen und schreiben; Export/Löschen/Freigeben	–
ADM-MB	Beitrags-Bezahleingang: zusätzlich mi_bezahlt und mi_m_*_bez*; Liste MIB	–
ADM-ARC	Archiv (Archivalien): schreiben für alle Mandanten	alle
ADM-INV	Inventar: schreiben für alle Mandanten	alle
ADM-MEDIA	Medien-Bibliothek (Audio, Foto, Video): schreiben für alle Mandanten; Listen MV, MD	alle
ADM-FZG	Fahrzeuge/Geräte: schreiben für alle Mandanten	alle
ADM-FREI	Freigeber der Öffentlichkeitsarbeit, derzeit Buch-Rezensionen (Rollen-Code in AccessGuard::REVIEW_ROLE, so in fv_rollen hinterlegt)	–
ADM-OEF	Öffentlichkeitsarbeit (Buch, Marktplatz, Museen, Presse, Termine, Dokumente)	–
ADM-PSA	Ärmelabzeichen/PSA (Listen ORT, OW, FW, AWA)	–
ADM-AB	Liste ABK	–
ADM-FI	Liste FIR	–
ADM-BER	(bitte Zuständigkeit ergänzen)	–
ONU-ALL	Normalbenutzer: lesen alles Erlaubte, schreiben nur für Mandanten aus fv_benutzer_mandant (mehrere möglich)	je Eintrag
ONU-GAST	Gast: nur lesen, schreibt nie	–

Hinweise:

Die frühere Sammelrolle (ADM-FZG,ADM-BER,ADM-FOTO,ADM-AB,ADM-FI) wird im neuen Schema nicht mehr gebraucht. Die Rolle ADM-FOTO heißt jetzt ADM-MEDIA (bessere Beschreibung: Audio, Foto, Video). In fv_rollen_beschr.fl_modules muss der alte Eintrag umgestellt werden (Abschnitt 8.1).
Technisch sind mehrere Rollen je Benutzer möglich (fv_rolle hat je Rolle eine Zeile, fr_aktiv = 'a'). Gebraucht wird es im neuen Schema nicht mehr.
3. Datenmodell
Tabelle	Zweck	Wichtige Spalten
fv_benutzer	Benutzer	be_id, be_uid, be_act (a aktiv, i inaktiv, '' zählt als aktiv; nur i sperrt)
fv_erlauben	Passwort-Hash (bcrypt)	be_id, fe_pw
fv_rolle	Rolle je Benutzer	be_id, fl_id, fr_aktiv (a zählt, i nicht)
fv_rollen_beschr	Rollen-Definition	fl_id, fl_modules (kommaseparierte Rollen-Codes, z. B. ONU-ALL, ADM-INV)
fv_benutzer_mandant	Mandanten-Schreibrecht je Benutzer (nur für ONU-ALL), gepflegt über MandErlEdit.php	fm_id, be_id, ei_id, fm_aktiv (a/i), fm_created_*, fm_changed_* (Spalten aus MandErlEdit/ROMList abgeleitet, bitte gegen die Tabelle prüfen)

Ablauf beim Laden (Auth::loadRoles()): fv_rolle (nur fr_aktiv = 'a') → fv_rollen_beschr.fl_modules → am Komma aufgeteilt → Duplikate und leere Einträge entfernt → Liste der Rollen-Codes. Ablauf Auth::loadMandanten() (bei einem Datenbankfehler, z. B. fehlende Tabelle: keine Mandanten, Eintrag im Error-Log; nie mehr Rechte): fv_benutzer_mandant (nur fm_aktiv = 'a') → Liste der ei_id.

Wichtig: Die Prüfung arbeitet mit den Codes in fl_modules, nicht mit dem Namen der Rolle. Ein Tippfehler dort (ADM-INV  statt ADM-INV) macht die Berechtigung wirkungslos.

4. Wer darf was (Matrix)
Bereich	Lesen	Schreiben
Archiv, Inventar, Medien, Fahrzeuge/Geräte (mandantenbezogen)	jeder angemeldete Benutzer	Fachrolle (ADM-ARC / ADM-INV / ADM-MEDIA / ADM-FZG, alle Mandanten) oder ONU-ALL + Mandant in fv_benutzer_mandant · ADM-ALLE immer · ONU-GAST nie
Mandanten-Stamm ma_mandanten	Liste MA: ADM-MA	ADM-MA, ADM-ALLE
Mitglieder mi_*	ADM-MI, ADM-ALLE (Listen MI, MIE, UN)	ADM-MI, ADM-ALLE; Zahlungsfelder zusätzlich ADM-MB
Beitrags-Bezahleingang (Liste MIB)	ADM-MB	ADM-MB
Export (DB-Sicherung), Löschen, Freigeben	–	ADM-MA, ADM-MI, ADM-ALLE
Freigabe Buch-Rezensionen	–	ADM-FREI, ADM-ALLE
Benutzer-/Rollenverwaltung	ADM-ZUGR, ADM-ALLE	ADM-ZUGR, ADM-ALLE
Öffentlichkeitsarbeit	öffentlich (Listen), Edit-Formulare schreibgeschützt	ADM-OEF
Ärmelabzeichen/PSA	öffentlich (Listen ORT, OW, FW, AWA)	ADM-PSA
Listen ABK, FIR, MV, MD, EMA	ADM-AB, ADM-FI, ADM-MEDIA, ADM-MEDIA, ADM-ALLE	je Modul

Extern (öffentliche Seiten, Erkennung $_SESSION['BS_Prim']['Mod']['smod'] === 'index'): nur lesen, nur Listen der Öffentlich-Liste (Abschnitt 6.3). Uploads sind extern immer verboten.

5. Die zwei Klassen
5.1 Auth (Datei Auth.php) – wer ist der Benutzer, welche Rollen hat er
Methode	Zweck
login($uid, $pw)	prüft Benutzer (be_act <> 'i') und Passwort; lädt Rollen und Mandanten
restore($beId)	stellt den Benutzer in Folgerequests aus der Session-be_id wieder her
logout()	Zustand leeren (Session löscht der Controller)
isLoggedIn() / getUser()	Anmeldestatus / Benutzer-Datensatz
hasPermission($code)	Rolle vorhanden oder ADM-ALLE (Super-Admin-Abkürzung)
hasRole($code)	Rolle exakt, ohne Super-Admin-Abkürzung (z. B. für ONU-ALL)
hasMandant($eiId)	steht der Mandant in fv_benutzer_mandant?
getRoles() / getMandanten()	Listen (leer, wenn nicht angemeldet)

Passwort-Funktionen (changePassword, Reset-Token) sind von der RBAC-Umstellung unberührt.

5.2 AccessGuard (Datei AccessGuard.php) – die Regeln

Erzeugen in jedem Script, das etwas prüft, mit einer Zeile:

php
use FWHist\Auth\AccessGuard;
use FWHist\Database\DB_GenericLog;

$DBD   = new DB_GenericLog();
$guard = AccessGuard::fromSession($DBD);   // liest be_id aus $_SESSION['BS_Prim']['BE']['be_id']

Ist niemand (mehr) gültig angemeldet – auch bei deaktiviertem Benutzer mit alter Session –, liefern alle Prüfungen false.

Methode	Regel
canReadMandantData()	angemeldet
canWriteMandantData($modul, $eiId)	Fachrolle von $modul (ARC, INV, MEDIA, FZG) oder (ONU-ALL und $eiId in fv_benutzer_mandant); ADM-ALLE immer. Unbekanntes Modul → InvalidArgumentException
canEditMandantStamm()	ADM-MA
canReadMemberData() / canWriteMemberData()	ADM-MI
canWriteMemberField($feld)	ADM-MI; außerdem ADM-MB für mi_bezahlt und mi_m_*_bez*
canAdminAction()	ADM-MA oder ADM-MI (Export, Löschen, Freigeben)
canApproveReviews()	ADM-FREI
canManageAccess()	ADM-ZUGR
canGrantRole($rolle)	ADM-ALLE: alle; ADM-ZUGR: alle außer ADM-ALLE
canEditOeffentlichkeit()	ADM-OEF
canEditPsa()	ADM-PSA
canUpload($subMod, $eiId)	Upload-Recht nach Modul, siehe 6.4
listActionConfig($canEdit, $hasDetailForm, $detailPublic, $isPublicView)	steuert die Action-Spalte: liefert showActionColumn, actionLabel („Anzeigen“/„Bearbeiten“), editProtect; $hasDetailForm = false = Spalte nur für Bearbeiter (Abschnitt 7)
canViewEditForm($modul, $isPublicView)	Edit-Formular (schreibgeschützt) ansehen: intern jeder Angemeldete, extern nur Module aus PUBLIC_VIEW_FORMS
canSeeUnreleasedReviews()	Buch-Rezensionen: nicht freigegebene sehen ADM-OEF/ADM-FREI, nie auf externen Seiten
canEditReview($freigegeben)	ADM-FREI immer; ADM-OEF nur solange nicht freigegeben (Annahme, eine Zeile)
applyReviewRelease($neu, $alt, $beId)	setzt bu_frei_stat/_id/_dat beim Speichern; Neuanlage unfrei, nur ADM-FREI kann freigeben/zurücknehmen (Abschnitt 6.6)
userId()	be_id des angemeldeten Benutzers (0 = niemand)
AccessGuard::deny($text, $status = 403)	beendet das Script mit Status und Meldung (vor jeder Ausgabe aufrufen)
AccessGuard::csrfField() / requireCsrf()	CSRF-Schutz: Hidden-Feld im Formular, beim Speichern (phase == 1) nach der Rechteprüfung requireCsrf() (Abschnitt 8.9); darunter csrfToken() und checkCsrf($token)
AccessGuard::current()	Guard des laufenden Requests (für Listen-Klassen, die keinen $guard bekommen)
AccessGuard::isPublicView()	externe Seite? (smod === 'index')
isLoggedIn(), hasPermission($code)	Durchreichen an Auth (für tabellengesteuerte Prüfungen)

ADM-ALLE ist bereits in Auth::hasPermission() enthalten und braucht in den Guard-Methoden keine Sonderbehandlung.

6. Wo wird was definiert
Was	Wo	Datei
Rollen-Codes und welcher Benutzer welche hat	DB: fv_rollen_beschr.fl_modules, fv_rolle	Datenbank
Mandanten-Schreibrecht für ONU-ALL	DB: fv_benutzer_mandant	Datenbank
Regeln (wer darf was)	AccessGuard	AccessGuard.php
Zuordnung Modul → Fachrolle (mandantenbezogen)	Konstante FACH_ROLLEN	AccessGuard.php
Öffentliche Listen, rollengeschützte Listen, editProtect je Liste	$publicLists, $protectedListRoles, Zweige je list_ID	ListHostAPI.php
Upload-Recht je Modul, Mandantenbindung des Zielordners	canUpload() und Block am Anfang	AccessGuard.php, UploadAPI.php
Anzeige schreibgeschützt/Speichern sperren	$editProtect, 403 bei phase == 1	Edit-Script und *_ph0_inc.php
Sichtbare Felder je Zielgruppe	unset($row['feld']) in der Listen-Klasse	FWHist\Services\… (Listen)
6.1 Liste (Seite, z. B. InventarList.php)
php
$DBD   = new DB_GenericLog();
$guard = AccessGuard::fromSession($DBD);
if (!$guard->canReadMandantData()) {
    http_response_code(403);
    exit('Anmeldung erforderlich.');
}

// ... später, wenn der Mandant bekannt ist (in renderList($ei_id)):
$canEdit = $guard->canWriteMandantData('INV', (int) $ei_id);   // Schreibrecht JE Mandant
$act     = $guard->listActionConfig($canEdit);                  // Action-Spalte, getrennt vom Schreibrecht

$lists = [[ /* ... */
    'showActionColumn' => $act['showActionColumn'],   // sichtbar für alle, die das Detail öffnen dürfen
    'actionLabel'      => $act['actionLabel'],        // "Anzeigen" oder "Bearbeiten"
    'editScript'       => $canEdit ? $NeuRec : '',    // "Neu anlegen" nur mit Schreibrecht
]];
$globalParams = [ /* ... */ 'edit_Protect' => $act['editProtect'] ];

Die Action-Spalte öffnet das Detail-/Edit-Formular, in dem der Volltext steht (Listenspalten sind dafür oft zu kurz). Sie ist daher nicht an das Schreibrecht gebunden. $canEdit steuert nur „Neu anlegen“ und das Label. Der Mandant ist erst nach der Auswahl bekannt, deshalb wird $canEdit in renderList() berechnet und nicht am Seitenanfang.

6.2 Edit-Script (Seite + Speichern, z. B. InvEdit.php)
php
$DBD   = new DB_GenericLog();
$pdo   = $DBD->getPDO();
$guard = AccessGuard::fromSession($DBD);

if (!$guard->canReadMandantData()) {                      // 1. ansehen: angemeldet genügt
    http_response_code(403);
    exit('Anmeldung erforderlich.');
}
if ((int) ($_POST['phase'] ?? 0) === 1                    // 2. speichern: Schreibrecht für DIESEN Mandanten
    && ($ei_id <= 0 || !$guard->canWriteMandantData('INV', $ei_id))) {
    http_response_code(403);
    exit('Für Änderungen am Inventar dieses Mandanten fehlt die Berechtigung.');
}

AccessGuard::requireCsrf();   // nach der Rechteprüfung, nur bei phase 1 (Abschnitt 8.9)

// beim Speichern: nie dem POST-Wert vertrauen, geschrieben wird in die Tabelle von $ei_id
$neu['ei_id'] = $ei_id;

Im Formular-Include (*_ph0_inc.php):

php
$editProtect = false;
if (!$guard->canWriteMandantData('INV', (int) $neu['ei_id'])) {
    $editProtect = true;                                   // Formular schreibgeschützt anzeigen
}
if (!$editProtect) { echo AccessGuard::csrfField(); }        // CSRF-Token (Abschnitt 8.9)
// ... $forms = new FormRendererFlex($meta, $phase, $neu, [], $editProtect, $module);
// Speichern-Button nur: if (!$editProtect) { ... }

Reihenfolge ist wichtig: zuerst $ei_id aus Request bestimmen (GET vor POST, wie bisher), danach prüfen, danach schreiben. Die Prüfung muss gegen denselben $ei_id laufen, der den Tabellennamen ({ei_id}_inventar) bestimmt.

6.3 ListHostAPI.php (liefert die Listendaten)

Der Endpunkt liest nur. Es geht daher um Lesezugriff je Liste; editProtect bestimmt nur die Anzeige (z. B. Aktionsspalte).

php
$publicLists = ['ORT', 'OW', 'FW', 'AWA', 'AL', 'BU', 'DO', 'MP', 'MU', 'PR', 'TE', 'ZT', 'ZI'];
// Alle anderen Listen: Login nötig (403 sonst).

$protectedListRoles = [ 'ABK' => 'ADM-AB', 'FIR' => 'ADM-FI', 'EMA' => 'ADM-ALLE',
                        'MA' => 'ADM-MA', 'MI' => 'ADM-MI', 'MIE' => 'ADM-MI', 'UN' => 'ADM-MI',
                        'MIB' => 'ADM-MB', 'MV' => 'ADM-MEDIA', 'MD' => 'ADM-MEDIA',
                        'BE' => 'ADM-ZUGR', 'RO' => 'ADM-ZUGR', 'ROM' => 'ADM-ZUGR' ];   // Benutzer, Rollen, Mandanten-Erlaubnisse
// Diese Listen: Rolle nötig (403 sonst), Zugriff über $guard->hasPermission(...)

editProtect je Zweig:

list_ID	Regel
ORT, OW, FW, AWA (PSA)	$isPublicView || !$guard->canEditPsa()
AL, BU, DO, MP, MU, PR, TE, ZT, ZI (ÖA)	$isPublicView || !$guard->canEditOeffentlichkeit()
IN (Inventar)	!$guard->canWriteMandantData('INV', sel_id) – sel_id = Mandant
6.4 UploadAPI.php

Block am Anfang, vor jeder Verarbeitung:

php
$guard = AccessGuard::fromSession(new DB_GenericLog());
if ($isPublicView || !$guard->canUpload($requestedSubMod, $gateEiId)) { /* 403 + Log */ }

canUpload() nimmt den Teil von subMod vor dem | und vergleicht den Anfang:

subMod beginnt mit	Recht
OEF	canEditOeffentlichkeit()
PSA	canEditPsa()
ARC / ADM-ARC	canWriteMandantData('ARC', eigner)
INV	canWriteMandantData('INV', eigner)
FZG	canWriteMandantData('FZG', eigner)
MEDIA / ADM-MED	canWriteMandantData('MEDIA', eigner)
MVW	canWriteMemberData()
alles andere	nur ADM-ALLE, sonst verweigert (Log: UploadAPI: Upload verweigert, subMod=… eigner=… be_id=…)

Zusätzlich bei mandantenbezogenen Modulen (ARC, INV, FZG): Dokumente (keine Bilder) dürfen nur in den Ordner AOrd_Verz/{eigner}/…; stimmt das erste Segment von storPath nicht mit eigner überein, wird die Datei abgelehnt. Fotos gehen in die Bibliothek des Urhebers (urhNr/09/06/Datum) und werden nur gegen das Modulrecht für eigner geprüft. urhNr und eigner werden auf [A-Za-z0-9_-] bereinigt.

6.5 Listen-Klasse (FWHist\Modules\…\Lists\*List.php)

Die Listen-Klasse liefert über ListHostAPI.php Spalten und Daten. Zwei Pflichtregeln:

php
private const SHOW_COLS = ["in_id", "in_sammlg", "in_bezeichnung", "in_hersteller", "in_foto_1"];

// getColumns(): $showCols = self::SHOW_COLS;
// getData(), je Zeile ($rows per Referenz):
$actionLabel = $editProtect ? 'Anzeigen' : 'Bearbeiten';        // 1. Action-Link IMMER, Label nach Recht
$row['action'] = "<a class='btn action-link' href='InvEdit.php?in_id={$in_id}&ei_id={$selId}'>{$actionLabel}</a>";
// ... Felder aufbereiten (HTML, Bilder, zusammengesetzte Inhalte) ...
$row = array_intersect_key($row, array_flip([...self::SHOW_COLS, 'action']));   // 2. nur Listenfelder ausliefern
Action-Link immer setzen. list-tables.js blendet die Spalte nur über list.showActionColumn ein/aus und zeigt den Link aus row.action. Fehlt row.action, baut das JS einen Ersatzlink aus id/fw_id (bei Inventar nicht vorhanden → kaputter Link). editProtect bestimmt nur das Label. Ob die Spalte sichtbar ist (extern, Liste ohne Detail), entscheidet die Seite mit listActionConfig(); das Detail-Formular selbst ist unabhängig davon über canViewEditForm() geschützt.
Nur Listenfelder ausliefern. SELECT * liest alles, das JSON an den Browser enthält sonst alle Spalten aller Zeilen, auch wenn die Liste nur fünf anzeigt (bei Inventar z. B. Neu-, Kauf- und Beschaffungswert, in_changed_id). Die Allowlist (array_intersect_key) ist sicherer als einzelne unset(); wer Felder in andere einbaut, macht das vor dieser Zeile. Wo je Zielgruppe unterschiedliche Felder gelten, die Allowlist nach $editProtect oder Rolle verzweigen.
6.6 Buch-Rezensionen (oe_buecher): Freigabe
Feld	Bedeutung
bu_frei_stat	SET('U','F'), NOT NULL: U = unfrei, F = frei (freigegeben)
bu_frei_id	be_id des Freigebers, int, NULL erlaubt (setzt der Server)
bu_frei_dat	Freigabe-Datum, date, NULL erlaubt (setzt der Server, Y-m-d)

Regeln:

Sehen: freigegebene Rezensionen sieht jeder (auch extern, ONU-ALL, ONU-GAST). Nicht freigegebene sehen nur Bearbeiter (ADM-OEF) und Freigeber (ADM-FREI), und nie auf externen Seiten (canSeeUnreleasedReviews()).
Bearbeiten: ADM-FREI immer, ADM-OEF nur solange nicht freigegeben (canEditReview($released)). Alle anderen sehen das Formular schreibgeschützt.
Freigeben/Zurücknehmen: nur ADM-FREI (und ADM-ALLE). Die Freigabe-Felder aus dem Formular werden nie übernommen; applyReviewRelease() setzt Status, Freigeber-ID und Datum serverseitig. Bei Rücknahme wird der Status auf U gesetzt und Freigeber-ID/Datum werden zurückgesetzt (0 / '0000-00-00', wie im bisherigen Formular). Neue Rezensionen starten immer als U, ein Freigeber darf sie schon beim Anlegen freigeben.
Liste (BUList): liefert nur Spaltenfelder plus action (und bu_frei_stat für Bearbeiter/Freigeber). Der Volltext steht im Formular.

Im Edit-Script (BuchEdit.php, fertig umgestellt):

php
$guard = AccessGuard::fromSession($DBD);
$isPublicView = AccessGuard::isPublicView();
if (!$guard->canViewEditForm('BU', $isPublicView)) { AccessGuard::deny('Keine Berechtigung.'); }

$released = ($record['bu_frei_stat'] ?? '') === 'F';
$canEdit  = $guard->canEditReview($released);                 // Formular schreibgeschützt, wenn false
if ($bu_id !== 0 && !$released && !$guard->canSeeUnreleasedReviews()) { AccessGuard::deny('Eintrag nicht gefunden.', 404); }

// Speichern (phase 1): $canEdit und CSRF prüfen, dann
if (isset($_POST['bu_frei_stat']) && is_string($_POST['bu_frei_stat'])) { $neu['bu_frei_stat'] = $_POST['bu_frei_stat']; }  // nur Wunsch
$neu = $guard->applyReviewRelease($neu, $record, $guard->userId());

Im Formular (BuchEdit_ph0_inc.php): ohne Schreibrecht kein Upload-Block und kein FotoUplLibs.js, stattdessen werden die vorhandenen Bilder als Links/Vorschau angezeigt; das Freigabe-Feld erscheint nur für canApproveReviews().

6.7 PSA: Ort-Formular und Sub-Listen

Das Ort-Formular (OrtsEdit.php) enthält die Sub-Listen Wappen (OW), Wappen im Feuerwehrgebrauch (FW) und Ärmelabzeichen (AWA). Die Ort-Liste (ORT) hat deshalb ein Detail für Leser; die Sub-Listen nicht.

Liste	Action-Spalte	Link in der Listen-Klasse
ORT (Seite OrtsAuswList.php)	alle, die die Liste sehen (extern, weil ORT in PUBLIC_VIEW_FORMS steht)	immer; „Anzeigen“/„Bearbeiten“ nach editProtect
OW, FW, AWA (Sub-Listen im Formular)	nur Bearbeiter (ADM-PSA)	nur ohne editProtect

Seitenkonfiguration (Include-Datei der Liste):

php
// Ort-Liste
$act = $guard->listActionConfig($guard->canEditPsa(), hasDetailForm: true, detailPublic: true,
                                isPublicView: AccessGuard::isPublicView());
// Sub-Listen im Ort-Formular
$act = $guard->listActionConfig($guard->canEditPsa(), hasDetailForm: false,
                                isPublicView: AccessGuard::isPublicView());
// danach: 'showActionColumn' => $act['showActionColumn'], 'actionLabel' => $act['actionLabel'],
//         globalParams: 'edit_Protect' => $act['editProtect']

Das Ort-Formular (OrtsEdit.php) bindet nur diese drei Sub-Listen ein (OrtsWappenList_inc.php, FwWappenList_inc.php, AermAbzList_inc.php); deren Include-Dateien setzen showActionColumn mit listActionConfig(…, hasDetailForm: false). Die Sub-Edits OWAEdit.php (Ortswappen, psw_ort_wappen), FWAEdit.php (Wappen im Feuerwehrgebrauch, psw_ff_wappen) und AermAbzEdit.php (Ärmelabzeichen) folgen demselben Muster: Ansehen intern für jeden Angemeldeten schreibgeschützt (canViewEditForm('OW'|'FW'|'AWA'), extern gesperrt), Speichern nur mit ADM-PSA und CSRF-Token. Gespeichert werden nur Sortierung und Beschreibung; Ort und Bilddatei eines bestehenden Datensatzes kommen immer aus der Datenbank, bei Neuanlage muss der Ort (fo_fw_id, aus der Sub-Liste mitgegeben) in pso_ort_ref existieren. Die Include-Listen geben fo_fw_id an den Link „Neu anlegen“ weiter.

OrtsEdit.php ist extern schreibgeschützt sichtbar (ORT in der Whitelist), AermAbzEdit.php extern gesperrt (AWA nicht in der Whitelist) und intern für Leser schreibgeschützt. Die Include-Dateien der Sub-Listen (OrtsWappenList_inc.php, FwWappenList_inc.php, AermAbzList_inc.php) sind bereits passend: showActionColumn hängt an $canEdit (aus OrtsEdit.php, im externen und im Lese-Modus false), ohne Leser-Detail erscheint also keine Spalte. AermDetail.php und EhrgDetail.php (Spalte „Details“ der Ort-Liste) werden nicht mehr gebraucht, sobald der Action-Link „Anzeigen“ mit dem schreibgeschützten OrtsEdit.php getestet ist: in OrtsList.php SHOW_DETAIL_LINKS = false setzen (Spalte und Links entfallen), danach beide Scripts löschen. Für das Projekt EHRZ erst abschalten, wenn OrtsEdit.php die Ehrenzeichen-Anzeige enthält (der Zweig EHRG dort ist noch leer und prüft einen anderen Projekt-Code).

6.8 Benutzer-, Rollen- und Mandanten-Verwaltung (Ben*, Rollen*, MandErl*)

Diese Scripts vergeben Rechte und sind deshalb die empfindlichste Stelle.

Script	Zweck	Prüfung
BenList.php	Benutzerliste (Liste BE)	canManageAccess()
BenEdit.php (+_ph0/_ph1_inc)	Benutzerdaten (fv_ben_dat), enthält die Listen RO und ROM	canManageAccess(), CSRF, Feld-Allowlist
RollenEdit.php (+Includes), RollenList_inc.php, Liste RO	Rollen einem Benutzer zuordnen (fv_rolle)	canManageAccess(), CSRF, canGrantRoleModules()
MandErlEdit.php (+Includes), MandErlList_inc.php, Liste ROM	Mandanten-Erlaubnis für ONU-ALL (fv_benutzer_mandant)	canManageAccess(), CSRF

Regeln:

Zugriff: nur ADM-ZUGR und ADM-ALLE, auch zum Ansehen. Die API-Listen BE, RO und ROM stehen in $protectedListRoles mit ADM-ZUGR; ohne diesen Eintrag könnte jeder Angemeldete die Benutzerliste über ListHostAPI.php laden.
Rollen vergeben, ändern, entziehen: nur Rollen, deren alle Codes in fl_modules der Benutzer vergeben darf (AccessGuard::canGrantRoleModules(); ADM-ZUGR: alle außer ADM-ALLE). Das gilt für die Auswahl im Formular und beim Speichern (die gewählte fl_id wird gegen diese Liste geprüft) sowie für das Ändern bestehender Zuordnungen: die Rolle einer bestehenden Zuordnung muss ebenfalls vergeben werden dürfen. Ein ADM-ZUGR kann also weder ADM-ALLE vergeben noch eine vorhandene ADM-ALLE-Zuordnung ändern.
Zuordnung gehört zum Benutzer: fr_id/fm_id werden zusammen mit be_id aus der Datenbank gelesen; eine fremde Zuordnung unter falscher be_id ergibt 404.
Nur benannte Felder: Rolle (fl_id), Status (a/i) bzw. Mandant (ei_id, muss in ma_mandanten existieren, keine Doppelzuordnung) kommen aus dem Formular. Benutzer, Zeitstempel und Bearbeiter setzt der Server. BenEdit.php übernimmt nur eine Allowlist von Profilfeldern.
Profil-Nr statt be_id: BenEdit.php ist über die Profil-Nr fd_id (fv_ben_dat) adressiert. Rücksprünge aus Rollen/Mandanten ermitteln sie über DB_Benutzer::getProfileIdByBeId(); vorher stand dort die be_id, was nur bei gleichen Nummern stimmte.
Neuanlage: BenEdit.php legt nur das Profil (fv_ben_dat) an. Login-Benutzer (fv_benutzer) und Passwort (fv_erlauben) entstehen dort nicht.
6.9 Mandanten-Stammdaten (ma_mandanten) und Autocomplete
Script	Zweck	Prüfung
MandantenList.php, Liste MA (MandList.php)	Liste der Mandanten	canEditMandantStamm() (ADM-MA, ADM-ALLE), auch in ListHostAPI ($protectedListRoles)
MandEdit.php (+_ph0_inc)	Mandant anlegen/ändern	canEditMandantStamm(), CSRF, Feld-Allowlist
MandantenAutoComp_API.php, UrheberAutoComp_API.php	Autocomplete für Mandant/Urheber (JSON)	angemeldet und nicht extern (canReadMandantData())
Zugriff: nur ADM-MA und ADM-ALLE, auch zum Ansehen (Namen, Adressen, Telefon, E-Mail, Einverständniserklärungen). Die Liste liefert deshalb nur die Spaltenfelder (SHOW_COLS, plus ei_org_name für den Textfilter) und den Action-Link; Telefon, E-Mail und Einverständnisse verlassen den Server nicht.
Speichern: nur Formularfelder, nur Spalten, die es in der Tabelle gibt; ei_id, Bearbeiter und Zeitstempel setzt der Server. Staat und Bundesland kommen aus den Hidden-Feldern der Autocomplete-Auswahl (staat_id, bdld_id).
Autocomplete-Endpunkte: vorher ohne jede Prüfung abrufbar (Namen aller Mandanten und Privatpersonen). Jetzt Login nötig und extern gesperrt. Sie liefern nur Auswahltexte für Edit-Formulare, deshalb genügt jeder angemeldete Benutzer. Die Log-Pfade AOrd_verz hießen unter Linux anders als der Ordner AOrd_Verz und hätten einen zweiten Ordner angelegt.
DB_Mandanten.php: setzt beim Laden keine ini-Werte und keine Shutdown-Funktion mehr. Das hatte error_log aller Scripts auf einen ungültigen Pfad umgebogen (fehlender /), sobald die Klasse geladen wurde.
Liste MA: braucht keine sel_id mehr (Eintrag in der Ausnahmeliste von ListHostAPI.php); der Platzhalter 9 entfällt.
6.10 Autocomplete-Bausteine: FormRendererFlex_Ext

Die Funktionen AutoCompForm_*() aus CommFuncsLib.php sind Teil der App und stehen jetzt in FormRendererFlex_Ext.php (neben FormRendererFlex.php, Namespace FWHist\Services). Da FormRendererFlex final ist, wird die Klasse umhüllt statt abgeleitet: ein FormRendererFlex_Ext-Objekt hat dieselben Konstruktorparameter und ersetzt FormRendererFlex direkt (alle render*-Aufrufe werden weitergereicht).

php
$forms = new FormRendererFlex_Ext($meta, $phase, $neu, [], $editProtect, $module);
echo $forms->renderTextLikeFieldFlex('ei_name', 60);     // wie bisher
echo $forms->renderAutoCompStaat();                      // Baustein; bei editProtect oder phase != 0 leer
echo FormRendererFlex_Ext::autoCompMandant('selection', 'mand', 'mand', '', 'mand_id');   // ohne Formular (Listenseite)
Instanz: renderAutoCompXxx() (leer bei Schreibschutz, das if (!$editProtect) entfällt). Statisch: autoCompXxx() (immer HTML). Bausteine: ArchOrd, Abk, Benutzer, Bdld, Eigent, Firma, Aufbau, Mandant, Mitgl, Ort, Sammlg, Staat, Urheber.
CommFuncsLib.php: die AutoCompForm_*()-Funktionen bleiben als Wrapper (echo auf die statischen Methoden) erhalten, damit noch nicht umgestellte Scripts weiterlaufen. Sie können entfallen, sobald alle Aufrufer umgestellt sind.
Werte werden escaped (vorher z. B. in AutoCompForm_Mitgl, _Bdld, _Staat nicht); in AutoCompForm_Eigent war das id-Attribut von suggestEigener_<n> nicht geschlossen.
Die JS-Dateien (AutoComp_Staat.js usw.) werden weiterhin vom Formular eingebunden.
Wird FormRendererFlex einmal nicht mehr final deklariert, kann die Klasse auf extends FormRendererFlex umgestellt werden (Konstruktor und __call entfallen), die Aufrufer bleiben unverändert.
6.11 Menüs (MainMenu, ZentralVerw, MitglVerw und ihre Templates)

Menüpunkte ohne Berechtigung werden nicht angezeigt. Die Regeln stehen zentral in AccessGuard::menuFlags(); die Controller übergeben sie als $can an das Template, die Templates rufen kein userHasRole() mehr auf.

Flag	Rolle(n)	Menüpunkt
referat1	ADM-ALLE, ADM-MA, ADM-MI, ADM-MB, ADM-ZUGR, ADM-FI, ADM-AB	Hauptmenü: „Referat 1 – Organisation“ (Link auf ZentralVerw.php)
mitglieder	ADM-MI, ADM-MB	Zentrale Verwaltung: Mitgliederverwaltung (Link auf MitglVerw.php)
mitgl_verw	ADM-MI	Mitgliederverwaltung: Mitglieder, Aktivierung, Ehrungen, Unterstützer
mitgl_zahlung	ADM-MB	Mitgliederverwaltung: Zahlungseingang
mandanten	ADM-MA	Eigentümer / Mandanten
zugriff	ADM-ZUGR	Benutzer- und Zugriffsverwaltung (BenList.php verlangt ADM-ZUGR; im Menü stand vorher ADM-MI)
firmen	ADM-FI	Firmen (Hersteller/Aufbauer)
abkuerzungen	ADM-AB	Abkürzungen
admin	nur ADM-ALLE	Protokolle, E-Mail-Empfänger, Konfiguration, Prozesse (VF_Z_*), Import, Datenbank-Sicherung
Zugriff auf die Menüseiten: ZentralVerw.php nur mit canSeeAdminMenu(), MitglVerw.php nur mit canSeeMemberMenu() (ADM-MI/ADM-MB), sonst 403 mit Link zum Hauptmenü. Ohne gültigen Benutzer Weiterleitung zur Startseite.
Unverändert für alle (auch ONU-ALL, ONU-GAST): die Menüs der Module (Suchen, Fahrzeuge/Geräte, Öffentlichkeitsarbeit, PSA, Archiv, Medien, Inventar, Dokumentationen), E-Mail an Mitglieder, DSVGO-Auskunft, Logoff. Alle Links auf VF_*.php bleiben bestehen.
Ausblenden ist keine Absicherung. Die Zielseiten müssen die Rechte selbst prüfen. Bei FirmenList.php, AbkuerzList.php, AdmEmailList.php, ConfEdit.php und den VF_Z_*-Scripten ist das noch nicht umgestellt bzw. unbekannt.
Die Controller hatten die Prüfung new Auth(...) + isLoggedIn() ohne restore() (kennt nie einen Benutzer, dazu umgekehrt) und echo "<Doctype html>" (ungültig, jetzt entfernt). ZentralVerw.php lud AppLogger nicht.
6.12 Öffentliche Formulare ohne Login (Email2Vorst, MitglAnmeld)

Beide Seiten sind von außen schreibend erreichbar. Hier schützt nicht die Rollenprüfung, sondern ein Satz von Maßnahmen, den PublicFormGuard zentral bereitstellt (Namespace FWHist\Services, wiederverwendbar für weitere öffentliche Formulare).

Schicht	Umsetzung
Honigtopf	unsichtbares Feld website; Bots füllen es, jede Eingabe darin = abgelehnt
Mindest-/Höchstdauer	Formular frühestens nach 5 Sekunden, höchstens 2 Stunden nach dem Anzeigen gültig
CSRF	Token der Session (AccessGuard::csrfField()/checkCsrf())
Nummern-Captcha	5 große Ziffern in Sieben-Segment-Schrift, schwarz auf weiß, als SVG-Linien (kein Text im HTML); „Neuen Code anzeigen“, „Größere Ziffern“, Hinweis auf eine Ausweich-Adresse; Code nur in der Session, 15 Minuten gültig, 5 Versuche; ein richtig gelöster Code gilt 10 Minuten weiter (nach Fehlern in anderen Feldern nicht erneut eingeben), nach erfolgreichem Absenden verbraucht
Begrenzung	RateLimiter (Dateien, ohne Datenbank): Fehlversuche 20/Stunde je IP; Email2Vorst: 3/Stunde und 10/Tag je IP, 3/Tag je Absender-Adresse; MitglAnmeld: 5/Stunde und 15/Tag je IP, 2/Tag je E-Mail-Adresse
Eingaben	nur benannte Felder, Längen, Zeilenumbrüche aus einzeiligen Feldern entfernt (kein Header-Einschleusen), HTML in E-Mails escaped, höchstens zwei Internet-Adressen im Nachrichtentext

Email2Vorst.php (Nachfolger von VF_EM_Edit.php): Empfänger sind die Mitglieder mit mi_mitglieder.mi_vorst_funct <> '' und gültiger mi_email, ohne verstorbene/ausgetretene (mi_sterbdat/mi_austrdat gesetzt). Im Formular erscheinen nur die Funktionen („Allgemein“ = alle); die Adressen bleiben auf dem Server (VorstandRepository). Gibt es keine Adresse, geht die Nachricht an die allgemeine Vereinsadresse ($_SESSION['BS_Prim']['vema']). Der Absender erhält keine Kopie (sonst Missbrauch als Mail-Weiterleiter).

MitglAnmeld.php: Übernommen werden nur die Formularfelder; Status (mi_neu_chkd), Token, Zahlungsfelder, Eintritts- und Einverständnisdaten setzt der Server. Die Bestätigungs-E-Mail mit dem Link geht an den Anmelder, die Hinweis-E-Mail ohne Link an die Verantwortlichen.

Die Seiten setzen smod = 'Ext' und sind daher keine isPublicView()-Seiten (die erkennt nur index); sie benutzen keine Rollenprüfung.

7. Schreibgeschützte Anzeige (ONU-ALL, ONU-GAST, extern)
Action-Spalte ≠ Schreibrecht. In der Liste sind lange Inhalte (Buchbesprechung, Beschreibungen) abgeschnitten, der Volltext steht im Detail-/Edit-Formular. Deshalb sehen alle Benutzer, die die Liste sehen, die Action-Spalte (Label „Anzeigen“); „Bearbeiten“ und „Neu anlegen“ gibt es nur mit Schreibrecht. Steuerung je Liste über listActionConfig():
Fall	showActionColumn	actionLabel	editProtect
angemeldet, mit Schreibrecht	ja	Bearbeiten	nein
angemeldet (ONU-ALL ohne Mandanten-Recht, ONU-GAST)	ja	Anzeigen	ja
extern, Liste mit freigegebenem Detail ($detailPublic = true)	ja	Anzeigen	ja
extern, sonst	nein	–	ja
Sub-Liste ohne Leser-Detail ($hasDetailForm = false, z. B. Wappen/Ärmelabzeichen)	nur mit Schreibrecht	Bearbeiten	nein
Edit-Formulare werden für alle ohne Schreibrecht schreibgeschützt angezeigt (nicht blockiert). Das gilt für ONU-ALL ohne Mandanten-Erlaubnis, ONU-GAST und – wo gewollt – für externe Besucher (Buchbesprechungen mit zu langen Texten für die Liste; Ärmelabzeichen mit Sub-Listen innerhalb des Edit-Formulars).
Umsetzung: $editProtect = true an FormRendererFlex, kein Speichern-Button, Upload-Block ausgeblendet. Das Speichern selbst (phase == 1) und der Upload werden unabhängig davon serverseitig mit 403 gesperrt.
Ein früheres if (!userBerechtigtOK($Zugr)) { return; } am Anfang eines Edit-Scripts würde diese Anzeige verhindern (leere Seite). Es entfällt bei der Umstellung.
Felder: Die Listen-Klassen lesen den Datensatz vollständig und entfernen nicht benötigte Felder mit unset($row['feldname']); einige Inhalte werden vorher in andere Felder eingebaut (HTML, textarea). Das ist der Ort, an dem festgelegt wird, was eine Zielgruppe sieht. Felder, die ein Benutzer/Besucher nicht sehen darf, müssen dort entfernt werden – nicht erst in der Oberfläche.
Extern: Edit-Formular schreibgeschützt anzeigen über canViewEditForm($modul, $isPublicView): intern jeder Angemeldete, extern nur Module aus der Whitelist PUBLIC_VIEW_FORMS in AccessGuard.php (derzeit BU = Buchbesprechung und ORT = Ort-Formular mit den Sub-Listen Wappen/Ärmelabzeichen; Schlüssel = list_ID des Moduls, bitte bestätigen). Im Edit-Script:
php
  $isPublicView = AccessGuard::isPublicView();
  if (!$guard->canViewEditForm('BU', $isPublicView)) { http_response_code(403); exit('Keine Berechtigung.'); }
  $editProtect = $isPublicView || !$guard->canEditOeffentlichkeit();   // extern immer schreibgeschützt

Die Sub-Listen (Ärmelabzeichen ORT, OW, FW, AWA) innerhalb des Formulars laufen über ListHostAPI.php: sie stehen in $publicLists, editProtect ist dort für extern und für Benutzer ohne ADM-PSA bereits true.

8. Änderungen und Erweiterungen
8.1 Rolle ADM-FOTO → ADM-MEDIA umstellen (einmalig)
sql
-- 1. Betroffene Rollen ansehen
SELECT fl_id, fl_modules FROM fv_rollen_beschr WHERE fl_modules LIKE '%ADM-FOTO%';

-- 2. nach Sicherung umstellen
UPDATE fv_rollen_beschr
   SET fl_modules = REPLACE(fl_modules, 'ADM-FOTO', 'ADM-MEDIA')
 WHERE fl_modules LIKE '%ADM-FOTO%';

Im Code kommt ADM-FOTO nur noch als Altbestand in dieser Tabelle vor.

8.2 Neues mandantenbezogenes Modul (z. B. „Bibliothek“)
Rolle in fv_rollen_beschr anlegen, Code z. B. ADM-BIB in fl_modules; Benutzern über fv_rolle zuordnen.
AccessGuard::FACH_ROLLEN: 'BIB' => 'ADM-BIB' ergänzen. Mehr braucht canWriteMandantData('BIB', $ei_id) nicht.
AccessGuard::canUpload(): Zeile str_starts_with($key, 'BIB') => $eiId > 0 && $this->canWriteMandantData('BIB', $eiId).
UploadAPI.php: in $mandantBound den Präfix BIB aufnehmen, wenn Dokumente unter AOrd_Verz/{eigner}/… liegen.
Liste und Edit-Script nach den Mustern 6.1 und 6.2; in ListHostAPI.php einen Zweig für die list_ID mit editProtect nach dem Muster IN. Bei neuen Listen nichts weiter nötig, um sie nur für angemeldete Benutzer lesbar zu machen (Default).
Test: Rolle, ONU-ALL mit und ohne Mandant, ONU-GAST (Abschnitt 9).
8.3 Neues Modul ohne Mandantenbezug (z. B. neue Rolle ADM-XYZ)
Rolle anlegen wie oben.
In AccessGuard eine Methode canEditXyz() mit $this->auth->hasPermission('ADM-XYZ') (nach dem Vorbild canEditPsa()).
canUpload(): Zeile für das Präfix.
Edit-Script: Prüfung nach 6.2, aber ohne Mandantenvergleich.
8.4 Neue Liste
Nur für Angemeldete lesbar: nichts tun (Default-Deny in ListHostAPI.php).
Öffentlich (externe Seiten): list_ID in $publicLists aufnehmen. Prüfen, dass die Listen-Klasse keine Felder liefert, die extern nicht sichtbar sein dürfen (unset).
Nur für eine Rolle: Eintrag in $protectedListRoles.
Mandantenbezogen mit Schreibrecht: Zweig mit canWriteMandantData(...) wie bei IN.
8.5 Neuer Mandant

Im RBAC nichts zu tun. Fachrollen gelten automatisch für alle Mandanten. Für ONU-ALL-Benutzer, die dort schreiben sollen, fv_benutzer_mandant-Eintrag anlegen.

8.6 Neuer Benutzer
fv_benutzer (be_act = 'a'), fv_erlauben (Passwort-Hash).
fv_rolle: Zeile mit fl_id der Rolle, fr_aktiv = 'a'.
Nur ONU-ALL: je Mandant eine Zeile in fv_benutzer_mandant.
Vergabe durch ADM-ZUGR oder ADM-ALLE, im Verwaltungsformular vor dem Speichern canGrantRole($rolle) und canManageAccess() aufrufen.
8.7 Regel ändern (Beispiel: „ONU-ALL darf auch Fotos löschen“)

Nur in AccessGuard ändern (oder neue Methode ergänzen). Die Module bleiben unverändert, solange sie die Guard-Methode nutzen. Das ist der Hauptvorteil der zentralen Klasse.

8.8 Rolle oder Mandanten-Recht entziehen

fv_rolle.fr_aktiv = 'i' bzw. fv_benutzer_mandant.fm_aktiv = 'i' oder fv_benutzer.be_act = 'i'. Wirkt beim nächsten Request, auch bei laufender Session.

8.9 CSRF-Schutz für Edit-Formulare

Ein Token pro Sitzung ($_SESSION['AG_CSRF'], 64 Zeichen), im Formular als Hidden-Feld, beim Speichern geprüft. Eingebaut in BuchEdit, InvEdit, OrtsEdit und AermAbzEdit. Für jedes weitere Edit-Script, das speichert, zwei Zeilen:

php
// Formular (ph0-Include), nur wenn gespeichert werden darf:
if (!$editProtect) { echo AccessGuard::csrfField(); }

// Speichern (phase 1), NACH der Rechteprüfung:
AccessGuard::requireCsrf();    // 403 mit Meldung, wenn Token fehlt oder falsch ist

Hinweise:

Das Token-Feld heißt csrf; Scripts, die Felder per Prefix filtern (in_, fw_, …), übernehmen es nicht in die Daten.
Mehrere Browser-Tabs funktionieren (ein Token je Sitzung). Nach Logout/neuem Login ist ein offenes Formular ungültig und muss neu geladen werden.
Nicht abgedeckt: die AJAX-Endpunkte (UploadAPI.php, MultiSelect-/AutoComplete-APIs). Für UploadAPI.php müsste das Upload-JS (FotoUplLibs.js/Upload-Bibliothek) das Token mitsenden, dort ist die Rechteprüfung (Login + Modulrecht) derzeit der Schutz.
8.10 Frühere Klasse Authz

BUList.php und BuchEdit.php verwendeten FWHist\Auth\Authz (u. a. canEditReleasable, refreshIfStale, deny, CSRF). Beide sind auf AccessGuard umgestellt. Wird Authz noch anderswo benutzt, dort ebenfalls umstellen und die Klasse danach entfernen. Unterschied: Authz hielt Rechte offenbar in der Session und aktualisierte sie nach Ablauf, AccessGuard liest sie bei jedem Request frisch aus der DB.

8.11 Rollen-Codes in der Datenbank prüfen

Der Code verwendet diese Rollen-Codes. Sie müssen genau so in fv_rollen_beschr.fl_modules stehen (Groß-/Kleinschreibung, keine Leerzeichen):

ADM-ALLE, ADM-ZUGR, ADM-MA, ADM-MI, ADM-MB, ADM-ARC, ADM-INV, ADM-MEDIA, ADM-FZG, ADM-FREI, ADM-OEF, ADM-PSA, ADM-AB, ADM-FI, ONU-ALL, ONU-GAST

sql
SELECT fl_id, fl_modules FROM fv_rollen_beschr ORDER BY fl_id;

Fehlt ein Code (z. B. noch ADM-FOTO statt ADM-MEDIA, Abschnitt 8.1), ist die Berechtigung wirkungslos; es gibt keine Fehlermeldung.

9. Testliste (nach jeder Änderung)
Benutzer	Liste ansehen	Edit ansehen	Speichern	Upload
nicht angemeldet, intern	403	Weiterleitung/403	403	403
extern (öffentliche Seite)	nur Öffentlich-Liste	nur freigegebene Scripts, schreibgeschützt	403	403
ONU-GAST	ja	schreibgeschützt	403	403
ONU-ALL, Mandant nicht zugeordnet	ja	schreibgeschützt	403	403
ONU-ALL, Mandant zugeordnet	ja	bearbeitbar	ja	ja (nur eigener Mandant)
Fachrolle (z. B. ADM-INV)	ja	bearbeitbar, jeder Mandant	ja	ja
ADM-ALLE	ja	ja	ja	ja
manipulierter Request (ei_id/eigner/storPath fremd)	–	–	403 bzw. Datei abgelehnt	abgelehnt

Auch prüfen: Die Action-Spalte ist für ONU-ALL und ONU-GAST sichtbar (Label „Anzeigen“) und öffnet das Formular schreibgeschützt; „Neu anlegen“ fehlt. Auf externen Seiten nur bei freigegebenen Listen.

10. Umstellungsstand und offene Punkte
Bereich	Stand
Auth.php, AccessGuard.php, fv_benutzer_mandant	fertig
ListHostAPI.php, UploadAPI.php	umgestellt, Test positiv (Listen extern OEF/PSA ok)
Inventar (InventarList, InvEdit, InvEdit_ph0_inc)	umgestellt
Listen-Klassen BUList, OrtsList, OWAList, FWAList, AWAList, InventList	umgestellt (Allowlist, Action-Link, Escape der Dateinamen)
BuchEdit.php (+_ph0_inc), OrtsEdit.php (+_ph0_inc), AermAbzEdit.php (+_ph0_inc)	umgestellt
CSRF-Token in Edit-Formularen	BuchEdit, InvEdit, OrtsEdit, AermAbzEdit fertig; alle weiteren Edit-Scripts beim Umstellen (8.9); Upload-/AJAX-APIs offen
Include-Listen OrtsWappenList_inc.php, FwWappenList_inc.php, AermAbzList_inc.php	geprüft, keine Änderung nötig
OWAEdit.php, FWAEdit.php (+_ph0_inc; Nachfolger von VF_PS_OV_OW_Edit.php und VF_PS_OV_FW_Edit.php)	umgestellt; Bild-Upload noch nicht angebunden (wie bei AermAbzEdit.php)
Alte Scripts VF_PS_OV_OW_*, VF_PS_OV_FW_* und die zugehörigen Includes	vom Server entfernen: kein RBAC, SQL per String-Verkettung, Tabellen aw_*
AermDetail.php, EhrgDetail.php	entfallen (SHOW_DETAIL_LINKS = false in OrtsList.php, nach Test löschen)
Öffentliche Formulare Email2Vorst.php (neu), MitglAnmeld.php (+Includes), PublicFormGuard, NumberCaptcha, RateLimiter, VorstandRepository	umgestellt (6.12); MitglRegBestaetigung.php (Token-Bestätigung) und PHP_MailFuncsLib.php noch nicht gesehen
Menüs (MainMenu, ZentralVerw, MitglVerw, main/zentrVerw/mitglVerw)	umgestellt (6.11); Zielseiten FirmenList, AbkuerzList, AdmEmailList, ConfEdit, VF_Z_* noch zu prüfen
Mandanten-Stammdaten (MandantenList, MandEdit, MandList, DB_Mandanten), Autocomplete-APIs für Mandant/Urheber	umgestellt (6.9)
FormRendererFlex_Ext.php, AutoCompForm_*()-Wrapper in CommFuncsLib.php	fertig (6.10); bestehende Aufrufer (BenEdit, OrtsEdit, InvEdit, InventarList) laufen über die Wrapper, bei Gelegenheit auf renderAutoComp*() umstellen
Benutzer-/Rollen-/Mandanten-Verwaltung (Ben*, Rollen*, MandErl*, Listen BE/RO/ROM)	umgestellt (6.8); MandErl*/ROMList ergänzt, gegen die Tabelle fv_benutzer_mandant und ma_mandanten zu prüfen
Archiv, Medien, Fahrzeuge/Geräte, Mitglieder, übrige ÖA-Module, PSA-Stifter/Ehrenzeichen	offen, Muster 6.1/6.2/6.5
FWHist\Auth\Authz (bisher in BUList.php verwendet)	durch AccessGuard ersetzt; weitere Verwendungen suchen und umstellen, dann Klasse entfernen
Benutzer-/Rollenverwaltung (ADM-ZUGR)	offen, canGrantRole() vor dem Speichern aufrufen
Freigabe Buch-Rezensionen (ADM-FREI)	fertig (Guard, BUList, BuchEdit)
Action-Spalte steuerbar (listActionConfig), Inventar-Liste und InventList umgestellt	fertig; alle übrigen Listen-Klassen nach Muster 6.5 anpassen (Action-Link immer, Allowlist der Felder)
Extern: Edit-Formular schreibgeschützt (Buchbesprechung, Ärmelabzeichen-Sub-Listen)	canViewEditForm vorhanden; Whitelist bestätigen, in BuchEdit/Ärmelabzeichen-Edit einbauen
userHasRole() (FuncsLib/CommFuncsLib) und userBerechtigtOK()	nach Umstellung aller Module entfernen
CommFuncsLib-Requires in ListHostAPI.php/UploadAPI.php	entfernen, sobald dort keine Funktion mehr gebraucht wird
medienRegistrierenSicher() (MedienDbLib.php)	urhNr zusätzlich als Zahl prüfen (Tabellenname {urhNr}_dmedien)
Rolle ADM-FOTO in fv_rollen_beschr	auf ADM-MEDIA umstellen (8.1)