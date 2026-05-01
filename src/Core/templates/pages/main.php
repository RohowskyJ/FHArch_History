<?php 
/** 
 * Haupt- Menu, interne Seite
 * 
 */

$this->layout('layout', ['title' => $title, 'path2ROOT' => $path2ROOT, 'cssBundles' => $cssBundles , 'has' => $has ] ) ;

var_dump($has);
?>

<div class="Menu-Header">Programmauswahl für Mitglieder</div>

<?php if (!empty($has['Suchen'])): ?> 
     <div class="Menu-Separator">Suchen nach Suchbegriffen</div>

     <div class="Menu-Line">
         Suchen in archivalien, Inventar, Fotos und Beschreibungen von Fahrzeugen und Geräten: muskelgezogen und Motorgezogen
     </div>
     <div class="Menu-Line">
         <a href="VF_S_SU_Ausw.php" target="Suchausw">Suchen nach Suchbegriffen</a>
     </div>
<?php endif; ?>  


<div class="Menu-Separator">Referat 1 - Organisation</div>
<div class="Menu-Line">
    <b>Datenabfrage laut DSVGO, E-Mail an andere Mitglieder, Protokolle,</b> Verwaltung der Daten von Mitgliedern, Benutzern und Zugriffen, Eigentümern, Empfängerliste autom. E-Mails, ....
</div>
<div class="Menu-Line">
    <a href="ZentralVerw.php" target="Zentrale Verwaltung">Zentrale Verwaltung Basisdaten</a>
</div>

<?php if (!empty($has['FzgGer'])): ?>   
        <div class="Menu-Separator">Referat 2 - Fahrzeuge und Geräte, mit Muskel oder Motor bewegt, Beschreibungen</div>
        <div class="Menu-Line">
             Beschreibungen von Fahrzeugen und Geräten: muskelgezogen und Motorgezogen
        </div>
       <div class="Menu-Line">
           <a href="FZGerVerw.php" target="F-Verwaltung">Fahrzeug und Geräte- Verwaltung</a>
       </div>    
<?php endif; ?>
    
<?php if (!empty($has['Oeffi'])): ?> 
       <div class="Menu-Separator">Referat 3 - Öffentlichkeitsarbeit</div>
       <div class="Menu-Line">
           Links zu Bibliotheken, Marktplatz, Buch- Rezensionen, Dokumente zu herunterladen, Fotos, Videos, Museumsdaten, Presseberichte, Terminplan, Veranstaltungsberichte.
       </div>
       <div class="Menu-Line">
           <a href="../Modules/Oeffentlichkeitsarbeit/OeffiVerw.php" target="Oeffi">Öffentlichkeitsarbeit</a>
       </div>  
<?php else: ?>
    <?php if (!empty($has['Archiv'])): ?>   
         div class="Menu-Separator">Archivalien</div>
         <div class="Menu-Line">
              Links zu Bibliotheken, Marktplatz, Buch- Rezensionen, Dokumente zu herunterladen, Fotos, Videos, Museumsdaten, Presseberichte, Terminplan, Veranstaltungsberichte.
         </div>
         <div class="Menu-Line">
             <a href="ArchivVerw.php" target="Archiv">Archivalien- Verwaltung</a>
         </div>  
    <?php endif; ?>
    <?php if (!empty($has['Foto'])): ?>   
       <div class="Menu-Separator">Fotos, Videos und Berichte</div>
       <div class="Menu-Line">
           Links zu Bibliotheken, Marktplatz, Buch- Rezensionen, Dokumente zu herunterladen, Fotos, Videos, Museumsdaten, Presseberichte, Terminplan, Veranstaltungsberichte.
       </div>
       <div class="Menu-Line">
           <a href="FotoVerw.php" target="Foto">Medien- Verwaltung und Berichte</a>
       </div>  
    <?php endif; ?>
    <?php if (!empty($has['Inventar'])): ?>  
       <div class="Menu-Separator">Inventar</div>
       <div class="Menu-Line">
           Links zu Bibliotheken, Marktplatz, Buch- Rezensionen, Dokumente zu herunterladen, Fotos, Videos, Museumsdaten, Presseberichte, Terminplan, Veranstaltungsberichte.
       </div>
       <div class="Menu-Line">
           <a href="InventVerw.php" target="Invent">Inventar- Verwaltung</a>
       </div>   
    <?php endif; ?>
    <?php if (!empty($has['Doku'])): ?>   
       <div class="Menu-Separator">Vereins- Dokumentationen</div>
       <div class="Menu-Line">
           Links zu Bibliotheken, Marktplatz, Buch- Rezensionen, Dokumente zu herunterladen, Fotos, Videos, Museumsdaten, Presseberichte, Terminplan, Veranstaltungsberichte.
       </div>
       <div class="Menu-Line">
           <a href="DokuVerw.php" target="Doku">Dokumentationen</a>
       </div>  
    <?php endif; ?>
    <?php if (!empty($has['Archiv'])): ?>   
    <?php endif; ?>
<?php endif; ?>

<?php if (!empty($has['PSA'])): ?>  
       <div class="Menu-Separator">Persönliche Ausrüstung - Beschreibungen</div>
       <div class="Menu-Line">
           Beschreibungen für alle Gegenstände die je FF Mitglied direkt der Person zugordnet ist (Uniform, Auszeichnugen, ...)
       </div>
       <div class="Menu-Line">
           <a href="FS_PS_Info_Ausz_Abz.php" target="Info_R4">Auszeichnungen, Ärmelabzeichen, Wappen - DA 1.5.3, Uniformen, Heraldik</a>
       </div> 
<?php endif; ?>
<!--  
<div class="Menu-Separator">Referat 2 - Fahrzeuge und Geräte, mit Muskel oder Motor bewegt, Beschreibungen</div>
<div class="Menu-Line">
    Beschreibungen von Fahrzeugen und Geräten: muskelgezogen und Motorgezogen
</div>
<div class="Menu-Line">
    <a href="FZGerVerw.php" target="F-Verwaltung">Fahrzeug und Geräte- Verwaltung</a>
</div>

<div class="Menu-Separator">Referat 3 - Öffentlichkeitsarbeit und Museen</div>
<div class="Menu-Line">
    Links zu Bibliotheken, Marktplatz, Buch- Rezensionen, Dokumente zu herunterladen, Fotos, Videos, Museumsdaten, Presseberichte, Terminplan, Veranstaltungsberichte.
</div>
<div class="Menu-Line">
    <a href="OeffiVerw.php" target="Oeffi">Öffentlichkeitsarbeit</a>
</div>

<div class="Menu-Separator">Persönliche Ausrüstung - Beschreibungen</div>
<div class="Menu-Line">
    Beschreibungen für alle Gegenstände die je FF Mitglied direkt der Person zugordnet ist (Uniform, Auszeichnugen, ...)
</div>
<div class="Menu-Line">
    <a href="FS_PS_Info_Ausz_Abz.php" target="Info_R4">Auszeichnungen, Ärmelabzeichen, Wappen - DA 1.5.3, Uniformen, Heraldik</a>
</div>
-->
<div class="Menu-Separator" style="margin-top: 48px;">
    <a href="../">LOGOFF (HomePage)</a>
</div>