<?php
/** @var League\Plates\Template\Template $this */
$title = $title ?? 'FWHist';
$name  = $name  ?? ($_SESSION['BS_Prim']['titel'] ?? 'Feuerwehr - Archiv');
$logo  = $logo  ?? ($_SESSION['BS_Prim']['logo'] ?? '');
$logoFile = basename(str_replace('\\', '/', (string) ($logo ?? '')));
?>
<!doctype html>
<html lang="de">
<?php 
$basePath = $_SESSION['BS_Prim']['Env']['basePath'] ?? '';
$publicRoot = trim($basePath . '/FWHist', '/');
$publicRoot = $publicRoot !== '' ? '/' . $publicRoot : '';
$logoUrl = $logoFile !== '' ? $publicRoot . '/imgs/' . rawurlencode($logoFile) : '';
# var_dump($_SESSION);
?>
<head>
    <?php $this->insert('partials/head', ['title' => $title]) ?>
    <?php if (!empty($cssBundles)): ?>
        <?php foreach ($cssBundles as $bundle): ?>
            <link rel="stylesheet" href="<?= $publicRoot ?>/css/<?= $bundle ?>.css" />
        <?php endforeach; ?>
    <?php endif; ?>
    <?= $this->section('head') ?>
</head>
<body>
    <a class="skip" href="#content">Zum Inhalt springen</a>

    <?php $this->insert('partials/header', ['title' => $title, 'name' => $name, 'logoUrl' => $logoUrl]) ?>

    <main id="content" class="shell">
        <?= $this->section('content') ?>
    </main>

    <?php $this->insert('partials/footer') ?>

    <?= $this->section('scripts') ?>
</body>
</html>


