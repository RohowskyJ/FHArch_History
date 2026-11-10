<?php

/**
 * Listen-Konfiguration "Wappen im Feuerwehrgebrauch" (FW), verknüpft über fw_id.
 * Gibt nur den Container-HTML-Block aus und hängt die Konfiguration an
 * $allLists an - das gemeinsame <script>const lists=...</script> und die
 * JS-Includes werden zentral in OrtsEdit_ph0_inc.php erzeugt.
 */

$list_ID = 'FW';

$canEdit = $canEdit ?? false;
$ActionScript = '';
$NeuRec = "";
if ($canEdit) {
    $NeuRec = "FWAEdit.php?ID=0&fo_fw_id=$fw_id&proj=$proj";
    $ActionScript = 'FWAEdit.php?proj=' . $proj;
}

$listConfigFW = [
    'id' => 'FW',
    'container' => 'FW-table',
    'apiUrl' => $basePath . '/FWHist/login/Services/API/ListHostAPI.php',
    'title' => 'Wappen im Feuerwehrgebrauch',
    'editScript' => $NeuRec,
    'actionScript' => $ActionScript,
    'actionLabel' => 'Bearbeiten',
    'newLabel' => 'Neuen Gebrauch anlegen',
    'showActionColumn' => $canEdit,
    'filterSelectFields' => [],
    'filterTextFields' => [],
    'paginationSizes' => [5, 10, 25, 50, 100],
];

$allLists[] = $listConfigFW;
?>
<div id="root-FW">
    <h3>FW-Liste zur Auswahl</h3>
    <div id="FW-table"></div>
</div>
