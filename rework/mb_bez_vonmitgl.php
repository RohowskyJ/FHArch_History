<?php
/** 
 * Einlesen der Bezahlungs.- Logs in fv_mi_bez
 * 
 * 
 * Einlesen von /login/log/MitglBeitrLog/ alle files
 * 
 * 
 * 
 */

$debug = false;
$path2ROOT = "../../";

require "../common/BS_Funcs.lib.php";

$db = link_DB('xY');

/** einlesen fv_mitglieder in arr */

$sql = "SELECT * FROM fv_mitglieder ORDER BY mi_id ASC ";

$ret = SQL_QUERY($db, $sql);

$currJahr = date("Y");
$j_1 = $currJahr - 1 ;
if ($ret) {
    WHILE  ($row = mysqli_fetch_object($ret) ) {
        $mi_id = 0;
        $mb_b_j = '';
        $bezDat_m = $bezDat_a = "";
        $uid = 0; 
        if ($row->mi_m_beitr_bez_bis != "") {
            if ($row->mi_m_beitr_bez_bis >= $j_1) {
                $mi_id = $row->mi_id;
                $mb_b_j = $row->mi_m_beitr_bez_bis;
                $mb_a_j = $row->mi_m_abo_bez_bis;
                $bezDat_m = $row->mi_m_beitr_bez;
                $bezDat_a = $row->mi_m_abo_bez;
                $uid = $row->mi_changed_id;
             
                add_bez($mi_id, $mb_b_j, $mb_a_j, $bezDat_m, $bezDat_a, $uid);     
            }
        }    
    }
    
}


function add_bez($mi_id, $mb_b_j, $mb_a_j, $bezDat_m, $bezDat_a, $uid) {
    global $db;
    echo __LINE__ . " parms: $mi_id, $mb_b_j, $mb_a_j, $bezDat_m, $bezDat_a,  $uid <br>";
    
    $m_mb = $m_ab = "";
  

    $sql = "INSERT INTO `fv_mi_bez` (mi_id, mb_bez_mb_bis, mb_bez_abo_bis, mb_bez_mb_jahr, mb_bez_abo_jahr, mb_changed_id)
            VALUES ('$mi_id', '$mb_b_j', '$mb_a_j', '$bezDat_m', '$bezDat_a', '$uid')";
    echo __LINE__ . " sql $sql <br>";
    
    $ret = SQL_QUERY($db, $sql);
}
