<?php

declare(strict_types=1);

/**
 * Liste "Zugeordnete Mandanten" (Mandanten-Erlaubnisse) im Benutzer-Formular (BenEdit_ph0_inc.php).
 * Tabulator und list-tables.js werden dort einmal für alle Listen eingebunden.
 */

$roleUserId = (int)($neu['be_id'] ?? 0);

$canManage = $guard->canManageAccess();
$act = $guard->listActionConfig($canManage);   // Action-Spalte: "Bearbeiten" mit Recht, sonst "Anzeigen"
?>

<h4>Liste der erlaubten Mandanten</h4>
<?php if ($roleUserId < 1): ?>
    <p>Speichern Sie zuerst den Benutzer, bevor Sie Mandanten zuordnen können.</p>
<?php else:
    $newMandUrl = $canManage ? 'MandErlEdit.php?' . http_build_query([
        'ID' => 0,
        'beId' => $roleUserId,
    ]) : '';
    $mandListConfig = [
        'id' => 'ROM',
        'container' => 'ROM-table',
        'apiUrl' => $basePath . '/FWHist/login/Services/API/ListHostAPI.php',
        'title' => 'Zugeordnete Mandanten',
        'editScript' => $newMandUrl,
        'actionScript' => 'MandErlEdit.php',
        'actionLabel' => $act['actionLabel'],
        'showActionColumn' => $act['showActionColumn'],
        'newLabel' => 'Neuen Mandanten zuordnen',
        'filterSelectFields' => ['fm_aktiv'],
        'filterTextFields' => ['mandant'],
        'paginationSizes' => [10, 25, 50],
    ];
    $mandGlobalParams = [
        'owner' => 'Mandanten- Erlaubnisse für Edit',
        'user' => $guard->userId(),
        'project' => null,
        'sel_id' => $roleUserId,
        'edit_Protect' => $act['editProtect'],
    ];
?>
<script>
globalThis.lists = [
    ...(Array.isArray(globalThis.lists) ? globalThis.lists : []),
    <?= json_encode($mandListConfig, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>
];
globalThis.globalParams = {
    ...(globalThis.globalParams || {}),
    ...<?= json_encode($mandGlobalParams, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>
};
</script>

<div id="root-ROM">
    <div id="ROM-table"></div>
</div>
<?php endif; ?>
