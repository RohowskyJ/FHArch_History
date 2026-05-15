<?php

/**
 * Anzeige des externen Archve, alte Berichte
 * @var string $module
 */
session_start();

$module    = 'VF_Arch';

/**
 * Angleichung an den Root-Path
 *
 * @var string $path2ROOT
 */
$path2ROOT = "../../";

$debug = False;  // Debug output Ein/Aus Schalter

require $path2ROOT.'login/common/BA_HTML_Funcs.lib.php' ;
require $path2ROOT.'login/common/BA_Funcs.lib.php' ;  // Diverse Unterprogramme

# VF_initial_debug(); # Wenn $debug=true - Ausgabe von Debug Informationen: $_POST, $_GET, $_FILES
initial_debug(); # Wenn $debug=true - Ausgabe von Debug Informationen: $_POST, $_GET, $_FILES

BA_HTML_header('Verein Feuerwehrhistoriker in NÖ) ','','Form','90em'); # Parm: Titel,Subtitel,HeaderLine,Type,width
?>

<body class="w3-container ">
<div class="w3-content ">  <!-- max-width:45em; margin:5em;--->


     <div class='w3-table' style='margin=auto'>
     
<h1>Berichte über Veranstaltungen und Veröffentlichungen</h1>


<table summary="Einstiegsseite" border="1" >

<tbody>
<tr>
   <th>
      <font size="+2">
         Grundlagen - Gesetze
      </font>
    </th>
 </tr>
<tr>
    <th>
      <font size="+1"> <br>
        <a href="Pflichtabgabe/Pflichtablieferung_BGBLA_2009_II_271.pdf" target='PflA'>Pflichtablieferung von Druckwerken - Gesetzestext</a>  <br>
      </font>
     </th>
</tr>

<tr>
    <th>
      <font size="+1"> <br>
        <a href="Pflichtabgabe/5-2009_Information_Pflichtablieferung.pdf" target='PflA'>Pflichtablieferung von Druckwerken - Kurzinformation</a>  <br>
      </font>
     </th>
</tr>

<tr><th>&nbsp;</th></tr>

<tr>
   <th>
      <font size="+2">
         LFKDO N&Ouml; - Ausbildung SG Feuerwehrgeschichte
      </font>
    </th>
 </tr>
<tr>
    <th>
      <font size="+1"> <br>
        <a href="LFKDO/20100116_Ausb_2010.pdf"  target='Ausb'>Ausbildung SG Feuerwehrgeschichte 2010</a>  <br>
      </font>
     </th>
</tr>

<tr><th>&nbsp;</th></tr>

<tr>
   <th>
      <font size="+2">
        BFKDO Krems - Internetseite, zur Feuerwehrgeschichte im Bezirk Krems
      </font>
    </th>
 </tr>

<tr>
    <th>
      <font size="+1"> <br>
        <a href="http://www.feuerwehr-krems.at"  target='Krems'>Feuerwehr Krems, siehe Feuerwehrgeschichte</a>  <br>
          (Sachgebiete-> Feuerwehrgeschichte)  <br/>
      </font>
     </th>
</tr>

<tr>
   <th>
      <font size="+2">
         BFKDO Mödling
      </font>
    </th>
 </tr>

<tr>
    <th>
      <font size="+1"> <br>
        <a href="LFKDO/BFKDO_Moedling/4-2009_Aufbau_Sammlung_Bezirk.pdf" target='Sammlg'>Aufbau einer Jahresbericht- und Festschriftensammlung im Bezirk</a>  <br>
      </font>
     </th>
</tr>

<tr><th>&nbsp;</th></tr>

<tr>
   <th>
      <font size="+2">
         Veranstaltungen
      </font>
    </th>
 </tr>
 
 <tr>
    <th>
      <font size="+1">
        <a href="Gars/FireTrophy/20060902/index_gaft_0609.php" target='firetr'>2. - 3. 9.2006 - 2. Waldviertler FIRE-Trophy 2006 - Gars/Kamp</a>
      </font>
     </th>
</tr>
 
<tr>
    <th>
      <font size="+1"> <br>
        <a href="VFHNOE/Genv/20060903/index_genv_0609.php" target='GV2006'>3.9.2006 - Generalversammlung 2006 des Vereines - Gars/Kamp</a>  <br>
      </font>
     </th>
