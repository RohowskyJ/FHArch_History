<?php

/**
 * Ehrungen des aktuellen Mitglieds
 */

$memberId = (int) ($neu['mi_id'] ?? 0);
$_SESSION[$module]['mi_id'] = $memberId;
$canEdit = $guard->canWriteMemberData();
$newRecordUrl = $canEdit && $memberId > 0
    ? 'MitglEhrgEdit.php?ID=0&mi_id=' . $memberId
    : '';

$lists = [
    [
        'id' => 'MIE',
        'container' => 'MIE-table',
        'apiUrl' => $basePath . '/FWHist/login/Services/API/ListHostAPI.php',
        'title' => 'Auszeichnungs-Details',
        'editScript' => $newRecordUrl,
        'actionScript' => $canEdit ? 'MitglEhrgEdit.php' : '',
        'actionLabel' => 'Bearbeiten',
        'newLabel' => $canEdit ? 'Neue Ehrung anlegen' : '',
        'showActionColumn' => $canEdit,
        'filterTextFields' => ['me_ehrung', 'me_begruendg'],
        'paginationSizes' => [10, 25, 50, 100],
    ],
];

$globalParams = [
    'owner' => 'Mitglieder-Ehrungen',
    'user' => $_SESSION['BS_Prim']['BE']['be_id'] ?? null,
    'project' => 'Alle',
    'sel_id' => $memberId,
    'edit_Protect' => !$canEdit,
];

?>
<script>
globalThis.lists = globalThis.lists || [];
globalThis.lists.push(...<?= json_encode($lists, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>);
globalThis.globalParams = <?= json_encode($globalParams, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
</script>

<div id="root-MIE">
    <h3>Auszeichnungs-Details</h3>
    <div id="MIE-table"></div>
</div>

<script src="<?= $basePath ?>/FWHist/js/tabulator-6.5.2/tabulator.min.js"></script>
<script src="<?= $basePath ?>/FWHist/js/list-tables.js"></script>
