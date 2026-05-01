<?php
session_start();
# ------------------------------------------------------------------------------------------------------------------
#
#Inhaltsverzeichnis und Aufruf für Archivierte Berichte


$module    = 'VF_Arch';
$path2ROOT = "../../../../../";

$debug = True; $debug = False;  // Debug output Ein/Aus Schalter

require $path2ROOT.'login/common/BA_HTML_Funcs.lib.php' ;  // Diverse Unterprogramme
 require $path2ROOT.'login/common/BA_Funcs.lib.php' ;  // Diverse Unterprogramme

initial_debug(); # Wenn $debug=true - Ausgabe von Debug Informationen: $_POST, $_GET, $_FILES

$logo = 'JA';
$header = "<link  href='".$path2ROOT."login/common/css/frame_pict.css' rel='stylesheet' type='text/css'>";
BA_HTML_header('Verein Feuerwehrhistoriker in NÖ) ',$header,'Form','75em'); # Parm: Titel,Subtitel,HeaderLine,Type,width

?>

<h1>25.9.2010 Historikertag des Vereines Feuerwehrhistoriker in NÖ in Möllersdorf</h1>
<h2>Der Brandschutz und die Freiwilligen Feuerwehren im 19. Jahrhundert</h2>

<div class='w3-table' style='margin=auto'>

<table>
<tbody>
<tr>
  <td><a href="../../../../../login/AOrd_Verz/124/09/06/20100925/124-20100925-W-IMG_1393.JPG" target="trk" ><IMG SRC="../../../../../login/AOrd_Verz/124/09/06/20100925/124-20100925-W-IMG_1393.JPG" alt="Stadtsaal Traiskirchen" width="250px"/></a> </td>
   <td>
Die Veranstaltung fand in dem sch&ouml;nen Saal der ehemaligen Kammgarnspinnerei in Möllersdorf
(heute Stadtsaal Traiskirchen) unter der Moderation von Anton Mück statt. Die Begrüssung
erfolgte durch EHBM Heinrich Gutmann, die Eröffnung der Veranstaltung durch ELFR Ing. Johann Landstetter.
     </td>
    <td><a href="../../../../../login/AOrd_Verz/124/09/06/20100925/124-20100925-W-IMG_1403.JPG" target="trk" ><IMG SRC="../../../../../login/AOrd_Verz/124/09/06/20100925/124-20100925-W-IMG_1403.JPG" alt="Moderator Anton Mück" width="250px"/></a> </td>
 </tr>

<tr>
  <td colspan="3">Themen</td>
 </tr>

<tr>
  <td><a href="../../../../../login/AOrd_Verz/124/09/06/20100925/124-20100925-W-IMG_1405.JPG" target="trk" ><IMG SRC="../../../../../login/AOrd_Verz/124/09/06/20100925/124-20100925-W-IMG_1405.JPG" alt="VR Günter Annerl" width="250px"/></a></td>
   <td colspan="2">Abriss der Geschichte des 19. Jahrhunderts, Autor: Prof. Mag. Andreas Landstetter</td>
   <td></td>

<tr>
  <td><a href="../../../../../login/AOrd_Verz/124/09/06/20100925/124-20100925-W-IMG_1407.JPG" target="trk" ><IMG SRC="../../../../../login/AOrd_Verz/124/09/06/20100925/124-20100925-W-IMG_1407.JPG" alt="OBR Mag. Horst Rainer Sekyra" width="250px"/></a></td>
   <td>Turnvereine als Väter der Freiwilligen Feuerwehr, Autor: OBR Mag. Horst Rainer Sekyra</td>
    <td><a href="../../../../../login/AOrd_Verz/124/09/06/20100925/124-20100925-W-IMG_1408.JPG" target="trk" ><IMG SRC="../../../../../login/AOrd_Verz/124/09/06/20100925/124-20100925-W-IMG_1408.JPG" alt="Turnvater Jahn" width="250px"/></a></td>
 </tr>

<tr>
  <td><a href="../../../../../login/AOrd_Verz/124/09/06/20100925/124-20100925-W-IMG_1409.JPG" target="trk" ><IMG SRC="../../../../../login/AOrd_Verz/124/09/06/20100925/124-20100925-W-IMG_1409.JPG" alt="ELBDSTV Ing. Herbert Schanda" width="250px"/></a></td>
   <td colspan="2">Gründungsvorgang einer Freiwilligen Feuerwehr in Wr. Neustadt, Autor: ELBDSTV Ing. Herbert Schanda </td>
 </tr>

<tr>
  <td><a href="../../../../../login/AOrd_Verz/124/09/06/20100925/124-20100925-W-IMG_1410.JPG" target="trk" ><IMG SRC="../../../../../login/AOrd_Verz/124/09/06/20100925/124-20100925-W-IMG_1410.JPG" alt="BR Gilbert Müller" width="250px"/></a></td>
   <td colspan="2">Das Pferd als Arbeitstier - auch bei der Feuerwehr, Autor: BR Gilbert Müller</td>
 </tr>

<tr>
  <td><a href="../../../../../login/AOrd_Verz/124/09/06/20100925/124-20100925-W-IMG_1414.JPG" target="trk" ><IMG SRC="../../../../../login/AOrd_Verz/124/09/06/20100925/124-20100925-W-IMG_1414.JPG" alt="EHBI Hans Setznagel" width="250px"/></a></td>
   <td colspan="2">Geräte der Feuerwehren in dieser Zeit -  aus dem Katalog der Fa. Kernreuther, Autor: EHBI Hans Setznagel
      <br/>Film über das Dampfertreffen in Gainfarn 2010.
     </td>
 </tr>

<tr>
  <td><a href="../../../../../login/AOrd_Verz/124/09/06/20100925/124-20100925-W-IMG_1420.JPG" target="trk" ><IMG SRC="../../../../../login/AOrd_Verz/124/09/06/20100925/124-20100925-W-IMG_1420.JPG" alt="OBI Obst Ing. Günther Gutmann" width="250px"/></a></td>
   <td colspan="2">Die Entstehung des Feuerwehrmuseums Möllersdorf mit anschliessender Führung, Autor: OBI Obst Ing. Günther Gutmann </td>
 </tr>

<tr>
  <td><a href="../../../../../login/AOrd_Verz/124/09/06/20100925/124-20100925-W-IMG_1419.JPG" target="trk" ><IMG SRC="../../../../../login/AOrd_Verz/124/09/06/20100925/124-20100925-W-IMG_1419.JPG" alt="LFR Franz Koternetz" width="250px"/></a></td>
   <td colspan="2">Das Sachgebiet Feuerwehrgeschichte im LFR</td>
 </tr>

<tr>
  <td><a href="../../../../../login/AOrd_Verz/124/09/06/20100925/124-20100925-W-IMG_1529.JPG" target="trk" ><IMG SRC="../../../../../login/AOrd_Verz/124/09/06/20100925/124-20100925-W-IMG_1529.JPG" alt="EOV Franz Wiesenhofer und Franz Knoll" width="250px"/></a></td>
   <td colspan="2">Gestaltung eines Jubil&auml;umsfilms am Beispiel der Freiwilligen Feuerwehr Purgstall</td>
 </tr>


</tbody>
     </table>
<p>Quellen: Text und Fotos: Josef Rohowsky
</p>
     </div>


<?php
 BA_HTML_trailer();
 ?>
