<?php

/**
 * Abkürzungs- Verwaltung, Warten, Daten schreiben
 * 
 * @author Josef Rohowsky - neu 2018
 * 
 */

/**
 * Includes-Liste
 * enthält alle jeweils includierten Scritpt Files
 */
$_SESSION[$module]['Inc_Arr'][] = "AbkuerzEdit_ph1_inc.php";

if ($debug) {
    echo "<pre class=debug>AbkuerzEdit_ph1_inc.php ist gestarted</pre>";
}

unset($neu['phase']);

$neu['ab_changed_id'] = $_SESSION['BS_Prim']['BE']['be_id'];
$neu['ab_changed_at'] =  date("Y-m-d H:i:s");

if ($neu['ab_id'] == "0") {

    $recno = $DBD->createabkuerz($neu);
 
} else {
    
    $ret = $abku->updateAbkuerz($ab_id, $neu);
    
}

header ('Location: VS_AbkuerzList.php');

if ($debug) {
    echo "<pre class=debug>AbkuerzEdit_ph1_inc.php beendet</pre>";
}
?>