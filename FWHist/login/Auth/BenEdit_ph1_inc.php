<?php

/**
 * Benutzervrwaltung, Warten, Daten schreiben
 * 
 * @author Josef Rohowsky - neu 2018
 * 
 */

if ($debug) {
    echo "<pre class=debug>BenEdit_ph1_inc.php ist gestarted</pre>";
}

#var_dump($neu);
if ($neu['staat_id'] != '') {
    $neu['fd_staat_abk'] = $neu['staat_id'];
}

if ($neu['fd_geb_dat'] == "") {
    $neu['fd_geb_dat'] = "0000-00-00";
}
if ($neu['fd_sterb_dat'] == "") {
    $neu['fd_sterb_dat'] = "0000-00-00";
}
if ($neu['fd_austr_dat'] == "") {
    $neu['fd_austr_dat'] = "0000-00-00";
}
# var_dump($neu);
unset($neu['cust_ID']);
unset($neu['proj']);
unset($neu['list_ID']);
unset($neu['staat']);
unset($neu['staat_id']);
unset($neu['phase']);

date_default_timezone_set('Europe/Berlin');
$neu['fd_changed_id'] = $guard->userId();
$neu['fd_changed_at'] =  date("Y-m-d H:i:s");

if ($fd_id === 0) {                       // neues Profil (Schlüssel ist die Profil-Nr fd_id, nicht be_id)
    unset($neu['fd_id']);                 // AUTO_INCREMENT vergibt die Nr
    $neu['fd_created_id'] = $guard->userId();
    $neu['fd_created_at'] =  date("Y-m-d H:i:s");

    $recno = $benu->createUserData($neu);

} else {
    unset($neu['fd_id'], $neu['be_id']);  // Schlüssel und Benutzer-Zuordnung ändern sich beim Speichern nicht

    $ret = $benu->updateUserData((string) $fd_id, $neu);

}

header ('Location: BenList.php');
exit;

if ($debug) {
    echo "<pre class=debug>BenEdit_ph1_inc.php beendet</pre>";
}
?>