<?php

/**
 * Unterstützerverwaltung, Date abspeichern
 *
 * @author Josef Rohowsky - neu 2020
 *
 */
# var_dump($_POST);

/**
 * Includes-Liste
 * enthält alle jeweils includierten Script Files
 */
$_SESSION[$module]['Inc_Arr'][] = "VS_UnterstEdit_ph1.inc.php"; 

if ($debug) {
    echo "<pre class=debug>VS_UnterstEdit_ph1.inc.php ist gestarted</pre>";
}
#var_dump($neu);

unset($neu['phase']);

$neu['fu_changed_id'] = $_SESSION['BS_Prim']['BE']['be_id'];
$id = $neu['fu_id'];

#var_dump($neu);

if ($neu['fu_id'] == 0) { // Neu anlegen eines Mitglieds- Datensatzes
    
    try {
       $insert_id = $mitgl->createUnterst($neu);
       
    } catch (Exception $e) {
        echo "Exception: " . $e->getMessage();
    }

    ob_flush();
    
  
} else { // ändern eines Mitglieds- Daensatzes
    
    try {
        $result = $mitgl->updateUnterst($neu['fu_id'], $neu);
        #var_dump($result);
    } catch (Exception $e) {
        echo "Exception: " . $e->getMessage();
    }
    # $result = $mitgl->updateMitglied($id, $neu);
    #var_dump($result);
    ob_flush();

}

header ("Location: VF_UnterstList.php");

if ($debug) {
    echo "<pre class=debug>VS_UnterstEdit_ph1.inc.php beendet</pre>";
}
?>