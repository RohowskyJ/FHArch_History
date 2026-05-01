<?php

/**
 * Benutzervrwaltung, Warten, Daten schreiben
 * 
 * @author Josef Rohowsky - neu 2018
 * 
 */

/**
 * Includes-Liste
 * enthält alle jeweils includierten Scritpt Files
 */
$_SESSION[$module]['Inc_Arr'][] = "BenEdit_ph1_inc.php";

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

unset($neu['list_ID']);
unset($neu['staat']);
unset($neu['staat_id']);
unset($neu['phase']);

$neu['fd_changed_id'] = $_SESSION['BS_Prim']['BE']['be_id'];
$neu['fd_changed_at'] =  date("Y-m-d H:i:s");

if ($neu['be_id'] == "0") {
    
    $neu['fd_created_id'] = $_SESSION['BS_Prim']['BE']['be_id'];
    $neu['fd_created_at'] =  date("Y-m-d H:i:s");
    
    $recno = $benu->createUserData($neu);
 
} else {
    
    $ret = $benu->updateUserData($fd_id, $neu);
    
}

header ('Location: BenList.php');

if ($debug) {
    echo "<pre class=debug>BenEdit_ph1_inc.php beendet</pre>";
}
?>