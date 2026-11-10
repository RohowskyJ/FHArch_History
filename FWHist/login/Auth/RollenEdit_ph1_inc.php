<?php

/**
 * Rollenzuordnung zu einem Benutzer, Daten schreiben
 *
 * $neu (Rolle, Status, Zeitstempel, Bearbeiter, bei Neuanlage Benutzer) wurde in RollenEdit.php geprüft und
 * zusammengestellt: Rolle muss vergeben werden dürfen, Zuordnung gehört zum Benutzer, CSRF-Token ist geprüft.
 *
 * @author Josef Rohowsky - neu 2018
 */
if ($debug) {
    echo "<pre class=debug>RollenEdit_ph1_inc.php ist gestarted</pre>";
}

if ($existing === null) {
    $recno = $benu->insertRoleData($neu);
} else {
    $ret = $benu->updateRoleById((string) $fr_id, $neu);
}

echo "Die Daten wurden gespeichert.<br>";
echo "Sie werden gleich zurückgeleitet.";

echo '<script>
    setTimeout(function() {
        window.location.href = "BenEdit.php?ID=' . urlencode($be_id) . '";
    }, 2000); // 2 Sekunden warten
</script>';
exit;
?>
