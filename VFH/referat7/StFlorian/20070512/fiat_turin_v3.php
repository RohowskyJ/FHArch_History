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
L&ouml;schfahrzeug Fiat Turin
mit Vorbaupumpe
<br/>
(1922 - heute als Oldtimer)
<br/>
FF Schwertberg
  </th>
</tr>

<tr>
 <td>
   <img src="../../../../login/AOrd_Verz/124/09/06/20070512/124-20070512-W-IMG_5836.JPG" alt="Fiat Turin" align="right" />
Die Motorisierungswelle des beginnenden 20. Jahrhunderts macht auch vor
den Feuerwehren nicht halt! Im besonderen war es das Problem der Verfügbarkeit
der Pferde, für die großen BF aber die enormen Kosten der Pferdehaltung,
die Lösungen erzwungen haben!
<br/>
BF Berlin: Pferdegezogener Löschzug kostet 1906 rund 22.000 Reichsmark/Jahr,
ein automobiler hingegen nur 5.000!
<br/>
Begonnen hat es bei den österr. Feuerwehren 1902 mit elektrisch betriebenen Fahrzeugen
(Batterien - benzin-elektrischer Antrieb) bis sich das benzinbetriebene Fahrzeug
schlussendlich durchgesetzt hat.
<br/>
   <img src="../../../../login/AOrd_Verz/124/09/06/20070512/124-20070512-W-IMG_5833.JPG" alt="Fiat Turin" align="left" />
Erste benzinbetriebene Fahrzeuge bei der FF LINZ 1912 ein Rettungsfahrzeug
Puch III, 1913 ein Löschfahrzeug Puch VI.
<br/>
Das hier gezeigte LF, ein Fiat Turin der FF Schwertberg mit 16 PS, Baujahr 1922,
ist - neben dem LF der FF ENNS mit Indienststellung 1924 - das älteste
noch fahrfä;hige Feuerwehrfahrzeug Oberösterreichs. Aufbau erfolgte
aus einem PKW- Fahrgestell, Besatzung 3 Personen, Bauartgeschwindigkeit 45 km/h,
Vorbaupumpe Tamini mit 500 l/min bei 6 bar.
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
