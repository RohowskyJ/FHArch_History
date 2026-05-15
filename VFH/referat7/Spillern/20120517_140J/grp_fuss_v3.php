<?php
session_start();
# ------------------------------------------------------------------------------------------------------------------
#
#Inhaltsverzeichnis und Aufruf für Archivierte Berichte


$module    = 'VF_Arch';
$path2ROOT = "../../../../";

$debug = True; $debug = False;  // Debug output Ein/Aus Schalter

require $path2ROOT.'login/common/BA_HTML_Funcs.lib.php' ;
require $path2ROOT.'login/common/BA_Funcs.lib.php' ;  // Diverse Unterprogramme

initial_debug(); # Wenn $debug=true - Ausgabe von Debug Informationen: $_POST, $_GET, $_FILES

$VF_logo = 'JA';
$header = "<link  href='".$path2ROOT."login/common/css/frame_pict.css' rel='stylesheet' type='text/css'>";
BA_HTML_header('Verein Feuerwehrhistoriker in NÖ) ',$header,'Form','75em'); # Parm: Titel,Subtitel,HeaderLine,Type,width

?>

<body class="w3-container ">
<div class="w3-content ">  <!-- max-width:45em; margin:5em;--->
<fieldset>
<legend>140 Jahre FF Spillern am 17.05.2012 <font size="-1">(Verein Feuerwehrhistoriker in NÖ)</font></legend><br>
<p> <strong>Fotos: Brigitta Laager (Feuerwehrhistoriker in NÖ), Josef Rohowsky (FF Wr. Neudorf, Feuerwehrhistoriker in NÖ)</strong></p>

<div class='w3-table' style='margin=auto'>

<table>
<tbody>
<tr><td colspan="11" align="center"><strong>Fusstruppen</strong></td></tr>

