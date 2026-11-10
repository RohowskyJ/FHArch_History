<?php

/**
 * Listen-Konfiguration "Gemeinde-Wappen" (OW), verknüpft über fw_id.
 * Gibt nur den Container-HTML-Block aus und hängt die Konfiguration an
 * $allLists an - das gemeinsame <script>const lists=...</script> und die
 * JS-Includes werden zentral in OrtsEdit_ph0_inc.php erzeugt.
 */

$list_ID = 'OW';

$canEdit = $canEdit ?? false;
$NeuRec = "";
if ($canEdit) {
    $NeuRec = "OWAEdit.php?ID=0&proj=$proj";
}

$ActionScript = 'OWAEdit.php?proj=' . $proj;

$listConfigOW = [
    'id' => 'OW',
    'container' => 'OW-table',
    'apiUrl' => $basePath . '/FWHist/login/Services/API/ListHostAPI.php',
    'title' => 'Ortswappen-Liste',
    'editScript' => $NeuRec,
    'actionScript' => $ActionScript,
    'actionLabel' => 'Bearbeiten',
    'newLabel' => 'Neuen Datensatz anlegen',
    'showActionColumn' => $canEdit,
    'filterSelectFields' => [],
    'filterTextFields' => [],
    'paginationSizes' => [5, 10, 25, 50, 100],
];

$allLists[] = $listConfigOW;
?>
<div id="root-OW">
    <h3>Ortswappen-Liste</h3>
    <div id="OW-table"></div>
</div>
