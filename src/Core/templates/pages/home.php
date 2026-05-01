<?php
/** @var League\Plates\Template\Template $this */
/** @var string $title */
/** @var string $path2ROOT */
/** @var array $has */
/** @var bool $configOk */
$this->layout('layout', ['title' => $title, 'configOk' => $configOk, 'cssBundles' => $cssBundles]);
?>

<section class="hero reveal" aria-label="Einführung">
  <div class="hero-inner">
    <div class="hero-copy">
      <div class="kicker"><span class="dot" aria-hidden="true"></span>Verein • Sammlung • Forschung</div>
      <h2>Geschichte bewahren. Wissen teilen. Feuerwehrkultur dokumentieren.</h2>
      <p>
        Willkommen im öffentlichen Bereich. Hier finden Sie Verzeichnisse, Links und aktuelle Hinweise rund um
        Feuerwehrhistorik, Sammlungen, Archive und Veranstaltungen.
      </p>

      <div class="hero-actions">
        <a class="btn primary" href="scripts/MitglAnmeld.php" target="MitglAnmeld" rel="noopener">
          Mitglied werden
          <span aria-hidden="true">→</span>
        </a>
        <a class="btn" href="scripts/VF_EM_Edit.php">
          Kontakt
          <span aria-hidden="true">↗</span>
        </a>
        <!-- 
        <a class="btn" href="<?= $this->e($path2ROOT) ?>login/Basis/common/FS_login.php">
         -->
        <a class="btn" href="<?= $this->e($path2ROOT) ?>public/login.php">
          Login intern
          <span aria-hidden="true">↗</span>
        </a>
      </div>

      <?php if (!$configOk): ?>
        <div style="margin-top:16px" class="notice">
          Hinweis: Mindestens eine Konfigurationsdatei fehlt. Prüfe:
          <code>config_d.ini</code>, <code>config_s_<?= $this->e($SI ?? '') ?>.ini</code>.
        </div>
      <?php endif; ?>
    </div>

    <div style="padding: clamp(18px, 3.4vw, 34px); border-left: 1px solid var(--line); background: rgba(255,255,255,.32);">
      <div class="panel" style="background: rgba(255,255,255,.62);">
        <div class="hd">
          <h3>Unsere Ziele</h3>
          <span class="meta">Kurz</span>
        </div>
        <div class="bd">
          <p class="lead">
            Einladen wollen wir alle, die an der Geschichte des Feuerwehrwesens interessiert sind – besonders jene,
            die historisch interessante Fahrzeuge, Geräte und Ausrüstungsgegenstände besitzen.
          </p>
          <p class="lead">
            Dazu zählen Feuerwehren, Feuerwehrmänner und -frauen, Museen und Sammlungen, Privatpersonen und Vereine.
            Wir fördern gezielte Sammeltätigkeit und unterstützen bei Dokumentation und Archivierung.
          </p>
          <ul class="links-inline" aria-label="Ziele Links">
            <li><a href="ziele.php" target="Ziele" rel="noopener">Details zu den Zielen</a></li>
          </ul>
        </div>
      </div>

      <div style="margin-top:14px" class="panel">
        <div class="hd">
          <h3>Aktuelles</h3>
          <a class="meta" href="referat7/index_act.php" target="Arch" rel="noopener">Archiv →</a>
        </div>
        <div class="bd">
          <ul class="navlist" style="gap:8px">
            <li><a href="referat7/Wiki_auch_fuer_Feuerwehren.pdf" target="Regiow" rel="noopener">
              <span>RegioWiki – Regionale Wiki-Seiten für Österreich</span><span class="meta">PDF</span>
            </a></li>
            <li><a href="https://mitglieder.wikimedia.at/Nachrichten/2014-09-29" target="RegioS" rel="noopener">
              <span>Seminar Wikipedia / RegioWiki (LFS Tulln)</span><span class="meta">Web</span>
            </a></li>
            <li><a href="https://regiowiki.at/wiki/Kategorie:Feuerwehr" target="_blank" rel="noopener">
              <span>Feuerwehren in RegioWiki</span><span class="meta">Web</span>
            </a></li>
            <li><a href="referat7/Pflichtabgabe/Pflichtablieferung_BGBLA_2009_II_271.pdf" target="_blank" rel="noopener">
              <span>Pflichtablieferung von Druckwerken – Gesetzestext</span><span class="meta">PDF</span>
            </a></li>
            <li><a href="referat7/BedienerDoku_7.pdf" target="_blank" rel="noopener">
              <span>Bedienerhilfe (Version 20.09.2024)</span><span class="meta">PDF</span>
            </a></li>
            <li><a href="referat7/archord.pdf" target="_blank" rel="noopener">
              <span>Archivordnung</span><span class="meta">PDF</span>
            </a></li>
            <li><a href="referat7/archord.xls" target="_blank" rel="noopener">
              <span>Archivordnung</span><span class="meta">XLS</span>
            </a></li>
          </ul>

          <div class="rule"></div>

          <div style="font-weight:600; margin-bottom:6px;">CIDOC / Museumsdokumentation</div>
          <a href="http://www.museumsbund.at/leitfaeden-und-standards" target="_blank" rel="noopener">
            CIDOC Leitfäden und Standards
          </a>
        </div>
      </div>
    </div>
  </div>
