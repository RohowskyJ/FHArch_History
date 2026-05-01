<?php
/** @var array $has */
/** @var string $path2ROOT */
var_dump($has);
?>

<div class="panel reveal">
  <div class="hd">
    <h3>Navigation</h3>
    <span class="meta">Öffentlich</span>
  </div>
  <div class="bd">
    <ul class="navlist">
      <?php if (!empty($has['museen'])): ?>
        <li><a href="<?= $this->e($path2ROOT) ?>src/Core/Modules/Oeffentlichkeitsarbeit/MuseenList.php" target="M_Links" rel="noopener">
          <span>Museen & Sammlungen</span><span class="meta">Liste</span>
        </a></li>
      <?php endif; ?>

      <?php if (!empty($has['archive'])): ?>
        <li><a href="<?= $this->e($path2ROOT) ?>src/Core/Modules/Oeffentlichkeitsarbeit/ArLinkList.php" target="A_Links" rel="noopener">
          <span>Archive & Bibliotheken</span><span class="meta">Links</span>
        </a></li>
      <?php endif; ?>

      <?php if (!empty($has['termine'])): ?>
        <li><a href="<?= $this->e($path2ROOT) ?>src/Core/Modules/Oeffentlichkeitsarbeit/TerminList.php" target="Veranstalt" rel="noopener">
          <span>Veranstaltungen</span><span class="meta">Kalender</span>
        </a></li>
      <?php endif; ?>

      <?php if (!empty($has['presse'])): ?>
        <li><a href="<?= $this->e($path2ROOT) ?>src/Core/Modules/Oeffentlichkeitsarbeit/PresseList.php" target="Presse" rel="noopener">
          <span>Presse-Information</span><span class="meta">Spiegel</span>
        </a></li>
      <?php endif; ?>

      <?php if (!empty($has['buch'])): ?>
        <li><a href="<?= $this->e($path2ROOT) ?>src/Core/Modules/Oeffentlichkeitsarbeit/BuchList.php" target="Buch" rel="noopener">
          <span>Buch-Besprechungen</span><span class="meta">Rezensionen</span>
        </a></li>
      <?php endif; ?>

      <?php if (!empty($has['markt'])): ?>
        <li><a href="<?= $this->e($path2ROOT) ?>src/Core/Modules/Oeffentlichkeitsarbeit/AzeigerList.php" target="Marktpl" rel="noopener">
          <span>Marktplatz</span><span class="meta">Anzeigen</span>
        </a></li>
      <?php endif; ?>
    </ul>

    <div class="rule"></div>

    <ul class="navlist">
      <li><a href="scripts/VF_EM_Edit.php"><span>Kontakt (E‑Mail)</span><span class="meta">Formular</span></a></li>
      <li><a href="impress.php"><span>Impressum</span><span class="meta">Recht</span></a></li>
      <li><a href="scripts/VS_M_Anmeld.php" target="MitglAnmeld" rel="noopener"><span>Mitglied werden</span><span class="meta">Anmeldung</span></a></li>
      <li><a href="<?= $this->e($path2ROOT) ?>login/Basis/common/FS_login.php"><span>Login intern</span><span class="meta">Bereich</span></a></li>
      <li><a href="DSGVO/Datenschutz_allg_Beschreibg.php" target="DSVGO" rel="noopener"><span>DSGVO</span><span class="meta">Info</span></a></li>
      <li><a href="referate.php" target="referate" rel="noopener"><span>Referate</span><span class="meta">Themen</span></a></li>
      <li><a href="Vorstand.php" target="Vorstand" rel="noopener"><span>Vorstand</span><span class="meta">Personen</span></a></li>
    </ul>
  </div>
</div>
