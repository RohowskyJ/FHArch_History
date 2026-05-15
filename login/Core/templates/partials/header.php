<?php
/** @var string $title */
/** @var bool $configOk */
?>
<div class="brandline" aria-hidden="true"></div>
<div class="topbar">
  <div class="topbar-inner">
    <div class="wordmark" aria-label="Seitentitel">
      <h1><?= $this->e($title) ?></h1>
      <div class="tag">Verein für Feuerwehrhistorik</div>
    </div>

    <div class="pill" role="status" aria-live="polite">
      <span style="width:10px;height:10px;border-radius:99px;background:<?= ($configOk ? 'var(--ok)' : 'var(--warn)') ?>;box-shadow:0 0 0 5px <?= ($configOk ? 'rgba(31,111,74,.14)' : 'rgba(138,31,31,.14)') ?>;"></span>
      <strong><?= $configOk ? 'System bereit' : 'Config fehlt' ?></strong>
      <span><?= $configOk ? 'öffentlicher Einstieg' : 'bitte Installation prüfen' ?></span>
    </div>
  </div>
</div>


