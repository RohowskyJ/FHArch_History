<?php
/** @var string $title */
?>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<meta name="color-scheme" content="light" />
<title><?= $this->e($title) ?></title>
<!-- - 
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,600;9..144,750&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
 -->
<style>
:root{
  --paper:#fbf6ee;
  --paper-2:#f4ecdf;
  --ink:#1b1a17;
  --muted:#4b463f;
  --line: rgba(27,26,23,.18);
  --shadow: 0 18px 50px rgba(27,26,23,.12);

  --brand:#8a1f1f;     /* tiefes Feuerwehr-Rot */
  --brand-2:#c58b2b;   /* Messing/Gold */
  --brand-3:#1f3a5b;   /* Stahlblau */
  --ok:#1f6f4a;
  --warn:#8a1f1f;

  --radius: 18px;
  --radius2: 26px;

  --font-display: "Fraunces", ui-serif, Georgia, serif;
  --font-body: "Inter", ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, "Helvetica Neue", sans-serif;
}

*{ box-sizing:border-box; }
html,body{ height:100%; }
body{
  margin:0;
  font-family:var(--font-body);
  color:var(--ink);
  background:
    radial-gradient(1200px 700px at 10% -10%, rgba(197,139,43,.22), transparent 60%),
    radial-gradient(900px 600px at 110% 10%, rgba(31,58,91,.18), transparent 55%),
    linear-gradient(180deg, var(--paper), var(--paper-2));
}

a{ color:inherit; text-decoration-thickness: .08em; text-underline-offset: .18em; }
a:hover{ text-decoration-thickness: .12em; }

.skip{
  position:absolute; left:-999px; top:auto; width:1px; height:1px; overflow:hidden;
}
.skip:focus{
  left:16px; top:16px; width:auto; height:auto; padding:10px 12px;
  background:#fff; border:1px solid var(--line); border-radius:12px;
  z-index:999;
}

.shell{
  width:min(1100px, calc(100% - 32px));
  margin-inline:auto;
  padding: 18px 0 56px;
}

.brandline{
  height: 10px;
  background:
    linear-gradient(90deg, var(--brand), var(--brand-2), var(--brand-3));
}

.topbar{
  position:sticky; top:0; z-index:50;
  backdrop-filter: blur(10px);
  background: rgba(251,246,238,.72);
  border-bottom: 1px solid var(--line);
}

.topbar-inner{
  width:min(1100px, calc(100% - 32px));
  margin-inline:auto;
  display:flex; align-items:center; justify-content:space-between;
  gap: 16px;
  padding: 14px 0;
}

.wordmark{
  display:flex; align-items:baseline; gap:12px;
}
.wordmark h1{
  margin:0;
  font-family:var(--font-display);
  font-size: clamp(18px, 2.3vw, 24px);
  letter-spacing: .02em;
}
.wordmark .tag{
  font-size: 12.5px;
  color: var(--muted);
  letter-spacing: .08em;
  text-transform: uppercase;
}

.pill{
  display:inline-flex; align-items:center; gap:10px;
  padding: 8px 12px;
  border: 1px solid var(--line);
  border-radius: 999px;
  background: rgba(255,255,255,.62);
  box-shadow: 0 10px 30px rgba(27,26,23,.06);
  font-size: 13px;
  color: var(--muted);
}
.pill strong{ color: var(--ink); font-weight:600; }

.hero{
  margin-top: 18px;
  border: 1px solid var(--line);
  border-radius: var(--radius2);
  overflow:hidden;
  box-shadow: var(--shadow);
  background:
    linear-gradient(135deg, rgba(138,31,31,.10), rgba(197,139,43,.06) 45%, rgba(31,58,91,.08));
}

.hero-inner{
  display:grid;
  grid-template-columns: 1.05fr .95fr;
  gap: 0;
}
@media (max-width: 900px){
  .hero-inner{ grid-template-columns: 1fr; }
}

.hero-copy{
  padding: clamp(18px, 3.4vw, 34px);
}
.kicker{
  display:flex; align-items:center; gap:10px;
  font-size: 12.5px;
  text-transform: uppercase;
  letter-spacing:.12em;
  color: var(--muted);
}
.kicker .dot{
  width:10px; height:10px; border-radius:99px;
  background: var(--brand);
  box-shadow: 0 0 0 5px rgba(138,31,31,.12);
}
.hero h2{
  margin: 12px 0 8px;
  font-family: var(--font-display);
  font-size: clamp(30px, 4.3vw, 48px);
  line-height: 1.04;
}
.hero p{
  margin: 12px 0 0;
  font-size: 16px;
  line-height: 1.65;
  color: var(--muted);
  max-width: 58ch;
}

.hero-actions{
  margin-top: 18px;
  display:flex; flex-wrap:wrap;
  gap: 12px;
}

