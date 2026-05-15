<?php
session_start();
# ------------------------------------------------------------------------------------------------------------------
#
#Inhaltsverzeichnis und Aufruf für Archivierte Berichte


$module    = 'VF_Arch';
$path2ROOT = "../../../../";

$debug = True; $debug = False;  // Debug output Ein/Aus Schalter

 require $path2ROOT.'login/common/BA_Funcs.lib.php' ;  // Diverse Unterprogramme

initial_debug(); # Wenn $debug=true - Ausgabe von Debug Informationen: $_POST, $_GET, $_FILES

$logo = 'JA';
$header = "<link  href='".$path2ROOT."login/common/css/frame_pict.css' rel='stylesheet' type='text/css'>";
HTML_header('Verein Feuerwehrhistoriker in NÖ) ','Achivierte Berichte',$header,'Form','75em'); # Parm: Titel,Subtitel,HeaderLine,Type,width

?>

<h1>Wettbewerb -  einmal anders, Stadt Marcali (Ungarn)</h1>
(Bei den Fotos mit Umrandung wird durch anklicken das grössere Bild angezeigt.)
<p>
Fotos von Karl Klebinger, mit freundlicher Genehmigung zur Verfügung gestellt.
</p>
<p>
Vor fünf Jahren fand das erste mal ein Bewerb mit historischer Ausrüstung in der Stadt Marcali,
circa 15 km südlich des Plattensees.
</p>

<p>
Die grundlegende Idee ist, dass die heutigen jungen Feuerwehrleute auch über die Löscharbeiten,
wie sie in der Vergangenheit durchgeführt wurden, Bescheid wissen sollen.
</p>

<p>
Bei der ersten Durchfürung des Bewerbes waren 4 Teilnehmergruppen, heuer 17.
</p>

<p>
Beim Bewerb müssen die Teilnehmer über einen Balken laufen, ein Hindernis überqueren,
</p>
<div class='w3-table' style='margin=auto'>

<table>
<tbody>

<tr>
<td>
  <a href="KK-20100814-IMG_5595.jpg" target=_blank><img src="KK-20100814-IMG_5595.jpg" alt="" align="left" width="250px"></a>
 </td>
<td>
  <a href="KK-20100814-IMG_5590.jpg" target=_blank><img src="KK-20100814-IMG_5590.jpg" alt="" align="left" width="250px"></a>
 </td>
<td>
  <a href="KK-20100814-IMG_5587.jpg" target=_blank><img src="KK-20100814-IMG_5587.jpg" alt="" align="left" width="250px"></a>
 </td>
<td>
  <a href="KK-20100814-IMG_5603.jpg" target=_blank><img src="KK-20100814-IMG_5603.jpg" alt="" align="left" width="250px"></a>
 </td>
</tr>
</table>
<p>
dann an der Handspritze die Saug- und Druckschläche anschliessen, eine Gruppe der Teilnehmer
kümmert sich um den Druck, eine andere um das Ziel, eine gefüllte PET- Flasche von einem
Brett zu spritzen.

</p>

<table border="1" summary="Weitere Fotos">
<tr>
<td>Ein Detail vom Bewerbsgerät:</td>
<td align=center><a href="KK-20100814-IMG_5606.jpg" target=_blank><img src="KK-20100814-IMG_5606.jpg" alt=""  width="250px"></a></td>
</tr>

<tr>
<td>Ein noch im Einsatz befindlicher Oldtimer:</td>
<td align=center><a href="KK-20100814-IMG_5612.jpg" target=_blank><img src="KK-20100814-IMG_5612.jpg" alt=""  width="250px"></a> </td>
</tr>

<tr>
<td>Zwei weitere Bewerbsgruppen:</td>
<td width="53%"><a href="KK-20100814-IMG_5581.jpg" target=_blank><img src="KK-20100814-IMG_5581.jpg" alt="" align="left" width="250px"></a>  <a href="KK-20100814-IMG_5608.jpg" target=_blank><img src="KK-20100814-IMG_5608.jpg" alt="" align="right" width="250px"></a> </td>
</tr>

<tr>
<td>Weitere, auf die Fertigstellung wartende Geräte:</td>
<td align=center><a href="KK-20100814-IMG_5623.jpg" target=_blank><img src="KK-20100814-IMG_5623.jpg" alt=""  width="250px"></a></td>
</tr>

</tbody>
     </table>

     </div>



<?php 
 HTML_trailer();
 ?>
