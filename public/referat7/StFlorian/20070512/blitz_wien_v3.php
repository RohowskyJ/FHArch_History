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
TLF Opel-Blitz,
Modell Wien
<br/>
Oldtimerverein FF Wels
  </th>
</tr>

<tr>
 <td>
   <img src="../../../../login/AOrd_Verz/124/09/06/20070512/124-20070512-W-IMG_5859.JPG" alt="Opel Blitz Modell Wien" align="right" />
Nach Kriegsende bis zur Lieferung von Feuerwehr-Fahrgestellen durch die
österreichische Fahrzeugindustrie wurden von den
Aufbauherstellern in Österreich, hauptsächlich Rosenbauer, eine
große Anzahl von Fahrzeugen der ehemaligen Deutschen Wehrmacht für
Feuerwehraufbauten verwendet. Hervorragend waren für diesen Zweck das
Opel-Fahrgestell 3,6 und der Steyr 1500 A geeignet.
<br/>
Für die BF Wien, aber auch andere Feuerwehren, wurden auf dem
Opel-Blitz beispielsweise Pumpenwagen mit Vorbaupumpe und das TLF "Modell Wien"
gefertigt, das sie hier sehen werden:
<br/>
Fahrgestell Opel-Blitz 3,6-6700 mit Allradantrieb, Aufbau Firma Rosenbauer,
Leonding bei Linz, Viertakt-Sechszylinder-Vergasermotor mit 75 PS,
Bauartgeschwindigkeit ~80 km/h, Gesamtgewicht 6.700 kg, Baujahr 1947,
Löschwassertank 1.500 l, FP aufgeprotzte TS mit 800 l/min bei 8 bar,
mit Elektrostarter, mit Schnellangriffseinrichtung, Besatzung TLF-Gruppe 1:6.
<br/>
Eingesetzt:
<br/>
1953 - 1979 bei der FF Weißenberg, Gemeinde Neuhofen,
<br/>
Ankauf durch Oldtimerverein 1996
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