.btn{
  display:inline-flex; align-items:center; justify-content:center;
  gap:10px;
  padding: 12px 14px;
  border-radius: 14px;
  border: 1px solid var(--line);
  background: rgba(255,255,255,.72);
  box-shadow: 0 12px 26px rgba(27,26,23,.08);
  font-weight: 600;
  text-decoration:none;
  transition: transform .18s ease, box-shadow .18s ease, background .18s ease;
}
.btn:hover{ transform: translateY(-1px); box-shadow: 0 18px 40px rgba(27,26,23,.12); }
.btn:active{ transform: translateY(0px) scale(.99); }

.btn.primary{
  background: linear-gradient(180deg, rgba(138,31,31,.96), rgba(112,18,18,.98));
  border-color: rgba(0,0,0,.12);
  color: #fff;
}
.btn.primary:hover{ filter: brightness(1.03); }

.grid{
  margin-top: 18px;
  display:grid;
  grid-template-columns: 320px 1fr;
  gap: 18px;
}
@media (max-width: 900px){
  .grid{ grid-template-columns: 1fr; }
}

.panel{
  border: 1px solid var(--line);
  border-radius: var(--radius);
  background: rgba(255,255,255,.58);
  backdrop-filter: blur(10px);
  box-shadow: 0 12px 34px rgba(27,26,23,.08);
  overflow:hidden;
}

.panel .hd{
  padding: 14px 16px;
  border-bottom: 1px solid var(--line);
  display:flex; align-items:center; justify-content:space-between;
  gap: 12px;
}
.panel .hd h3{
  margin:0;
  font-family: var(--font-display);
  font-size: 18px;
  letter-spacing:.01em;
}
.panel .bd{ padding: 14px 16px 16px; }

.navlist{
  list-style:none; padding:0; margin:0;
  display:flex; flex-direction:column;
  gap: 6px;
}
.navlist a{
  display:flex; align-items:center; justify-content:space-between;
  gap: 12px;
  padding: 10px 10px;
  border-radius: 12px;
  text-decoration:none;
  border: 1px solid transparent;
  transition: background .16s ease, transform .16s ease, border-color .16s ease;
}
.navlist a:hover{
  background: rgba(197,139,43,.10);
  border-color: rgba(197,139,43,.28);
  transform: translateX(2px);
}
.navlist .meta{
  font-size: 12px;
  color: var(--muted);
  letter-spacing:.08em;
  text-transform: uppercase;
}

.rule{
  height:1px; background: var(--line);
  margin: 14px 0;
}

.notice{
  padding: 10px 12px;
  border-radius: 14px;
  border: 1px dashed rgba(138,31,31,.35);
  background: rgba(138,31,31,.06);
  color: #3a1a1a;
  font-size: 13.5px;
  line-height: 1.45;
}

.section-title{
  margin: 0 0 8px;
  font-family: var(--font-display);
  font-size: 22px;
}
.lead{
  margin: 0 0 10px;
  color: var(--muted);
  line-height: 1.7;
}

.links-inline{
  display:flex; flex-wrap:wrap; gap: 10px 14px;
  padding: 0; margin: 10px 0 0;
  list-style:none;
}
.links-inline a{
  padding: 8px 10px;
  border-radius: 999px;
  border: 1px solid var(--line);
  background: rgba(255,255,255,.55);
  text-decoration:none;
  transition: transform .16s ease, background .16s ease;
}
.links-inline a:hover{ transform: translateY(-1px); background: rgba(255,255,255,.78); }

.sponsors{
  margin-top: 18px;
  padding: 16px;
  display:flex;
  align-items:center;
  justify-content:space-between;
  gap: 16px;
  flex-wrap:wrap;
}
.sponsors .label{
  font-size: 12.5px;
  color: var(--muted);
  letter-spacing:.08em;
  text-transform: uppercase;
}
.sponsor-logos{
  display:flex; align-items:center; gap: 16px;
  flex-wrap:wrap;
}
.logo-box{
  width: 190px;
  height: 64px;
  border-radius: 16px;
  border: 1px solid var(--line);
  background:
    linear-gradient(180deg, rgba(255,255,255,.72), rgba(255,255,255,.45));
  display:flex; align-items:center; justify-content:center;
  padding: 10px 12px;
  overflow:hidden;
}
.logo-box img{
  max-width: 100%;
  max-height: 100%;
  display:block;
}

.footer{
  border-top: 1px solid var(--line);
  background: rgba(251,246,238,.72);
}
.footer-inner{
  width:min(1100px, calc(100% - 32px));
  margin-inline:auto;
  padding: 18px 0 26px;
  display:flex;
  flex-wrap:wrap;
  align-items:flex-start;
  justify-content:space-between;
  gap: 14px;
  color: var(--muted);
  font-size: 13.5px;
}
.footer a{ color: var(--muted); }
.footer .fine{
  max-width: 70ch;
  line-height: 1.6;
}

.reveal{
  opacity:0;
  transform: translateY(10px);
}
.reveal.in{
  opacity:1;
  transform: translateY(0);
  transition: opacity .5s ease, transform .5s ease;
}

@media print{
  .topbar, .hero-actions, .sponsors, .footer{ display:none !important; }
  body{ background:#fff; }
  .panel, .hero{ box-shadow:none; }
}
</style>

