<?php
/** reorganisation von ffhist_arch auf fharch_neu
 * einlesen alt und ausgabe neu mit Korrekturen
 * 
 * aenduid von varchar auf int mi_changed_id
 * alle Datumer mi_gebtag, mi_beitr -> mi_eintrdat, m_sterbdat, mi_austrdat, mi_beitr_bez, mi_abo_bez
 * 
 * mi_tel_handy führender , entfernen
 * 
 * wenn verstorben: leeren: Adresse, TelNr, E-mail, mi_ref*
 * wenn austritt: e-Mail @ -> (AT)
 * anrede == bl _Meldung
 * 
 * 
 * fh_mitglieder -< fv_mitglieder_reo
 * 
 * Tabelle neu anlegen, dann übertragen
 */
$debug = false;
$path2ROOT = "../../";
require "../common/BS_Funcs.lib.php";

$db = link_DB('xY');

/** einlesen fv_mitglieder in arr */

$sql = "SELECT * FROM fh_mitglieder ORDER BY mi_id ASC ";

$ret = SQL_QUERY($db, $sql);

$mi_arr = [];

WHILE ( $row = mysqli_fetch_object($ret) ) {
    $mi_arr[$row->mi_id] = $row;
}
# var_dump($mi_arr);

/** Array abarbeiten */

$stmt = $db->prepare("INSERT INTO fv_mitglieder_reo (
    mi_mtyp, mi_org_typ, mi_org_name, mi_name, mi_vname, mi_titel,
    mi_n_titel, mi_dgr, mi_anrede, mi_gebtag, mi_staat, mi_plz, mi_ort, mi_anschr,
    mi_tel_handy, mi_fax, mi_email, mi_email_status, mi_vorst_funct, mi_ref_leit, mi_ref_int_2, mi_ref_int_3, mi_ref_int_4,
    mi_sterbdat, mi_eintrdat, mi_austrdat,
    mi_einv_art, mi_einversterkl, mi_einv_dat, mi_changed_id, mi_changed_at,
    mi_m_beitr_bez, mi_m_abo_bez, mi_m_beitr_bez_bis, mi_m_abo_bez_bis
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

foreach ($mi_arr as $row) {
    
    $row->mi_gebtag = normalizeDate($row->mi_gebtag);
    $row->mi_sterbdat = normalizeDate($row->mi_sterbdat);
    $row->mi_austrdat = normalizeDate($row->mi_austrdat);
    $row->mi_beitritt = normalizeDate($row->mi_beitritt);
    $row->mi_m_beitr_bez = normalizeDate($row->mi_m_beitr_bez);
    $row->mi_m_abo_bez = normalizeDate($row->mi_m_abo_bez);
    $row->mi_m_beitr_bez   = normalizeDate($row->mi_m_abo_bez);
    $row->mi_m_abo_bez   = normalizeDate($row->mi_m_abo_bez); 

    $changed_id = intval($row->mi_uidaend);
    
    $stmt->bind_param(
        "sssssssssssssssssssssssssssssisssss",
        $row->mi_mtyp, $row->mi_org_typ, $row->mi_org_name, $row->mi_name, $row->mi_vname, $row->mi_titel,
        $row->mi_n_titel, $row->mi_dgr, $row->mi_anrede, $row->mi_gebtag, $row->mi_staat, $row->mi_plz, $row->mi_ort, $row->mi_anschr,
        $row->mi_tel_handy, $row->mi_fax, $row->mi_email, $row->mi_email_status, $row->mi_vorst_funct, $row->mi_ref_leit, $row->mi_ref_int_2, $row->mi_ref_int_3, $row->mi_ref_int_4,
        $row->mi_sterbdat, $row->mi_beitritt, $row->mi_austrdat,
        $row->mi_einv_art, $row->mi_einversterkl, $row->mi_einv_dat, $changed_id, $row->mi_aenddat,
        $row->mi_m_beitr_bez, $row->mi_m_abo_bez, $row->mi_m_beitr_bez_bis, $row->mi_m_abo_bez_bis
        );
    
    $stmt->execute();
}

function normalizeDate($dateStr) {
    if ($dateStr === "" || $dateStr === NULL) {
        return NULL;
    }
    $date = DateTime::createFromFormat('Y-m-d', $dateStr);
    return ($date && $date->format('Y-m-d') === $dateStr) ? $date->format('Y-m-d') : NULL;
}
