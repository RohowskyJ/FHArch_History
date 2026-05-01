<?php
/** @var array $has */
?>
<div class="panel sponsors reveal" aria-label="Sponsoren">
  <div>
    <div class="label">Mit freundlicher Unterstützung</div>
    <div style="font-family:var(--font-display);font-size:18px;margin-top:6px;">Sponsoren</div>
  </div>

  <div class="sponsor-logos">
    <?php if (!empty($has['sponsor_vb'])): ?>
      <a class="logo-box" href="http://www.noemitte.volksbank.at/" target="_blank" rel="noopener" aria-label="Volksbank">
        <img src="imgs/logo_versand_blauer_hintergrund.jpeg" alt="Volksbank Logo" />
      </a>
    <?php else: ?>
      <div class="logo-box" aria-hidden="true">
        <svg width="160" height="40" viewBox="0 0 160 40" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path d="M8 30V10h22c6 0 10 4 10 10s-4 10-10 10H8Z" stroke="rgba(27,26,23,.38)" stroke-width="2"/>
          <path d="M58 30V10h18c7 0 12 4 12 10s-5 10-12 10H58Z" stroke="rgba(27,26,23,.26)" stroke-width="2"/>
          <path d="M102 30l10-20 10 20" stroke="rgba(27,26,23,.30)" stroke-width="2"/>
          <path d="M132 30V10h18" stroke="rgba(27,26,23,.22)" stroke-width="2"/>
        </svg>
      </div>
    <?php endif; ?>

    <?php if (!empty($has['sponsor_jo'])): ?>
      <a class="logo-box" href="https://www.joechlinger-gemuese.at/" target="_blank" rel="noopener" aria-label="Jöchlinger Gemüse">
        <img src="imgs/Logo_Joechlinger.jpg" alt="Jöchlinger Gemüse Logo" />
      </a>
    <?php else: ?>
      <div class="logo-box" aria-hidden="true">
        <svg width="160" height="40" viewBox="0 0 160 40" fill="none" xmlns="http://www.w3.org/2000/svg">
          <circle cx="32" cy="20" r="12" stroke="rgba(197,139,43,.65)" stroke-width="2"/>
          <path d="M54 26c12-18 32-18 44 0" stroke="rgba(31,58,91,.45)" stroke-width="2" />
          <path d="M110 12h40" stroke="rgba(27,26,23,.24)" stroke-width="2"/>
          <path d="M110 20h30" stroke="rgba(27,26,23,.20)" stroke-width="2"/>
          <path d="M110 28h36" stroke="rgba(27,26,23,.16)" stroke-width="2"/>
        </svg>
      </div>
    <?php endif; ?>
  </div>
</div>