</tr>

<tr>
    <th>
      <font size="+1">
        <a href="LFS_TU/20060906/index_LFTu_0609.php" target='Schaur'>6.9.2006 - Eröffnung LFS neu Tulln</a>
         6.9.2006 - LFS neu Tulln - Tag der offenen Tür
 <br>
      </font>
     </th>
</tr>

<tr>
    <th>
      <font size="+1">
        <a href="CTIF/20060927/index_CT_0609.php" target='ctif'>27. - 29.9.2006 -  CTIF Arbeitsgemeinschaft für Feuerwehr und Brandschutzgeschichte
         in Jonsdorf
</a> <br>
      </font>
     </th>
</tr>

<tr>
    <th>
      <font size="+1">
        <a href="VFHNOE/BildgReise/20061021/index_VF_Bldg_0610.php" target='StdR'>21.10.2006 - Studienreise 2006</a>
      </font>
     </th>
</tr>

<tr>
    <th>
      <font size="+1">
        2007  <br>
      </font>
     </th>
</tr>

<tr>
    <th>
      <font size="+1">
        <a href="StPoelten/20070512/index_StPoe_0705.php" target='StP'>12.5.2007 - 140 Jahre FF St. P&ouml;lten</a>
      </font>
     </th>
</tr>

<tr>
    <th>
        <a href="StFlorian/20070512/index_StFlo_0705.php" target='StFlo'>12.5.2007 - 1. Museumsfest St. Florian 2007</a>
     </th>
</tr>

<tr>
    <th>
      <font size="+1">
        <a href="Spillern/20070615/index_Spi_0706.php" target='Spill'>15.6.2007 - 135 Jahre FF Spillern mit Fahnenweihe</a>
      </font>
     </th>
</tr>

<tr>
    <th>
      <font size="+1">
        2008  <br>
      </font>
     </th>
</tr>
<tr>
    <th>
      <font size="+1">
        <a href="LFS_TU/20080405/index_LFTu_0804.php" target='LFS'>05.04.2008 - 1. Achivar- Lehrgang an der Landesfeuerwehrschule in Tulln</a>
      </font>
     </th>
</tr>
<tr>
    <th>
      <font size="+1">
        <a href="Perchtoldsdorf/20080503/index_Percht_0805.php" target='PDorf'>03.-04.05.2008 - Florianitag in Perchtoldsdorf</a>
      </font>
     </th>
</tr>
<tr>
    <th>
      <font size="+1">
        <a href="Bruck_Leitha/20080504/bruck-l_080504.html" target='LFS'>04.05.2008 - Florianitag in Bruck an der Leitha, Segnung Feuerwehrmuseum</a>
      </font>
     </th>
</tr>


<tr>
    <th>
      <font size="+1">
        2009  <br>
      </font>
     </th>
</tr>
<tr>
    <th>
      <font size="+1">
        <a href="CTIF/20090414/index_CT_0904.php" target='Tausch'>14.4.2009 - Jaromir Tausch verstorben</a>
        </font>
     </th>
</tr>

<tr>
    <th>
      <font size="+1">
        <a href="StFlorian/20090523/index_StFlo_0905.php" target='StFlo'>23.5.2009 - 2. Museumsfest St. Florian 2009 - 25 Jahre Feuerwehrmuseum</a>
      </font>
     </th>
</tr>

<tr>
    <th>
      <font size="+1">
        <a href="Gars/FireTrophy/20090904/index_Ga_Ft_0909.php" target='Ftro'>4-6.9.2009 - Firetrophy 2009, Teilnehmerliste</a>  <br>
        <a href="Gars/FireTrophy/20090904/Ber_Annerl_v3.php" target='Ftro'>4-6.9.2009 - Firetrophy 2009, Bericht Günter Annerl</a>
      </font>
     </th>
</tr>

<tr>
    <th>
      <font size="+1">
        <a href="Gars/DampfFeuer/20090802/index_Ga_Df_0908.php" target='DaFeu'>2.8.2009 - Dampf und Feuer - Parallelfahrt Dampfzug- Feuerwehroldtimer</a>
      </font>
     </th>
</tr>

