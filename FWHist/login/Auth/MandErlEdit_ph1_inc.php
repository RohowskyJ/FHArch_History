<?php

/**
 * Mandanten-Erlaubnis zu einem Benutzer, Daten schreiben
 *
 * $neu (Mandant, Status, Zeitstempel, Bearbeiter, bei Neuanlage Benutzer) wurde in MandErlEdit.php geprüft und
 * zusammengestellt: Mandant existiert, ist dem Benutzer noch nicht zugeordnet, CSRF-Token ist geprüft.
 *
 * @author Josef Rohowsky - neu 2018
 */
if ($debug) {
    echo "<pre class=debug>MandErlEdit_ph1_inc.php ist gestarted</pre>";
}

if ($existing === null) {
    $recno = $benu->insertMandantPerm($neu);
} else {
    $ret = $benu->updateMandantPermById($fm_id, $neu);
}

echo "Die Daten wurden gespeichert.<br>";
echo "Sie werden gleich zurückgeleitet.";

echo '<script>
    setTimeout(function() {
        window.location.href = "BenEdit.php?ID=' . urlencode((string) $profileId) . '";
    }, 2000); // 2 Sekunden warten
</script>';
exit;
?>
