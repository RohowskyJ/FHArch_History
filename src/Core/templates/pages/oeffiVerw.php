<?php
/** 
 * Öffenlichkeitsarbeit, Menu
 * 
 */

$this->layout('layout', ['title' => $title, 'path2ROOT' => $path2ROOT, 'cssBundles' => $cssBundles , 'has' => $has ] ) ;

var_dump($has);
?>

<div class="Menu-Header">Öffentlichkeitsarbeit</div>

<div class="Menu-Separator">Archiv- und Bibliotheks- Links</div>
<div class="Menu-Line">
    <b>Pflege der öffentlichen Links zu Bibliotkeken und Archiven</b>
</div>
<div class="Menu-Line">
   <a href='ArLinkList.php?' target='Archive'>Archiv- und Bibliotheks- Links</a>
</div>
   
<div class="Menu-Separator">Marktplatz, Biete/Suche</div>
<div class="Menu-Line">
    <b>Jedes Mitglied hier seine Wünsche und freies Material anbieten/suchen</b>
</div>
<div class="Menu-Line">
    <a href='MarktplList.php' target='Biete-Suche'>Biete- /Suche- Marktplatz, Adminstrativer Teil</a>
</div>
  
<div class="Menu-Separator">Buch Rezensionen</div>
<div class="Menu-Line">
    <b>Pflege der Buch Rezensionen</b>
</div>
<div class="Menu-Line">
    <a href='BuchList.php' target='Bücher'>Buch- Rezensionen, Verwalten, Redigieren, Freischalten</a>
</div>

<div class="Menu-Separator">Vereins- Dokumentationen zum herunterladen</div>
<div class="Menu-Line">
    <b>ier werden die verschiedenen im Verein erstellten und Vorgetragenen Dokumentationen ins Netz gestellt,
          und können heruntergeladen und dürfen für Zwecke der Feuerwehrgeschichte verwendet werden</b>
</div>
<div class="Menu-Line">
    <a href='DokuList.php' target='Doku'>Dokumentationen zum herunterladen</a>
</div>

    
<div class="Menu-Separator">Museumsdaten warten</div>
<div class="Menu-Line">
    <b>Pflege der Museumsliste </b>
</div>
<div class="Menu-Line">
   <a href='MuseenList.php' target='Museen'>Museumsdatenwartung</a>
</div>
 
<div class="Menu-Separator">Presse- Ausschnitte</div>
<div class="Menu-Line">
    <b>Pflege der in der Presse veröffentlichen Artikel</b>
</div>
<div class="Menu-Line">
    <a href='PresseList.php' target='Presse'>Presse-Informationen verwalten</a>
</div>

<div class="Menu-Separator">Terminplan  (Kalender)</div>
<div class="Menu-Line">
    <b>Hier werden die Termine in den Kalender eingepflegt</b>.
</div>
<div class="Menu-Line">
    <a href='TerminList.php' target='Terminplan'>Terminplan und Anmeldungs- Bearbeitung</a>
</div>
 
<div class="Menu-Separator">Index von Feuerwehrzeitungen</div>
<div class="Menu-Line">
    <b>Eingabe und Pflege von Index für Feuerwehrzeitugen</b>
</div>
<div class="Menu-Line">
    <a href='ZeitungList.php' target='ZT-Index'>Zeitungsindex</a>
</div>

<?php if ($has['Archiv']): 
//  Doku Foto Inventar
?> 
     <div class="Menu-Separator">Archivalien- Verwaltung</div>

     <div class="Menu-Line">
         <b>Verwaltung aller Dokumente, Video- Listen, Foto-(Negativ)-Listen (die Fotos und Videos selbst sind unter \"Foto,Video und Berichte\" zu finden), ..</b>
     </div>
     <div class="Menu-Line">
         <a href='ArchivVerw.php' target='A-Verwaltung'>Archivalienverwaltung und erweiterte Archivordnung </a>
     </div>
<?php endif; ?>  

<?php if (!empty($has['Inventar'])): 

?> 
     <div class="Menu-Separator">Inventar- Verwaltung</div>
     <div class="Menu-Line">
         Verwaltung aller nicht unter Dokumente fallenden Gegenstände</b>
     </div>
     <div class="Menu-Line">
         <a href='InventarVerw.php' target='Inventar'>Inventar- Verwaltung</a>
     </div>
<?php endif; ?>  

<?php if (!empty($has['Foto'])): 
?> 
     <div class="Menu-Separator">Medien (Fotos, Videos (Filme), Berichte) </div>

     <div class="Menu-Line">
        <b></b> Hier können die von Mitgliedern erstellten Fotos <b>einzeln oder als Masse (Verzeichnisweise</b> ins Netz gestellt,
       und  heruntergeladen und dürfen für Zwecke des Vereines mit Namensnennung des Fotografen (Urheber) verwendet werden.
       Für die Berichtserstellung werden diese Fotos direkt verwendet - kein extra Upload notwendig.</b>
     </div>
     <div class="Menu-Line">
         <a href='MedienVerw.php' target='Foto_Ber'>Foto, Video und Berichte- Verwaltung</a>";
     </div>
<?php endif; ?>  
      


