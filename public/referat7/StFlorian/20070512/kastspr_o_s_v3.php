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
Kastenspritze ohne Saugwerk,
Füllung mittels Löscheimer
<br/>
Oldtimerverein FF Wels
und FF Lambach
  </th>
</tr>

<tr>
 <td>
  <img src="../../../../login/AOrd_Verz/124/09/06/20070512/124-20070512-W-IMG_5792.JPG" alt="Kastenspritze ohne Saugwerk, Löschgruppe" align="right"/>
Wichtigstes und überall anzutreffendes Löschgerät des
Mittelalters und der beginnenden Neuzeit ist der Feuerlöscheimer -
aus Leder, Stroh, Hanf, Holz oder  Segeltuch - soweit nötig -
inwendig ausgepicht -  Inhalt ca. 4 - 6 Liter.
<br/>
Im Brandfalle werden gefüllte Eimer von der Wasserbezugsstelle
zum Brandplatz entweder einzeln getragen oder in einer langen Reihe
weitergereicht, vom Letzten in das Feuer geschüttet. Leere
Löscheimer werden in einer zweiten Reihe zurücktransportiert ("Eimerkette").
<br/>
Auch nach Auftreten der "Feuerspritze" -  anfang des 17. Jahrhunderts -
muss das Löschwasser noch immer händisch aus Löscheimern
oder anderen Wasserbehältern in den Wasserkasten der Handdruckspritzen
geschüttet werden, da diese nicht "ansaugen" konnten!
<br/>
Statistik Ende Dezember 1886: <br/>88 Wasserwagen und über 4.200 Löscheimer
(ohne die für jedes Haus vorgeschriebenen Eimer!)
<br/>
  <img src="../../../../login/AOrd_Verz/124/09/06/20070512/124-20070512-W-IMG_5794.JPG" alt="Kastenspritze ohne Saugwerk" align="left"/>
Vor Erfindung der Druckschläuche alle Löschmaßnahmen = "Außenangriff".
Erste Verwendung der vom Amsterdamer Brandmeister Jan van der Heyde erfundenen,
aus Leder gefertigten, durch Verschraubungen ("Holländer") verbundenen
Druckschläuche bei einem Brand in Amsterdam am 12. Jänner 1673.
<br/>
Löschstrahl wird vorerst stoßweise, später kontinuierlich
über einen Windkessel mittels "Wenderohr" oder eine Schlauchleitung
mit Strahlrohr auf den Brandherd gespritzt.
<br/>
Die gezeigte Kastenspritze ohne Saugwerk, aber bereits mit Windkessel,
kommt von der FF Wels, erzeugt Mitte des 18. Jh., außer Dienst gestellt 1868;
<br/>
Bedienung 4 Mann, 180 kg schwer, Wasserkasten ca. 180 l, Wendestrahlrohr,
ca. 100 l/min, Spritzweite um 15 m
<br/>
Der 1. Feuerwehr-Oldtimerverein der FF Wels, der heute einen großen Teil unseres
Programms bestreitet, wurde 1987 gegründet und hat unter seinem
Präsidenten Erwin GATTRINGER heuer sein 20. Bestandsjahr erreicht.
<br/>
Der Verein betreut derzeit 6 Oldtimer-Feuerwehrfahrzeuge und zwei Anhängespritzen.
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
