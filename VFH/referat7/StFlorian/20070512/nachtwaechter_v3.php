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
Aufsteigender Rauch,
daraufhin erfolgt der "Feuerruf" des Nacht- bzw. Feuerwächters und
Sturmgeläute durch die "Feuerglocke"
<br/>
Nachtwächter-Darstellung durch Kamerad Bernecker
<br/>
Sturmgeläute
AW Ing. Schmotzer
(alle FF Wels)

  </th>
</tr>

<tr>
 <td>
<img src="124-20070512-W-IMG_5789_2_96.JPG" alt="Nachtwächter" align="left">
"Sicherheit" vor Gefahren ist seit jeher Grundbedürfnis der Menschen, Wächter auf Türmen sehen von
weitem annähernde Feinde aber auch aufflammende Brände. In der Neuzeit werden die Nacht- und Turmwächter
"Organe der Feuerpolizei"
<br/>
Vorbeugend, als Mahner für einen vorsichtigen Umgang mit Feuer und Licht
("Hört, ihr Leut und lasst euch sagen, die Glock hat zehn geschlagen, bewahrt das Feuer und auch das Licht,
damit niemand kein Schad g'schicht."
(Richard WAGNER, Meistersinger von Nürnberg)
<br/>
Türmer St. Stefan seit Mitte 15. Jh., in Oberösterreich Mitte 16. Jh.
<br/>
Abwehrend, als alarmierende Stelle, mittels Hornsignal,  Sprachrohr, Veranlassung Läuten von Kirchenglocken,
Abgabe von Kanonenschüssen. Richtung wahrgenommener Brände wird mit roten Fahnen - Laternen angezeigt.
<br/>
(Turmwächterstube in FREISTADT).
Letzte Turm- bzw. Nachtwächter (zw. 1931 und 1939).
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
