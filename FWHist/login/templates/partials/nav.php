<?php
/** @var array $has */
/** @var string $systemBasePath */

# var_dump($has);
?>

<div class="panel reveal">
  <div class="hd">
    <h3>Navigation</h3>
    <span class="meta">Öffentlich</span>
  </div>
  <div class="bd">
    <ul class="navlist">
      <?php if (!empty($has['museen'])): ?>
        <li><a href="<?= $this->e($systemBasePath) ?>login/Modules/Oeffentlichkeitsarbeit/MuseenList.php" target="M_Links" rel="noopener">
          <span>Museen & Sammlungen</span><span class="meta">Liste</span>
        </a></li>
      <?php endif; ?>

      <?php if (!empty($has['archive'])): ?>
        <li><a href="<?= $this->e($systemBasePath) ?>login/Modules/Oeffentlichkeitsarbeit/ArLinkList.php" target="A_Links" rel="noopener">
          <span>Archive & Bibliotheken</span><span class="meta">Links</span>
        </a></li>
      <?php endif; ?>

      <?php if (!empty($has['termine'])): ?>
        <li><a href="<?= $this->e($systemBasePath) ?>login/Modules/Oeffentlichkeitsarbeit/TerminList.php" target="Veranstalt" rel="noopener">
          <span>Veranstaltungen</span><span class="meta">Kalender</span>
        </a></li>
      <?php endif; ?>

      <?php if (!empty($has['presse'])): ?>
        <li><a href="<?= $this->e($systemBasePath) ?>login/Modules/Oeffentlichkeitsarbeit/PresseList.php" target="Presse" rel="noopener">
          <span>Presse-Information</span><span class="meta">Spiegel</span>
        </a></li>
      <?php endif; ?>

      <?php if (!empty($has['buch'])): ?>
        <li><a href="<?= $this->e($systemBasePath) ?>login/Modules/Oeffentlichkeitsarbeit/BuchList.php" target="Buch" rel="noopener">
          <span>Buch-Besprechungen</span><span class="meta">Rezensionen</span>
        </a></li>
      <?php endif; ?>

      <?php if (!empty($has['markt'])): ?>
        <li><a href="<?= $this->e($systemBasePath) ?>login/Modules/Oeffentlichkeitsarbeit/MarktplList.php" target="Marktpl" rel="noopener">
          <span>Marktplatz</span><span class="meta">Anzeigen</span>
        </a></li>
      <?php endif; ?>
      
      <?php if (!empty($has['aerm'])): ?>
        <li><a href="<?= $this->e($systemBasePath) ?>login/Modules/Psa/OrtsAuswList.php?proj=AERM" target="Aerm" rel="noopener">
          <span>Ärmelabzeichen</span><span class="meta">Anzeigen</span>
        </a></li>
      <?php endif; ?>
    </ul>
  </div>
</div>
