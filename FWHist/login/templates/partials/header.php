<?php
/** @var string $title */
/** @var string $name */
/** @var string $logoUrl */
?>
<div class="brandline" aria-hidden="true"></div>
<div class="topbar">
  <div class="topbar-inner">
    <div class="wordmark" aria-label="Seitentitel">
      <h1><?= $this->e($title) ?></h1>
      <div class="tag"><?= $this->e($name) ?></div>
    </div>

    <?php if ($logoUrl !== ''): ?>
      <img src="<?= $this->e($logoUrl) ?>" alt="<?= $this->e($name) ?>" style="display:block;max-width:180px;max-height:64px;width:auto;height:auto;object-fit:contain;">
    <?php endif; ?>
  </div>
</div>


