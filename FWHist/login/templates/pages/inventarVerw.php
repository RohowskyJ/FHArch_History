<?php
/** 
 * Öffenlichkeitsarbeit, Menu
 * 
 */

$this->layout('layout', ['title' => $title, 'path2ROOT' => $path2ROOT, 'cssBundles' => $cssBundles , 'has' => $has ] ) ;

if (!empty($debug)) {
    var_dump($has);
}
?>

<div class="Menu-Header">Inventar- Verwaltung</div>

<div class="Menu-Separator">Inventar</div>
<div class="Menu-Line">
    <b>Hier wird die Pflege der Daten durchgeführt. Die Daten werden unter dem Eigentümer abgelegt und bestehen aus der Inventarnummer,
dem entsprechenden Referat, die Epoche der Entstehung/Nutzung, Suchbegriff, Hersteller, genauere Beschreibung,
Daten über einen Leiher und den Ort (Raum, Platz) wo sich der Gegenstand zur Zeit befindet. </b>
</div>
<div class="Menu-Line">
   <a href='InventarList.php?ID=Abf' target='Invent'>Inventar- Verwaltung </a>
</div>


