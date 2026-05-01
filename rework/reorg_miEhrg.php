<?php
/** reorganisation von ffhist_arch auf fharch_neu der Tabelle fh_m_ehrung -> fv_mi_ehrung
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
 * fh_m_ehrung>> -> fv_mi_ehrung
 *
 * Tabelle neu anlegen, dann übertragen
 */
$debug = false;
$path2ROOT = "../../";
require "../common/BS_Funcs.lib.php";

$db = link_DB('xY');

/** einlesen fv_mitglieder in arr */

$sql = "SELECT * FROM fh_m_ehrung ORDER BY fe_lfnr ASC ";

$ret = SQL_QUERY($db, $sql);

$mi_arr = [];

WHILE ( $row = mysqli_fetch_object($ret) ) {
    $mi_arr[$row->fe_lfnr] = $row;
}
var_dump($mi_arr);

/** Array abarbeiten */

$stmt = $db->prepare("INSERT INTO fv_mi_ehrung (
    mi_id, me_ehrung, me_eh_datum, me_begruendg, me_bild1, me_bild2,
    me_bild3, me_bild4, me_changed_id, me_changed_at
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

foreach ($mi_arr as $row) {
    
    $row->fe_eh_datum = normalizeDate($row->fe_eh_datum);
    
    $changed_id = intval($row->fe_uidaend);
    
    $stmt->bind_param(
        "isssssssis",
        $row->fe_m_id, $row->fe_ehrung, $row->fe_eh_datum, $row->fe_begruendg, $row->fe_bild1, $row->fe_bild2,
        $row->fe_bild3, $row->fe_bild4,  $changed_id, $row->fe_aenddat
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

