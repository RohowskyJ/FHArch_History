<?php

/**
 * NeuOrganisation der Zgriffsberechtigungen
 * 
 * mit ba_funcs wegen SQL_QUERY($db, $sql)
 * 
 * wenn PW noch nicht geändert im neuen Table gleich, sonst be_id
 * 
 * einlesen von fh_benutzer und fh_zugriffe_n in array
 * 
 * für jeden Eintrag in 
 *
 */

$path2ROOT = "../";
$debug = false;
require "common/BS_Funcs.lib.php";


$db = $dblink = mysqli_connect('localhost', 'root', 'b1teller') or die('Verbindung zu MySQL gescheitert!' . mysqli_connect_error());

mysqli_select_db($dblink, 'fharch_comm') or die("Datenbankzugriff zu $database gescheitert!");
# if ($debug) { echo "<pre class=debug> mysqli_select_db:"; print_r($dblink); echo '</pre>'; }
mysqli_set_charset($dblink, 'utf8mb4');
#$LinkDB_database = $database; # wird in Funktion Tabellen_Spalten_v2.php verwendet

/** einlesen fh_benutzer in array */
 $ben_arr = [];
 
 $sql = "SELECT * FROM fh_benutzer ORDER BY be_id ASC ";
 
 $return = SQL_QUERY($db,$sql);
 
 while ($row = mysqli_fetch_object($return)) {
     $ben_arr[$row->be_id] = $row;
 }
# var_dump($ben_arr);
 
 $zugr_arr = [];
 
 /** einlesen fh_zugriffe_n */
 $sql = "SELECT * FROM fh_zugriffe_n ORDER BY zu_id ASC ";
 
 $return = SQL_QUERY($db,$sql);
 
 while ($row = mysqli_fetch_object($return)) {
     $zugr_arr[$row->zu_id] = $row;
 }
 # var_dump($zugr_arr);
 
 $defPW_hash = '$1$banane1a$oUJmYYTChsVwW/Qs/Hl/t0'; //für banane1a
 
 /** für jeden Eintrag in $ben_arr[id] und $zugr_arr[id} entsprechende Daten nach fv_benutzer, fv_ben_dat, fv_erlauben, fv_rolle, fv_mand_erl anlegen */

 foreach ($ben_arr as $be_id => $be_row) {
     /** Daten von fv_benutzer nach fs_benutzer und fs_ben_dat */
     
     $sql_ben = "INSERT into fv_benutzer (
               be_uid,be_2fa_secret,be_2fa_enabled,be_2fa_email,
               be_created_id,be_created_at,be_changed_id,be_changed_at
             ) VALUES (
               '$be_row->be_email','','0','$be_row->be_email',
               '$be_row->be_uidaend','$be_row->be_aenddat','0','0000-00-00 00:00:00'
             )";
     
     $ret_ben = SQL_QUERY($db,$sql_ben);
     $recNr = mysqli_insert_id($db);

     $sql_ben_d = "INSERT into fv_ben_dat (  
               be_id,be_mi_id,fd_anrede,fd_tit_vor,fd_vname,fd_name,fd_tit_nach,
               fd_adresse,fd_plz,fd_ort,fd_staat_abk,fd_tel,fd_email,
               fd_hp,fd_sterb_dat,fd_austr_dat,
               fd_created_id,fd_created_at,fd_changed_id,fd_changed_at
             ) VALUES (
               '$recNr','0','$be_row->be_anrede','$be_row->be_titel','$be_row->be_vname','$be_row->be_name','$be_row->be_n_titel',
               '$be_row->be_adresse', '$be_row->be_plz', '$be_row->be_ort','$be_row->be_staat', '$be_row->be_telefon','$be_row->be_email',
               '', '0000-00-00', '0000-00-00',
               '$be_row->be_uidaend','$be_row->be_aenddat','0','0000-00-00 00:00:00'
             )";
    
     $ret_ben_d = SQL_QUERY($db,$sql_ben_d);
     
     if (!isset($zugr_arr[$be_id])) {
         continue;
     }
     $row_zug = $zugr_arr[$be_id];
    
     $pw_hold  = $row_zug->zu_pw_enc;
     
     if ($pw_hold == $defPW_hash ) {
         $pwHash = password_hash('banane1a',PASSWORD_BCRYPT);
     } else {
         $pwHash = password_hash($be_id,PASSWORD_BCRYPT);
     }
         
     $sql_erl = "INSERT into fv_erlauben (
               be_id,fe_pw,fe_pw_chgd_id,fe_pw_chgd_at,
               fe_created_id,fe_created_at,fe_changed_id,fe_changed_at
             ) VALUES (
               '$recNr','$pwHash','0','0000-00-00',
               '$be_row->be_uidaend','$be_row->be_aenddat','0','0000-00-00 00:00:00'
             )";
     
     $ret_erl = SQL_QUERY($db,$sql_erl);
     
     if ($row_zug->zu_id == 1) {
         $erl = '1';
     } else {
         $erl = '14';
     }
     
     $sql_rolle = "INSERT into fv_rolle (
               be_id,fl_id,
               fr_created_id,fr_created_at,fr_changed_id,fr_changed_at
             ) VALUES (
               '$recNr','$erl',
               '$be_row->be_uidaend','$be_row->be_aenddat','0','0000-00-00 00:00:00'
             )";
     
     $ret_rolle = SQL_QUERY($db,$sql_rolle);
     
     $erlb = 'update';
     if ($row_zug->zu_eignr_1 == 0 || $row_zug->zu_eignr_1 == 600 || ($row_zug->zu_eignr_1 >= 605 && $row_zug->zu_eignr_1 <= 609) ) {
         $row_zug->zu_eignr_1 =  1 ;
         $erlb = 'nix';
     }
     $sql_mande = "INSERT into fv_mand_erl (
               be_id,ei_id,fu_erlauben,
               fu_created_id,fu_created_at,fu_changed_id,fu_changed_at
             ) VALUES (
               '$recNr','$row_zug->zu_eignr_1','$erlb',
               '$be_row->be_uidaend','$be_row->be_aenddat','0','0000-00-00 00:00:00'
             )";
     
     $ret_mande = SQL_QUERY($db,$sql_mande);

     
 }
 
 
 
 
 
 