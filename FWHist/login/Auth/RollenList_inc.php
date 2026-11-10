<?php

declare(strict_types=1);

/**
 * Liste "Zugeordnete Rollen" im Benutzer-Formular (BenEdit_ph0_inc.php).
 * Tabulator und list-tables.js werden dort einmal für alle Listen eingebunden.
 */

$roleUserId = (int)($neu['be_id'] ?? 0);

$canManage = $guard->canManageAccess();
$act = $guard->listActionConfig($canManage);   // Action-Spalte: "Bearbeiten" mit Recht, sonst "Anzeigen"
?>

<h4>Liste der auszuübenden Rollen</h4>
<?php if ($roleUserId < 1): ?>
    <p>Speichern Sie zuerst den Benutzer, bevor Sie Rollen zuordnen können.</p>
<?php else:
    $newRoleUrl = $canManage ? 'RollenEdit.php?' . http_build_query([
        'ID' => 0,
        'beId' => $roleUserId,
    ]) : '';
    $roleListConfig = [
        'id' => 'RO',
        'container' => 'RO-table',
        'apiUrl' => $basePath . '/FWHist/login/Services/API/ListHostAPI.php',
        'title' => 'Zugeordnete Rollen',
        'editScript' => $newRoleUrl,
        'actionScript' => 'RollenEdit.php',
        'actionLabel' => $act['actionLabel'],
        'showActionColumn' => $act['showActionColumn'],
        'newLabel' => 'Neue Rolle anlegen',
        'filterSelectFields' => ['fr_aktiv'],
        'filterTextFields' => ['fl_beschreibung', 'fl_modules'],
        'paginationSizes' => [10, 25, 50],
    ];
    $roleGlobalParams = [
        'owner' => 'Benutzerrollen',
        'user' => $guard->userId(),
        'project' => null,
        'sel_id' => $roleUserId,
        'edit_Protect' => $act['editProtect'],
    ];
?>
<script>
globalThis.lists = [
    ...(Array.isArray(globalThis.lists) ? globalThis.lists : []),
    <?= json_encode($roleListConfig, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>
];
globalThis.globalParams = {
    ...(globalThis.globalParams || {}),
    ...<?= json_encode($roleGlobalParams, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>
};
</script>

<div id="root-RO">
    <div id="RO-table"></div>
</div>
<?php endif; ?>
