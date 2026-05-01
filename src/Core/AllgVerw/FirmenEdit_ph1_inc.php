<?php

/**
 * Firmen- Verwaltung, Warten, Daten schreiben
 * 
 * @author Josef Rohowsky - neu 2018
 * 
 */

if ($debug) {
    echo "<pre class=debug>FirmenEdit_ph1_inc.php ist gestarted</pre>";
}

unset($neu['phase']);

$neu['fi_changed_id'] = $_SESSION['BS_Prim']['BE']['be_id'];
$neu['fi_changed_at'] =  date("Y-m-d H:i:s");

if ($neu['fi_id'] == "0") {

    $recno = $firm->createFirmen($neu);
 
} else {
    
    $ret = $firm->updateFirmen($fi_id, $neu);
    
}

header ('Location: FirmenList.php');

if ($debug) {
    echo "<pre class=debug>FirmenEdit_ph1_inc.php beendet</pre>";
}
?>