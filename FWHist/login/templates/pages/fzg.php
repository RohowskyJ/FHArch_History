<?php
/**
 * Menu für die Fahrzeug- Geräte-  Verwaltung
 *
 */

$this->layout('layout', ['title' => $title, 'systemBasePath' => $systemBasePath, 'cssBundles' => $cssBundles]) ?>

<div class="Menu-Header">Fahrzeuge und Geräte</div>

<div class="Menu-Separator">mit Muskelkraft betriebene Geräte</div>

<div class="Menu-Line">
    Beschreibungen der mit Muskelkraft betriebenen Fahrzeuge und Geräte.
</div>
<div class="Menu-Line">
    <a href='../FahrzeugeGeraete/FzMList.php?ID=Abf' target='Muskel-FzgGer'>Muskelgezogene Fahrzeuge und muskelbetriebene Geräte - Wartung </a>
</div>
<div class="Menu-Line">
    <a href='../FahrzeugeGeraete/FzMKatalog.php' target='Muskel-Katalog'>Katalog der Muskelgezogenen Fahrzeug- und Geräte</a>
</div>
<div class="Menu-Line">
    <a href='../../Oeffentlichkeitsarbeit/DokuList.php?sel_thema=1' target='M-Verwaltung'>Vereins- Dokumentation zu Muskelbewegtem</a>
</div>

<div class="Menu-Separator">mit Motorkraft betriebene Geräte</div>

<div class="Menu-Line">
   Beschreibungen der mit Motorkraft bewegten Fahrzeuge und für Motorfahrzeuge konstruierten Anhänger, Geräte.
</div>
<div class="Menu-Line">
    <a href='../FahrzeugeGeraete/FzAList.php?ID=Abf' target='Motor_FzgGer'>Motorisierte Fahrzeug- und Geräte Wartung</a>
</div>
<div class="Menu-Line">
    <a href='../FahrzeugeGeraete/FzAKatalog.php' target='Motor-Katalog'>Automobil- Fahrzeugkatalog</a>
</div>
<div class="Menu-Line">
    <a href='../Oeffentlichkeitsarbeit/DokuList.php?sel_thema=2' target='M-Verwaltung'>Vereins- Dokumentation zu Fahrzeugen</a>
</div>

<div class="Menu-Separator">Beenden </div>

<div class="Menu-Line">
    <a href="../../logout.php">LOGOFF (HomePage)</a>
</div>