<tr><td>01</td><td colspan="6">"1945 Flucht" Tausende auf der Flucht vor den neuen Landesherren nach Beendigung des 2. Weltkrieges</td><td colspan="4" align="center"><a href="../../../../login/AOrd_Verz/124/09/06/20120517/124-20120517-W-IMG_0259.JPG"><img src="../../../../login/AOrd_Verz/124/09/06/20120517/124-20120517-W-IMG_0259.JPG" alt="0259" width="250px"/></a><a href="../../../../login/AOrd_Verz/124/09/06/20120517/124-20120517-W-IMG_0260.JPG"><img src="../../../../login/AOrd_Verz/124/09/06/20120517/124-20120517-W-IMG_0260.JPG" alt="0260"  width="250px"/></a></td></tr>
<tr><td>02</td><td colspan="6">Eine kleine Fachtagung bevor's richtig losgeht</td><td colspan="4" align="center"><a href="../../../../login/AOrd_Verz/124/09/06/20120517/124-20120517-W-IMG_0278.JPG"><img src="../../../../login/AOrd_Verz/124/09/06/20120517/124-20120517-W-IMG_0278.JPG"  alt="0278" width="250px"></a></td></tr>
<tr><td>03</td><td colspan="6">Die alten R&ouml;mer hatten schon die Vorfahren der heutigen Feuerwehr</td><td colspan="4" align="center"><a href="../../../../login/AOrd_Verz/124/09/06/20120517/124-20120517-W-IMG_0279.JPG"><img src="../../../../login/AOrd_Verz/124/09/06/20120517/124-20120517-W-IMG_0279.JPG" alt="0279" width="250px"/></a></td></tr>
<tr><td>04</td><td colspan="6">Die Feuerwehrmusik marschiert ab, es geht endlich los.</td><td colspan="4" align="center"><a href="../../../../login/AOrd_Verz/124/09/06/20120517/124-20120517-W-IMG_0280.JPG"><img src="../../../../login/AOrd_Verz/124/09/06/20120517/124-20120517-W-IMG_0280.JPG" alt="0280" width="250px"/></a></td></tr>
<tr><td>05</td><td colspan="6">Die Fahnengruppe folgt der Musik</td><td colspan="4" align="center"><a href="../../../../login/AOrd_Verz/631/09/06/20120517/631-20120517-W-DSCN6175.JPG"><img src="../../../../login/AOrd_Verz/631/09/06/20120517/631-20120517-W-DSCN6175.JPG" alt="6175" width="250px"/></a><a href="../../../../login/AOrd_Verz/631/09/06/20120517/631-20120517-W-DSCN6176.JPG"><img src="../../../../login/AOrd_Verz/631/09/06/20120517/631-20120517-W-DSCN6176.JPG" alt="6176" width="250px"/></a><a href="../../../../login/AOrd_Verz/631/09/06/20120517/631-20120517-W-DSCN6177.JPG"><img src="../../../../login/AOrd_Verz/631/09/06/20120517/631-20120517-W-DSCN6177.JPG" alt="6177" width="250px"/></a></td></tr>
<tr><td>06</td><td colspan="6">Die Deutschmeister.</td><td colspan="4" align="center"><a href="../../../../login/AOrd_Verz/124/09/06/20120517/124-20120517-W-IMG_0285.JPG"><img src="../../../../login/AOrd_Verz/124/09/06/20120517/124-20120517-W-IMG_0285.JPG" alt="0285" width="250px"/></a><a href="../../../../login/AOrd_Verz/631/09/06/20120517/631-20120517-W-DSCN6179.JPG"><img src="../../../../login/AOrd_Verz/631/09/06/20120517/631-20120517-W-DSCN6179.JPG" alt="6179" width="250px"/></a></td></tr>
<tr><td>07</td><td colspan="6">Unser Nachwuchs: die Feuerwehrjugend</td><td colspan="4" align="center"><a href="../../../../login/AOrd_Verz/124/09/06/20120517/124-20120517-W-IMG_0287.JPG"><img src="../../../../login/AOrd_Verz/124/09/06/20120517/124-20120517-W-IMG_0287.JPG" alt="0287" width="250px"/></a><a href="../../../../login/AOrd_Verz/124/09/06/20120517/124-20120517-W-IMG_0295.JPG"><img src="../../../../login/AOrd_Verz/124/09/06/20120517/124-20120517-W-IMG_0295.JPG" alt="0295" width="250px"/></a><a href="../../../../login/AOrd_Verz/631/09/06/20120517/631-20120517-W-DSCN6180.JPG"><img src="../../../../login/AOrd_Verz/631/09/06/20120517/631-20120517-W-DSCN6180.JPG" alt="6180" width="250px"/></a></td></tr>
<tr><td>08</td><td colspan="6">Mittelalterlicher Brandmelder</td><td colspan="4" align="center"><a href="../../../../login/AOrd_Verz/124/09/06/20120517/124-20120517-W-IMG_0289.JPGP"><img src="../../../../login/AOrd_Verz/124/09/06/20120517/124-20120517-W-IMG_0289.JPG" alt="0289" width="250px"/></a></td></tr>
<tr><td>09</td><td colspan="6">Eine wichtige St&uuml;tze im vorbeugenden Brandschutz, die Rauchfangkehrer</td><td colspan="4" align="center"><a href="../../../../login/AOrd_Verz/124/09/06/20120517/124-20120517-W-IMG_0300.JPG"><img src="../../../../login/AOrd_Verz/124/09/06/20120517/124-20120517-W-IMG_0300.JPG" alt="0300" width="250px"/></a><a href="../../../../login/AOrd_Verz/631/09/06/20120517/631-20120517-W-DSCN6195.JPG"><img src="../../../../login/AOrd_Verz/631/09/06/20120517/631-20120517-W-DSCN6195.JPG" alt="6195" width="250px"/></a></td></tr>
<tr><td>10</td><td colspan="6">L&ouml;schgruppe zu Fuss, mit Karrenspritze und Schlauchhaspel</td><td colspan="4" align="center"><a href="../../../../login/AOrd_Verz/124/09/06/20120517/124-20120517-W-IMG_0292.JPG"><img src="../../../../login/AOrd_Verz/124/09/06/20120517/124-20120517-W-IMG_0292.JPG" alt="0292" width="250px"/></a></td></tr>
<tr><td>11</td><td colspan="6">Berittene Feuerwerker</td><td colspan="4" align="center"><a href="../../../../login/AOrd_Verz/124/09/06/20120517/124-20120517-W-IMG_0294.JPG"><img src="../../../../login/AOrd_Verz/124/09/06/20120517/124-20120517-W-IMG_0294.JPG" alt="0294" width="250px"/></a></td></tr>

</tbody>
     </table>

     </div>


</fieldset>
</div>
<?php 
 HTML_trailer();
 ?>
