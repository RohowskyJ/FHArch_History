<?php

/**
 * Listen-Konfiguration "Ärmel- und T-Shirt-Abzeichen" (AWA), verknüpft über fw_id.
 * Ersetzt das alte AWA_Liste_API.php-basierte System - läuft jetzt über die
 * gemeinsame ListInterface/ListHostAPI-Infrastruktur, genau wie OW und FW.
 * Gibt nur den Container-HTML-Block aus und hängt die Konfiguration an
 * $allLists an; das gemeinsame <script>const lists=...</script> und die
 * JS-Includes werden zentral in OrtsEdit_ph0_inc.php erzeugt.
 */

$list_ID = 'AWA';

$canEdit = $canEdit ?? false;
$ActionScript = '';
$NeuRec = "";
if ($canEdit) {
    $NeuRec = "AermAbzEdit.php?ID=0&fo_fw_id=$fw_id&proj=$proj";
    $ActionScript = 'AermAbzEdit.php?fo_fw_id=' . $fw_id . '&proj=' . $proj;
}

$listConfigAWA = [
    'id' => 'AWA',
    'container' => 'AWA-table',
    'apiUrl' => $basePath . '/FWHist/login/Services/API/ListHostAPI.php',
    'title' => 'Ärmel- und T-Shirt-Abzeichen',
    'editScript' => $NeuRec,
    'actionScript' => $ActionScript,
    'actionLabel' => 'Bearbeiten',
    'newLabel' => 'Neue Daten anlegen',
    'showActionColumn' => $canEdit,
    'filterSelectFields' => [],
    'filterTextFields' => [],
    'paginationSizes' => [5, 10, 25, 50, 100],
];

$allLists[] = $listConfigAWA;
?>
<div id="root-AWA">
    <h3>Ärmel- und T-Shirt-Abzeichen</h3>
    <div id="AWA-table"></div>
</div>
