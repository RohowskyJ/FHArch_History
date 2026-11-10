<?php 
/** 
 * Haupt- Menu, interne Seite
 * 
 */

$this->layout('layout', ['title' => $title, 'systemBasePath' => $systemBasePath, 'cssBundles' => $cssBundles , 'has' => $has ] ) ;

if (!empty($debug)) {
    var_dump($has);
}
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


<?php if (!empty($can['referat1'])): ?>
<div class="Menu-Separator">Referat 1 - Organisation</div>
<div class="Menu-Line">
    <b>Datenabfrage laut DSVGO, E-Mail an andere Mitglieder, Protokolle,</b> Verwaltung der Daten von Mitgliedern, Benutzern und Zugriffen, Eigentümern, Empfängerliste autom. E-Mails, ....
</div>
<div class="Menu-Line">
    <a href="ZentralVerw.php" target="Zentrale Verwaltung">Zentrale Verwaltung Basisdaten</a>
</div>
<?php endif; ?>

<?php #var_dump($has); 
if (!empty($has['FzgGer'])): ?>   
        <div class="Menu-Separator">Referat 2 - Fahrzeuge und Geräte, mit Muskel oder Motor bewegt, Beschreibungen</div>
        <div class="Menu-Line">
             Beschreibungen von Fahrzeugen und Geräten: muskelgezogen und Motorgezogen
        </div>
       <div class="Menu-Line">
           <a href="../Modules/FahrzeugeGeraete/FzgGerVerw.php" target="F-Verwaltung">Fahrzeug und Geräte- Verwaltung</a>
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
         <div class="Menu-Separator">Archivalien</div>
         <div class="Menu-Line">
              Links zu Bibliotheken, Marktplatz, Buch- Rezensionen, Dokumente zu herunterladen, Fotos, Videos, Museumsdaten, Presseberichte, Terminplan, Veranstaltungsberichte.
         </div>
         <div class="Menu-Line">
             <a href="../Modules/Archivalien/ArchivVerw.php" target="Archiv">Archivalien- Verwaltung</a>
         </div>  
    <?php endif; ?>
    <?php if (!empty($has['Media'])): ?>   
       <div class="Menu-Separator">Fotos, Videos und Berichte</div>
       <div class="Menu-Line">
           Links zu Bibliotheken, Marktplatz, Buch- Rezensionen, Dokumente zu herunterladen, Fotos, Videos, Museumsdaten, Presseberichte, Terminplan, Veranstaltungsberichte.
       </div>
       <div class="Menu-Line">
           <a href="../Modules/Medien/MedienVerw.php" target="Foto">Medien- Verwaltung und Berichte</a>
       </div>  
    <?php endif; ?>
    <?php if (!empty($has['Inventar'])): ?>  
       <div class="Menu-Separator">Inventar</div>
       <div class="Menu-Line">
           Inventar- Verwaltung
       </div>
       <div class="Menu-Line">
           <a href="../Modules/Inventar/InventVerw.php" target="Invent">Inventar- Verwaltung</a>
       </div>   
    <?php endif; ?>
    <?php if (!empty($has['Doku'])): ?>   
       <div class="Menu-Separator">Vereins- Dokumentationen</div>
       <div class="Menu-Line">
           Links zu Bibliotheken, Marktplatz, Buch- Rezensionen, Dokumente zu herunterladen, Fotos, Videos, Museumsdaten, Presseberichte, Terminplan, Veranstaltungsberichte.
       </div>
       <div class="Menu-Line">
           <a href="../Modules/Oeffentlichkeitsarbeit/DokuVerw.php" target="Doku">Dokumentationen</a>
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
           <a href="../Modules/Psa/PsaVerw.php" target="Info_R4">Auszeichnungen, Ärmelabzeichen, Wappen - DA 1.5.3, Uniformen, Heraldik</a>
       </div> 
<?php endif; ?>

<div class='Menu-Separator'> E-Mail an Mitglieder</div>

<div class='Menu-Line'> 
      Mitglieder können E-Mails an andere Mitglieder senden, ohne das Sie die E-Mail Adresse kennen.</a>
</div> 
<div class='Menu-Line'>
     <a href='MitglEMail.php' target='M-Mail'>Mail an andere Mitglieder senden </a>
</div> 

<div class='Menu-Separator'>Mitglieder- Auskuft laut DSVGO</div>

<div class='Menu-Line'>
     Jedes Mitglied kann sich die im System gespeicherten persönliche Daten entsprechend der DSVGO selbst anfordern und bekommt sie sofort per E-Mail zugeschickt.
</div>

<div class='Menu-Line'>
    <a href="<?= !empty($has['mitgliedDsvgo']) ? '../Modules/Mitglieder/DsvgoMiMail.php' : '../AllgVerw/DsvgoBeMail.php' ?>" target="M-Mail" onclick="window.open(this.href, this.target); return false;">DSVGO Information abrufen</a>
</div>

<div class="Menu-Separator">Beenden </div>


<div class="Menu-Separator" style="margin-top: 48px;">
    <a href="../../logout.php">LOGOFF (HomePage)</a>
</div>