</section>

      <div class="hero-actions">
        <a href="#" class="btn ajax-load" data-page="vorstand">Vorstand anzeigen</a>
        <a href="#" class="btn ajax-load" data-page="impressum">Impressum anzeigen</a>
        <a href="#" class="btn ajax-load" data-page="dsvgo">DSGVO anzeigen</a>
        <a href="#" class="btn ajax-load" data-page="referate">Referate anzeigen</a>
      </div>

      <div id="ajax-content-container" style="border:1px solid var(--line); padding:15px; margin-top:20px; background: rgba(255,255,255,0.9);">
      <em>Hier erscheinen die Inhalte...</em>
     </div>

<section class="grid" aria-label="Hauptinhalt">
  <?php $this->insert('partials/nav', ['has' => $has, 'path2ROOT' => $path2ROOT]) ?>

  <article class="panel reveal" aria-label="Hinweise">
    <div class="hd">
      <h3>Übersicht</h3>
      <span class="meta">Schnellzugriff</span>
    </div>
    <div class="bd">
      <p class="lead">
        Nutzen Sie die Navigation links, um in die öffentlichen Listen (Museen, Archive, Termine) zu wechseln.
        Der interne Bereich ist über den Login erreichbar.
      </p>

      <div class="notice">
        Tipp: Wenn du später weitere Seiten umstellst, lege für jede Seite eine Datei unter
        <code>templates/pages/</code> an und rendere sie im jeweiligen Controller.
      </div>

      <div class="rule"></div>

      <div class="section-title">Struktur (Plates)</div>
      <p class="lead" style="margin-bottom:0">
        <strong>layout.php</strong> (Rahmen) → <strong>partials/*</strong> (Header/Nav/Footer) → <strong>pages/*</strong> (Seiteninhalt).
        Dadurch bleibt die PHP-Logik im Controller und die Ausgabe in Templates.
      </p>
    </div>
  </article>
</section>

<?php $this->insert('partials/sponsors', ['has' => $has]) ?>

<?php $this->start('scripts') ?>
<script>
(() => {
  // Staggered reveal (Load + Scroll)
  const els = Array.from(document.querySelectorAll('.reveal'));
  els.forEach((el, i) => { el.style.transitionDelay = (i * 70) + 'ms'; });

  const io = new IntersectionObserver((entries) => {
    entries.forEach(e => {
      if (e.isIntersecting) {
        e.target.classList.add('in');
        io.unobserve(e.target);
      }
    });
  }, { threshold: 0.12 });

  els.forEach(el => io.observe(el));
})();

(() => {
  const buttons = document.querySelectorAll('.ajax-load');
  const container = document.getElementById('ajax-content-container');

  buttons.forEach(btn => {
    btn.addEventListener('click', (e) => {
      e.preventDefault();
      const page = btn.getAttribute('data-page');
      if (!page) return;
console.log('data Page ', page);
      container.innerHTML = '<p>Lade Inhalte...</p>';

      fetch(`ajax_content.php?seite=${encodeURIComponent(page)}`)
        .then(response => {
          if (!response.ok) throw new Error('Netzwerkfehler');
          return response.text();
        })
        .then(html => {
          container.innerHTML = html;
        })
        .catch(err => {
          container.innerHTML = `<p style="color:red;">Fehler beim Laden: ${err.message}</p>`;
        });
    });
  });
})();
</script>
<?php $this->end() ?>
