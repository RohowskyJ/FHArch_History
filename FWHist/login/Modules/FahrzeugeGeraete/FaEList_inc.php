<?php

/**
 * Listen-Konfiguration "Gemeinde-Wappen" (OW), verknüpft über fw_id.
 * Gibt nur den Container-HTML-Block aus und hängt die Konfiguration an
 * $allLists an - das gemeinsame <script>const lists=...</script> und die
 * JS-Includes werden zentral in OrtsEdit_ph0_inc.php erzeugt.
 */

$list_ID = 'FAE';
$editProtect = $editProtect ?? true;

$lists = [
    [
    'id' => 'FAE',
    'container' => 'FAE-table',
    'apiUrl' => $basePath . '/FWHist/login/Services/API/ListHostAPI.php',
    'title' => 'Eigntümer-Liste',
    'editScript' => !$editProtect ? "FaEEdit.php?fz_id={$neu['fz_id']}&ei_id=$ei_id" : '',
    'showActionColumn' => true,
    'actionLabel' => $editProtect ? 'Anzeigen' : 'Bearbeiten',
    'filterSelectFields' => [],
    'filterTextFields' => [],
    'paginationSizes' => [5, 10, 25, 50, 100],
    ]
];

// Globales Parameter-Array
$globalParams = [
    'owner' => $ei_id,
    'user' => $_SESSION['BS_Prim']['BE']['be_id'],
    'project' => $neu['fz_id'],
    'sel_id' => $ei_id ?? null,
    'edit_Protect' => $editProtect,
];

?>
<script>
globalThis.lists = globalThis.lists || [];
globalThis.lists.push(...<?= json_encode($lists, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>);
globalThis.globalParams = <?= json_encode($globalParams, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
</script>

<div id="root-FAE">
    <h3>Eigentümer-Liste</h3>
    <div id="FAE-table"></div>
</div>