<tr>
    <th>
      <font size="+1">
        <a href="StPoelten/20090919/index_St_Poe_0919.php" target='LFV'>19.9.2009 - 140 Jahre Landesfeuerwehrverband NÖ, in St. Pölten</a>
      </font>
     </th>
</tr>

<tr>
    <th>
      <font size="+1">
        2010  <br>
      </font>
     </th>
</tr>

<tr>
    <th>
      <font size="+1">
        <a href="FF_Lustenau_oldie/2010/restaurierung.html" target='Lustenau'>2010 - Start der Restaurierung Lustenauer AF</a>
      </font>
     </th>
</tr>

<tr>
    <th>
      <font size="+1">
        <a href="Purgstall/20100423/index_Purg_1004.php" target='Purgst'>23.4.2010 - Ausstellung in Purgstaller Feuerwehrmuseum</a>
      </font>
     </th>
</tr>

<tr>
    <th>
      <font size="+1">
        <a href="Marcali_HU/20100814/index_HU_1008.php" target='Marca'>14.8.2010 Ein Bewerb der etwas anderen Art - BF Marcali (HU)- altes Gerät im Einsatz</a>
      </font>
     </th>
</tr>

<tr>
    <th>
      <font size="+1">
        <a href="Purgstall/20100918/index_Purg_0918.php" target='Purg'>18.9.2010 - Feier zum 140 jährigen Bestehen der FF Purgstall</a>
      </font>
     </th>
</tr>

<tr>
    <th>
      <font size="+1"> <br>
        <a href="VFHNOE/Histtag/20100925/index_VF_Histt_1009.php" target='HistT'>25.9.2010 - Historikertag des Vereines der Feuerwehrhistoriker in NÖ</a> 
      </font>
     </th>
</tr>

<tr>
    <th>
      <font size="+1">
        <a href="CTIF/20100929/index_CT_1009.php" target='CTIF'>29.9.2010-02.10.2010 - 18. Tagung der Internat. Arbeitsgemeinschaft für Feuerwehr- und Brandschutzgeschichte in Varazdin</a>
      </font>
     </th>
</tr>

<tr>
    <th>
      <font size="+1">
        <a href="VFHNOE/BildgReise/20101023/index.html" target='Bldg_20101023'>23.10.2010-24.10.2010 - Bildungsreise Südsteiermark und Südburgenland</a>
      </font>
     </th>
</tr>

<tr>
    <th>
      <font size="+1"> <br>
        2011  <br>
      </font>
     </th>
</tr>

  <tr>
    <th>
      <font size="+1"> <br>
        <a href="BadSauerbrunn/20110430/index_Bad_sau_1104.php" target='Oldies'>1. Burgenländisches Oldtimertreffen Bad. Sauerbrunn 30.04.2011</a>
      </font>
     </th>
</tr>

<tr>
    <th>
     <font size="+1">
        <a href="VFHNOE/Histtag/20110917/index_VF_Histt_1109.php" target='EBra'>17.9.2011 Historikertag im Stift Geras</a>
     </font>
    </th>
</tr>

<tr>
    <th>
      <font size="+1">
        2012
      </font>
     </th>
</tr>

<tr>
    <th>
     <font size="+1">
        <a href="Spillern/20120517_140J/index_Sp_1205.php" target='Spill'>17.5.2012 Umzug historischer Fahrzeuge zum 140 jährigen Bestand der FF Spillern</a>
     </font>
    </th>
</tr>



</tbody>
</table>


<table summary="Einstiegsseite" border="1" >

<tbody>
<tr>
   <th>
      <font size="+2">
         Veröffentlichungen
      </font>
    </th>
 </tr>
<tr>
    <th>
      <font size="+1">
        <a href="buecher/kaier/index.html" target='Helm'>Helmbuch von Arnold Kaier</a>
      </font>
     </th>

</tr>

<tr>
    <th>
      <font size="+1">
        <a href="buecher/rux/index.html" target='TS'>Die Tragkraftspritze von Günter Rux</a>
      </font>
     </th>

</tr>

<tr>
    <th>
      <font size="+1">
        <a href="buecher/schanda/index.html" target='sch'>ElBDStv Ing. Herbert Schanda, Publikationen</a>
      </font>
     </th>

</tr>

</table>

</div>

</div>

<?php

 BA_HTML_trailer();
 ?>
