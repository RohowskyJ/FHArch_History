<?php
session_start();
# ------------------------------------------------------------------------------------------------------------------
#
#Inhaltsverzeichnis und Aufruf für Archivierte Berichte


$module    = 'VF_Arch';
$path2ROOT = "../../../../";

$debug = True; $debug = False;  // Debug output Ein/Aus Schalter

require $path2ROOT.'login/common/BA_HTML_Funcs.lib.php' ;  // Diverse Unterprogramme
 require $path2ROOT.'login/common/BA_Funcs.lib.php' ;  // Diverse Unterprogramme

initial_debug(); # Wenn $debug=true - Ausgabe von Debug Informationen: $_POST, $_GET, $_FILES

$logo = 'JA';
$header = "<link  href='".$path2ROOT."login/common/css/frame_pict.css' rel='stylesheet' type='text/css'>";
BA_HTML_header('Verein Feuerwehrhistoriker in NÖ) ',$header,'Form','75em'); # Parm: Titel,Subtitel,HeaderLine,Type,width

?>

<body class="w3-container ">
<div class="w3-content ">  <!-- max-width:45em; margin:5em;--->
<fieldset>


<div class='w3-table' style='margin=auto'>

<table>
<tbody>
<tr>
 <th>
TLF 2000 auf MB 1113
<br/>
(1968 - 2006)
<br/>
Michael Thomasberger, Eidenberg
  </th>
</tr>

<tr>
 <td>
   <img src="../../../../login/AOrd_Verz/124/09/06/20070512/124-20070512-W-IMG_5862.JPG" alt="TLF 2000 auf MB 1113" align="right" />
Um die sechziger Jahre finden wir für TLF auch die Kurzhauber-Fahrgestelle
von MERCEDES BENZ, die sich großer Akzeptanz erfreut haben,
wie beispielsweise die Typen MB 911, MB 322 und dann die leistungsstärkere
Type MB 1113.
<br/>
In den Folgejahren sind dann die für uns wichtigsten Fahrgestellproduzenten
auf Frontlenker-Fahrgestelle umgestiegen.
<br/>
Unser Feuerwehrkamerad Mag. Michael Thomasberger von der FF Eidenberg,
Bezirk Urfahr-Umgebung,  zeigt sein:
<br/>
TLF 2000 auf Mercedes LAF 1113, Fahrgestell Daimler-Benz, Stuttgart,
mit Allradantrieb, Aufbau Firma Rosenbauer,  Wien.
Viertakt-Sechszylinder-Dieselmotor mit 126 PS, Bauartgeschwindigkeit ca. 80 km/h,
Gesamtgewicht 11.000 kg, Baujahr 1968, Löschwassertank 2.400 l,
im Heck eingebaute Hochdruck- und Normaldruckpumpe R 65.000, 250 l/min bei
40 bar bzw.
1600 l/min bei 8 bar, Schnellangriffseinrichtung, Besatzung TLF-Gruppe 1:6.
Seilwinde vorne, Zugkraft 2.000 kg.
<br/>
Eingesetzt:
<br/>
1968 - 1985:   FF Amstetten
<br/>
1985 - 1996:   FF Strengberg
<br/>
1996 - 2006:   FF Höbersbrunn
<br/>
Ab 2007:         Mag. Michael Thomasberger
  </td>
</tr>


</tbody>
     </table>
<p>Quellen: Texte zur Präsentation: Dr. jur. Alfred Zeilmayr, Fotos: Josef Rohowsky
</p>
     </div>


</fieldset>
</div>
<?php 
BA_HTML_trailer();
 ?>
