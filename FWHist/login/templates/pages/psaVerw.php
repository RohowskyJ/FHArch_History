<?php

/** 
 * Persönliche AUsrüstungs- arbeit, Menu
 * 
 */

$this->layout('layout', ['title' => $title, 'systemBasePath' => $systemBasePath, 'cssBundles' => $cssBundles , 'has' => $has ] ) ;

if (!empty($debug)) {
    var_dump($has);
    var_dump($systemBasePath);
}
?>

<div class="Menu-Header">Persönliche Ausrüstung</div>

<div class="Menu-Separator">Ärmelabzeichen, Wappen</div>
<div class="Menu-Line">
    <b>Dienstanweisungen NÖLFKDO für Ärmelabzeichen (zur Anzeige den entsprechenden Link anklicken)</b>
</div>
<div class="Menu-Line">
    1951 Rundschreiben, Vorläufer der DA 1.5.3<br>
</div>
<div class="Menu-Line">
    DA 1.5.3 01.11.2005
</div>
   
<div class="Menu-Line">
   <a href= '20061124_DA-1-5-3.php' target='DA_2006'>DA 1.5.3 24.11.2006</a>
</div>

<div class="Menu-Line">
    DA 1.5.3 01.08.2012
</div>

<div class="Menu-Line">
    <a href='20140501_DA-1-5-3.php' target='DA_2014'>DA 1.5.3 01.05.2014</a>
</div>

<div class="Menu-Line">
    <a href='http://<?php echo $systemBasePath?>login/AOrd_Verz/PSA/DA/201701_da-3-6-2-dienstbekleidung.pdf' target='DA_2017'>DA 3.6.2 01.2017</a>
</div>

<div class="Menu-Line">
    <a href='http://<?php echo $systemBasePath?>login/AOrd_Verz/PSA/DA/202212_da-3-6-2-ergaenzung.pdf' target='DA_2022'>DA 3.6.2 Erweiterung 16.12.2022</a>
</div>

<div class="Menu-Line">
    <b>Informationen zu Begriffen (Wikipedia)</b>
</div>

<div class="Menu-Line">
    <a href='http://de.wikipedia.org/wiki/Heraldik' target='Herald'>Heraldik </a>(Heroldskunst, Wappenwesen, Wappenkunst, Wappenkunde und Wappenrecht)
</div>

<div class="Menu-Line">
    <a href='http://de.wikipedia.org/wiki/Blasonierung' target='Blasionierung'>Blasonierung </a>ist in der Heraldik die fachsprachliche Beschreibung eines Wappens. 
</div>

<div class="Menu-Separator">Auszeichnungen, Ehrenzeichen, Leistungsabzeichen, ...</div>
  
<div class="Menu-Line">
    <b>Quellen, Abkürzungen, Hinweise, ...</b>
</div>

<div class="Menu-Line">
    <a href='PsaQuellen.php' target='Quel'>Quelle, Abkürzungen</a>
</div>

<div class="Menu-Line">
    <a href='PsaAbbilder.php' target='Blder'>Bildgrößen</a>
</div>


<div class="Menu-Separator">Aufruf der Programmteile: </div>
<div class="Menu-Line">
    <b>Ärmelabzeichen, Wappen (Ortswappen, Verwendung bei der FF)</b>
</div>
<div class="Menu-Line">
    <a href='OrtsAuswList.php?proj=AERM' target='Orts-Daten' >Orts-, Wappen und Abzeichenverwaltung</a>
</div>


<div class="Menu-Line">
    <b>Ehrenzeichen, Auszeichnungen, Vereins- Abzeichen</b>
</div>

<div class="Menu-Line">
    <a href='OrtsAuswList.php?proj=EHRZ' target='Auszeichnungs-Daten'>Auszeichnungen, Ehrenzeichenverwaltung</a>
</div>


<div class="Menu-Separator">Druckwerke</div>
<div class="Menu-Line">
    <b>Hier werden die verschiedenen im Verein erstellten und Vorgetragenen Dokumentationen ins Netz gestellt,
          und können heruntergeladen und dürfen für Zwecke der Feuerwehrgeschichte verwendet werden</b>
</div>
<div class="Menu-Line">
    <a href='../../../Core/Modules/Oeffentlichkeitsarbeit/DokuList.php' target='Doku'>Dokumentationen zum herunterladen</a>
</div>

    <?php 

  ?> 
   
<div class="Menu-Line">
    <a href='../../../AOrd_Verz/Downloads/Drucke/Katalog-FG/Uniformen_01092009.pdf' target='Uniformen'>Uniformen</a>
</div>

<div class="Menu-Line">
    <a href='../../../AOrd_Verz/Downloads/Drucke/Katalog-FG/Katalog_m_Gilb_W.pdf' target= 'Ausz_1' >Auszeichnungen, Teil 1</a><br>
    <a href='../../../AOrd_Verz/Downloads/Drucke/Katalog-FG/Ausz_Kat_Teil_2_20140403_n-1.pdf' target='Ausz_2' >Auszeichnungen, Teil 2</a><br>
     <a href='../../../AOrd_Verz/Downloads/Drucke/Katalog-FG/Ausz_Kat_3_20150415.pdf' target='Ausz_3' >Auszeichnungen, Teil 3</a>   
</div>

<div class="Menu-Line">

</div>
    

<?php if ($has['Ehrung']): 
//  Doku Foto Inventar
?> 
     <div class="Menu-Separator">Ehrungen, Auszeichnungen, Leistungsabzeichen</div>

     <div class="Menu-Line">
         <b>Verwaltung aller Dokumente, Video- Listen, Foto-(Negativ)-Listen (die Fotos und Videos selbst sind unter \"Foto,Video und Berichte\" zu finden), ..</b>
     </div>
     <div class="Menu-Line">
         <a href='../../../Core/Modules/Psa/EhrungenList.php' target='A-Verwaltung'>Ehrenzeichen, Leistungsabzeichen, ... </a>
     </div>
<?php endif; ?>  

<?php if (!empty($has['AermAb'])): 

?> 
     <div class="Menu-Separator">Ärmelabzeichen</div>
     <div class="Menu-Line">
         Verwaltung aller nicht unter Dokumente fallenden Gegenstände</b>
     </div>
     <div class="Menu-Line">
         <a href='../../../Core/Modules/Psa/AermAbzList.php' target='Ärmelabzeichen'>Ärmelabzeichen</a>
     </div>
<?php endif; ?>  

<?php if (!empty($has['OldEhrg'])): 
?> 
     <div class="Menu-Separator">Ehrungen und Auszeichnungen, Leistungsabzeichen - alte Version </div>

     <div class="Menu-Line">
         <a href='../../../VF_PS_OV_O_List.php?proj=AUSZ' target='Ehrung'>Ehren- und Leistungsabzeichen</a>
     </div>
<?php endif; ?>  

<?php if (!empty($has['OldAermAb'])): 
?> 
     <div class="Menu-Separator">Ärmelabzeichen, alte Version </div>

     <div class="Menu-Line">
         <a href='../../../VF_PS_OV_O_List.php?proj=AERM' target='Aermel'>Ärmelabzeichen</a>
     </div>
<?php endif; ?>  
      
      <div class="Menu-Separator">Beenden </div>

<div class="Menu-Line">
    <a href="../../logout.php">LOGOFF (HomePage)</a>
</div>
      
