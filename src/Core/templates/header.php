<!DOCTYPE html>
<html lang="de" style="overflow:auto;">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title><?= htmlspecialchars($this->e($title)) ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Feuerwehrhistoriker Dokumentationen - Archiv, Inventar, Beschreibungen, Kataloge, ...">
    <meta name="copyright" content="Ing. Josef Rohowsky 2020-2026">
    <meta name="robots" content="noindex, nofollow">

    <?php 
    $path2ROOT = "../";
    $logo = "logo.jpg";
    if ($logo): ?>
    
        <link rel="icon" type="image/x-icon" href="<?= $this->e($path2ROOT) ?>public/imgs/<?= $this->e($logo) ?>">
    <?php endif; ?>

    <style>
        body {
            max-width: <?= htmlspecialchars($width) ?>;
            margin: 0 auto;
            font-family: Arial, sans-serif;
            background: #f9f9f9;
            color: #333;
        }
        .page-header {
            max-width: 1200px;
            margin: 1rem auto;
            padding: 1rem 2rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            background: white;
            border-bottom: 3px solid #007acc;
            flex-wrap: wrap;
        }
        .logo-container img {
            max-width: 100px;
            height: auto;
            border: 3px solid lightblue;
            display: block;
        }
        .text-container {
            flex-grow: 1;
        }
        .org-name {
            font-size: 1.2rem;
            font-weight: bold;
            color: #007acc;
        }
        .page-title {
            font-size: 1.5rem;
            font-weight: bold;
            margin-top: 0.25rem;
        }
        @media (max-width: 600px) {
            .page-header {
                flex-direction: column;
                align-items: flex-start;
            }
            .logo-container {
                margin-bottom: 1rem;
            }
        }
    </style>
<!-- 
    <?= $head ?>
 -->
</head>
<body>

<?php 
    $orgName = "Organisationsname aber noch statisch";
    if ($type === '1P'): ?>
    <div class="page-header">
        <div class="logo-container">
            <img src="<?= $this->e($path2ROOT) ?>public/imgs/header_1p.png" alt="Header Image">
        </div>
        <div class="text-container">
            <div class="org-name"><?= htmlspecialchars($orgName) ?></div>
            <div class="page-title"><?= htmlspecialchars($title) ?></div>
        </div>
    </div>
<?php elseif ($type === 'Form'): ?>
    <div class="page-header">
        <?php if ($logo): ?>
            <div class="logo-container">
                <img src="<?= $this->e($path2ROOT) ?>public/imgs/<?= $this->e($logo) ?>" alt="Logo">
            </div>
        <?php endif; ?>
        <div class="text-container">
            <div class="org-name"><?= htmlspecialchars($orgName) ?></div>
            <div class="page-title"><?= htmlspecialchars($title) ?></div>
        </div>
    </div>
    <fieldset>
<?php else: /* Listen-Seite, kein Logo */ ?>
    <fieldset>
<?php endif; ?>
b) templates/form.php
