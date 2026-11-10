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

<div class="Menu-Header">Archiv- Verwaltung</div>

<div class="Menu-Separator">Archivalien</div>
<div class="Menu-Line">
    <b>Archivalien- Verwaltung: Einordnung entsprechend der Archivordnug, Eingeben der Daten einer Archivalie (Dokument), Eingabe der Schlagworte zum Suchen </b>
</div>
<div class="Menu-Line">
   <a href='ArchivList.php?ID=Abf' target='Archiv'>Archivalien- Verwaltung </a>
</div>
<div class="Menu-Line">
   Die Anzahl der digitalisierten Objekte in den jeweiligen Archiv- Teilen anzeigen.
</div>
<div class="Menu-Line">
   <a href='CntArchDocList.php' target='Arch-Docs'>Archiv- Dokumente Anzahl je Arch_Ordnung </a>
</div>
   
<div class="Menu-Separator">Archivordnung, erweitert</div>
<div class="Menu-Line">
    <b>Die Grundlegende Archivordnung ist im OBFV- Feuerwehrgeschichte definiert und hat zwei Ebenen.</b>
    </div>
<div class="Menu-Line">
     Die erweiterte Archivordung nutzt die Definierte Ordnung und fügt vier Ebenen hinzu, die in der Basis zu grob definiert sind,
     und für die genauere Suche hilfreich sind.
</div>
<div class="Menu-Line">
    <a href='ArchOrdList.php?' target='ArchivOrd'>Erweiterung der Archiv- Ordnung </a>
</div>
  
<div class="Menu-Separator">Massen- Hochladen von Dokumenten</div>
<div class="Menu-Line">
    <b>Hier können die die digitalisierten Dokumente massenweise (je Gruppe innerhalb der Archivordnung) geladen und die Tabelle für die 
Dokumente in den jeweiligen Archivordnung werden erstellt. Die Beschreibungen müssen dann in der Archivalien-Verwaltung eingegeben werden.</b>
</div>
<div class="Menu-Line">
    <a href='MassUp.php?sk=$sk&typ=Arch' target='Upl_AR'> Archivalien- Massen- Upload (PDF- Dateien, Fotos)</a>
</div>


