<?php

/**
 * Automatische Benachrichtigung für ADMINS bei Änderungen, Wartun, Daten schreiben
 *
 * @author Josef Rohowsky - neu 2023
 *
 */
if ($debug) {
    echo "<pre class=debug>AdmEmailEdit_ph1.inc.php ist gestarted</pre>";
}

foreach ($_POST as $name => $value) {
    $neu[$name] = trim($value);
}

$neu['em_changed_id'] = $_SESSION['BS_Prim']['BE']['be_id'];
$neu['em_changed_at'] = date("Y-m-d H:m:s");

if ($debug) {
    echo '<pre class=debug>';
    echo '<hr>$neu: ';
    print_r($neu);
    echo '</pre>';
}

unset($neu['fd_name']);
unset($neu['benutzer']);
unset($neu['ben_id']);
unset($neu['phase']);

if ($neu['em_id'] == 0) { // update
    $neu = $emai->gcreateAdminMail($neu);
} else { // neuer Datensatz
    $neu = $emai->updateAdminMail($em_id, $neu) ;
}

header("Location: AdmEmailList.php");

if ($debug) {
    echo "<pre class=debug>AdmEmailEdit_ph1.inc.php beendet</pre>";
}
?>