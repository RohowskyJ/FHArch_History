<?php
session_start();
# ------------------------------------------------------------------------------------------------------------------
#
#Inhaltsverzeichnis und Aufruf für Archivierte Berichte


$module    = 'VF_Arch';
$path2ROOT = "../../../../";

$debug = False;  // Debug output Ein/Aus Schalter

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
TLF 2000 auf Steyr 380 Z
<br/>
(1957 - 1997)
<br/>
Ingo Weickenkas und Martin Pammer, Braunau am Inn
  </th>
</tr>

<tr>
 <td>
   <img src="../../../../login/AOrd_Verz/124/09/06/20070512/124-20070512-W-IMG_5861.JPG" alt="TLF 2000 auf Steyr 380 Z" align="right" />
Die Steyrwerke konnten ab 1946 wieder ihre Fahrzeugproduktion aufnehmen.
Der erste Typ, der 3 t Benzin-LKW Steyr 370 mit 80 PS, entsprach weitgehend dem
Kriegsfahrgestelltyp 1500 A, bewährte sich aber für die Feuerwehren nicht.
Erst 1948 mit dem 3,5 t Diesel-Typ Steyr 380 gelang der Durchbruch.
In der Folge wurden alle geeigneten Steyr-Fahrgestelle (die bekannten
Typen 480, 580, 586 usw.) von der Feuerwehr-Fahrzeugindustrie verwendet.
<br/>
Das hier von unserem Kameraden Ingo Weickenkas von der FF Braunau am Inn
vorgestellte Fahrzeug, ist ein TLF 2000, Fahrgestell Steyr 380 Z
mit Straßenantrieb und Differentialsperre, Aufbau Firma Metz in Karlsruhe,
Viertakt-Sechszylinder-Dieselmotor mit 90 PS, Bauartgeschwindigkeit ca. 81 km/h,
Gesamtgewicht 7.800 kg, Baujahr 1957, Löschwassertank 2.000 l, im
Heck eingebaute Metz-Normaldruckpumpe 1600 l/min bei 8 bar,
Schnellangriffseinrichtung, Besatzung TLF-Gruppe 1:6.
<br/>
Dieses TLF war das erste im Feuerwehrbezirk Braunau am Inn, mitgeführt
wird ein Rüstanhänger 750, Aufbau Rosenbauer, das erste Seriengerät
in OÖ für Stützpunkte im Technischen Hilfsdienst!
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
