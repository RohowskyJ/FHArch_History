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
$_SESSION[$module]['Inc_Arr'][] = "RollenEdit_ph1_inc.php";

if ($debug) {
    echo "<pre class=debug>RollenEdit_ph1_inc.php ist gestarted</pre>";
}

if ($neu['descript'] != '' && ($neu['descript'] != $neu['fl_id'])) {
    $neu['fl_id'] = $neu['descript'];
}

unset($neu['Rolle']);
unset($neu['descript']);
unset($neu['phase']);

$neu['fr_changed_id'] = $_SESSION['BS_Prim']['BE']['be_id'];
$neu['fr_changed_at'] =  date("Y-m-d H:i:s");

if ($neu['be_id'] == "0") {
    
    $neu['fr_created_id'] = $_SESSION['BS_Prim']['BE']['be_id'];
    $neu['fr_created_at'] =  date("Y-m-d H:i:s");
    
    $recno = $benu->createRoleByBen($neu);
 
} else {
    
    $ret = $benu->updateRoleById($fr_id, $neu);
    
}

echo "Die Daten wurdden gespeichert.<br>";
echo "Sie werden gleich zurückgeleitet.";

echo '<script>
    setTimeout(function() {
        window.location.href = "BenEdit.php?ID=' . urlencode($be_id) . '";
    }, 2000); // 2 Sekunden warten
</script>';
exit;

if ($debug) {
    echo "<pre class=debug>RollenEdit_ph1_inc.php beendet</pre>";
}
?>