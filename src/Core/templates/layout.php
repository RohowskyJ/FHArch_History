<?php
/** @var League\Plates\Template\Template $this */
$title = $title ?? 'VFH';
?>
<!doctype html>
<html lang="de">
<?php 
$basePath = $_SESSION['BS_Prim']['Env']['basePath'] ?? '';
# var_dump($_SESSION);
?>
 <?php if (!empty($cssBundles)): ?>
        <?php foreach ($cssBundles as $bundle): ?>
            <link rel="stylesheet" href="<?= $basePath ?>/public/css/<?= $bundle ?>.css" />
        <?php endforeach; ?>
 <?php endif; ?>
<head>
    <?php $this->insert('partials/head', ['title' => $title]) ?>
    <?= $this->section('head') ?>
</head>
<body>
    <a class="skip" href="#content">Zum Inhalt springen</a>

    <?php $this->insert('partials/header', ['title' => $title, 'configOk' => $configOk ?? true]) ?>

    <main id="content" class="shell">
        <?= $this->section('content') ?>
    </main>

    <?php $this->insert('partials/footer') ?>

    <?= $this->section('scripts') ?>
</body>
</html>


